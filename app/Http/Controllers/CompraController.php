<?php

namespace App\Http\Controllers;

use App\Models\Catalogo;
use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\Insumo;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\LoteInsumo;
use App\Models\LoteProducto;
use App\Models\MovimientoInventario;
use App\Models\MovimientoInventarioLote;
use App\Models\MovimientoProducto;
use App\Models\MovimientoProductoLote;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompraController extends Controller
{
    public function index(Request $request)
    {
        $query = Compra::with('proveedor')
            ->when($request->search, function ($query) use ($request) {
                $search = '%' . $request->search . '%';

                $query->where(function ($q) use ($search) {
                    $q->where('numero', 'like', $search)
                        ->orWhere('numero_comprobante', 'like', $search)
                        ->orWhere('tipo_comprobante', 'like', $search)
                        ->orWhere('metodo_pago', 'like', $search)
                        ->orWhereHas('proveedor', function ($proveedorQuery) use ($search) {
                            $proveedorQuery->where('nombre_comercial', 'like', $search)
                                ->orWhere('nombre_legal', 'like', $search)
                                ->orWhere('rtn', 'like', $search)
                                ->orWhere('telefono', 'like', $search);
                        });
                });
            })
            ->when($request->fecha_desde, function ($query) use ($request) {
                $query->whereDate('fecha', '>=', $request->fecha_desde);
            })
            ->when($request->fecha_hasta, function ($query) use ($request) {
                $query->whereDate('fecha', '<=', $request->fecha_hasta);
            })
            ->when($request->tipo_pago && $request->tipo_pago !== 'todos', function ($query) use ($request) {
                $query->where('tipo_pago', $request->tipo_pago);
            })
            ->when($request->estado && $request->estado !== 'todos', function ($query) use ($request) {
                $query->where('estado', $request->estado);
            });

        $totalCompras = (clone $query)->count();

        $montoCompras = (clone $query)
            ->where('estado', '!=', 'Anulada')
            ->sum('total');

        $saldoPendiente = (clone $query)
            ->where('estado', '!=', 'Anulada')
            ->sum('saldo_pendiente');

        $montoAnulado = (clone $query)
            ->where('estado', 'Anulada')
            ->sum('total');

        $compras = $query
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate(10)
            ->appends($request->query());

        return view('compras.index', [
            'compras' => $compras,
            'totalCompras' => $totalCompras,
            'montoCompras' => $montoCompras,
            'saldoPendiente' => $saldoPendiente,
            'montoAnulado' => $montoAnulado,
        ]);
    }

    public function create()
    {
        $proveedores = Proveedor::where('activo', true)
            ->orderBy('nombre_comercial')
            ->get();

        $insumos = Insumo::where('activo', true)
            ->orderBy('nombre')
            ->get();

        $productos = Producto::where('activo', true)
            ->orderBy('nombre')
            ->get();

        $metodosPago = Catalogo::opciones('metodo_pago')
            ->pluck('nombre')
            ->toArray();

        $insumosCompra = $insumos->map(function ($insumo) {
            return [
                'id' => $insumo->id,
                'codigo' => $insumo->codigo,
                'nombre' => $insumo->nombre,
                'costo' => (float) $insumo->costo_unitario_real,
            ];
        })->values()->toArray();

        $productosCompra = $productos->map(function ($producto) {
            return [
                'id' => $producto->id,
                'codigo' => $producto->codigo,
                'nombre' => $producto->nombre,
                'costo' => (float) $producto->costo_unitario,
            ];
        })->values()->toArray();

        $itemsCompraInicial = old('items', [
            [
                'tipo_item' => 'Insumo',
                'item_id' => '',
                'cantidad' => 1,
                'costo_unitario' => '',
                'descuento' => 0,
            ],
        ]);

        return view('compras.form', [
            'proveedores' => $proveedores,
            'insumos' => $insumos,
            'productos' => $productos,
            'metodosPago' => $metodosPago,
            'insumosCompra' => $insumosCompra,
            'productosCompra' => $productosCompra,
            'itemsCompraInicial' => $itemsCompraInicial,
        ]);
    }

    public function store(Request $request)
    {
        $items = collect($request->input('items', []))
            ->filter(function ($item) {
                return !empty($item['item_key'])
                    || !empty($item['cantidad'])
                    || !empty($item['costo_unitario']);
            })
            ->values()
            ->toArray();

        $request->merge([
            'items' => $items,
        ]);

        $request->validate([
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'fecha' => 'required|date',
            'numero_comprobante' => 'nullable|max:100',
            'tipo_comprobante' => 'required|max:50',
            'tipo_pago' => 'required|in:Contado,Crédito',
            'metodo_pago' => 'required|max:50',
            'monto_pagado' => 'nullable|numeric|min:0',
            'observacion' => 'nullable|max:1000',

            'items' => 'required|array|min:1',
            'items.*.item_key' => 'required|string',
            'items.*.cantidad' => 'required|numeric|min:0.01',
            'items.*.costo_unitario' => 'required|numeric|min:0.0001',
            'items.*.descuento' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $subtotalCompra = 0;
                $descuentoCompra = 0;
                $totalCompra = 0;

                $detallesPreparados = [];

                foreach ($request->items as $item) {
                    $partes = explode('|', $item['item_key']);

                    if (count($partes) !== 2) {
                        throw new \Exception('Uno de los items seleccionados no es válido.');
                    }

                    $tipoItem = $partes[0];
                    $itemId = $partes[1];

                    if (!in_array($tipoItem, ['Insumo', 'Producto'])) {
                        throw new \Exception('Tipo de item no válido.');
                    }

                    if ($tipoItem === 'Insumo') {
                        $modelo = Insumo::findOrFail($itemId);
                        $codigo = $modelo->codigo;
                        $descripcion = $modelo->nombre;
                    } else {
                        $modelo = Producto::findOrFail($itemId);
                        $codigo = $modelo->codigo;
                        $descripcion = $modelo->nombre;
                    }

                    $cantidad = (float) $item['cantidad'];
                    $costoUnitario = (float) $item['costo_unitario'];
                    $descuento = isset($item['descuento']) ? (float) $item['descuento'] : 0;

                    $subtotal = $cantidad * $costoUnitario;
                    $total = $subtotal - $descuento;

                    if ($total < 0) {
                        $total = 0;
                    }

                    $subtotalCompra += $subtotal;
                    $descuentoCompra += $descuento;
                    $totalCompra += $total;

                    $detallesPreparados[] = [
                        'tipo_item' => $tipoItem,
                        'item_id' => $itemId,
                        'codigo' => $codigo,
                        'descripcion' => $descripcion,
                        'cantidad' => $cantidad,
                        'costo_unitario' => $costoUnitario,
                        'subtotal' => $subtotal,
                        'descuento' => $descuento,
                        'total' => $total,
                    ];
                }

                $montoPagado = $request->tipo_pago === 'Contado'
                    ? $totalCompra
                    : (float) ($request->monto_pagado ?? 0);

                if ($montoPagado > $totalCompra) {
                    $montoPagado = $totalCompra;
                }

                $saldoPendiente = $totalCompra - $montoPagado;

                $compra = Compra::create([
                    'proveedor_id' => $request->proveedor_id ?: null,
                    'fecha' => $request->fecha,
                    'numero_comprobante' => $request->numero_comprobante,
                    'tipo_comprobante' => $request->tipo_comprobante,
                    'tipo_pago' => $request->tipo_pago,
                    'metodo_pago' => $request->metodo_pago,
                    'subtotal' => $subtotalCompra,
                    'descuento' => $descuentoCompra,
                    'impuesto' => 0,
                    'total' => $totalCompra,
                    'monto_pagado' => $montoPagado,
                    'saldo_pendiente' => $saldoPendiente,
                    'estado' => 'Registrada',
                    'observacion' => $request->observacion,
                ]);

                foreach ($detallesPreparados as $detalle) {
                    $detalle['compra_id'] = $compra->id;

                    $detalleCompra = CompraDetalle::create($detalle);

                    $this->registrarEntradaInventarioCompra($compra, $detalleCompra);
                }
            });

            return redirect()
                ->route('compras.index')
                ->with('message', 'Compra registrada correctamente.');
        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }


    public function show(Compra $compra)
    {
        $compra->load(['proveedor', 'detalles']);

        return view('compras.show', compact('compra'));
    }

    public function anular(Compra $compra)
    {
        if ($compra->estado === 'Anulada') {
            return redirect()
                ->route('compras.index')
                ->with('error', 'Esta compra ya está anulada.');
        }

        try {
            DB::transaction(function () use ($compra) {
                $this->revertirInventarioCompra($compra);

                $observacionAnterior = $compra->observacion ? $compra->observacion . "\n" : '';

                $compra->update([
                    'estado' => 'Anulada',
                    'monto_pagado' => 0,
                    'saldo_pendiente' => 0,
                    'observacion' => $observacionAnterior . 'Compra anulada el ' . now()->format('d/m/Y H:i'),
                ]);
            });

            return redirect()
                ->route('compras.index')
                ->with('message', 'Compra anulada correctamente. El inventario fue revertido.');
        } catch (\Exception $e) {
            return redirect()
                ->route('compras.index')
                ->with('error', $e->getMessage());
        }
    }

    private function registrarEntradaInventarioCompra(Compra $compra, CompraDetalle $detalle)
    {
        if ($detalle->tipo_item === 'Insumo') {
            $this->registrarEntradaInsumoCompra($compra, $detalle);
            return;
        }

        if ($detalle->tipo_item === 'Producto') {
            $this->registrarEntradaProductoCompra($compra, $detalle);
            return;
        }
    }

    private function registrarEntradaInsumoCompra(Compra $compra, CompraDetalle $detalle)
    {
        $insumo = Insumo::findOrFail($detalle->item_id);

        $costoUnitarioReal = $detalle->cantidad > 0
            ? $detalle->total / $detalle->cantidad
            : $detalle->costo_unitario;

        $movimiento = MovimientoInventario::create([
            'insumo_id' => $insumo->id,
            'tipo_movimiento' => 'Entrada compra',
            'cantidad' => $detalle->cantidad,
            'costo_unitario' => $costoUnitarioReal,
            'total' => $detalle->total,
            'referencia' => $compra->numero,
            'observacion' => 'Entrada automática por compra ' . $compra->numero,
        ]);

        $lote = LoteInsumo::create([
            'insumo_id' => $insumo->id,
            'codigo_lote' => $compra->numero . '-INS-' . $detalle->id,
            'fecha_entrada' => $compra->fecha,
            'cantidad_inicial' => $detalle->cantidad,
            'cantidad_disponible' => $detalle->cantidad,
            'costo_unitario' => $costoUnitarioReal,
            'total' => $detalle->total,
            'referencia' => $compra->numero,
            'observacion' => 'Lote creado automáticamente desde compra ' . $compra->numero,
            'activo' => true,
        ]);

        MovimientoInventarioLote::create([
            'movimiento_inventario_id' => $movimiento->id,
            'lote_insumo_id' => $lote->id,
            'cantidad' => $detalle->cantidad,
            'costo_unitario' => $costoUnitarioReal,
            'total' => $detalle->total,
        ]);

        $this->actualizarCostoActualPepsInsumo($insumo);
    }

    private function registrarEntradaProductoCompra(Compra $compra, CompraDetalle $detalle)
    {
        $producto = Producto::findOrFail($detalle->item_id);

        if (!$producto->maneja_inventario) {
            return;
        }

        $costoUnitarioReal = $detalle->cantidad > 0
            ? $detalle->total / $detalle->cantidad
            : $detalle->costo_unitario;

        $movimiento = MovimientoProducto::create([
            'producto_id' => $producto->id,
            'tipo_movimiento' => 'Entrada compra',
            'cantidad' => $detalle->cantidad,
            'costo_unitario' => $costoUnitarioReal,
            'total' => $detalle->total,
            'referencia' => $compra->numero,
            'observacion' => 'Entrada automática por compra ' . $compra->numero,
        ]);

        $lote = LoteProducto::create([
            'producto_id' => $producto->id,
            'codigo_lote' => $compra->numero . '-PROD-' . $detalle->id,
            'fecha_entrada' => $compra->fecha,
            'cantidad_inicial' => $detalle->cantidad,
            'cantidad_disponible' => $detalle->cantidad,
            'costo_unitario' => $costoUnitarioReal,
            'total' => $detalle->total,
            'referencia' => $compra->numero,
            'observacion' => 'Lote creado automáticamente desde compra ' . $compra->numero,
            'activo' => true,
        ]);

        MovimientoProductoLote::create([
            'movimiento_producto_id' => $movimiento->id,
            'lote_producto_id' => $lote->id,
            'cantidad' => $detalle->cantidad,
            'costo_unitario' => $costoUnitarioReal,
            'total' => $detalle->total,
        ]);

        $this->actualizarCostoActualPepsProducto($producto);
    }

    private function actualizarCostoActualPepsInsumo($insumo)
    {
        $stockActual = $insumo->lotes()
            ->where('activo', true)
            ->sum('cantidad_disponible');

        $proximoLote = $insumo->lotes()
            ->where('activo', true)
            ->where('cantidad_disponible', '>', 0)
            ->orderBy('fecha_entrada')
            ->orderBy('id')
            ->first();

        $insumo->stock_actual = $stockActual;

        if ($proximoLote) {
            $insumo->costo_unitario_base = $proximoLote->costo_unitario;
            $insumo->costo_unitario_real = $proximoLote->costo_unitario;
        }

        $insumo->save();
    }

    private function actualizarCostoActualPepsProducto($producto)
    {
        $stockActual = $producto->lotes()
            ->where('activo', true)
            ->sum('cantidad_disponible');

        $proximoLote = $producto->lotes()
            ->where('activo', true)
            ->where('cantidad_disponible', '>', 0)
            ->orderBy('fecha_entrada')
            ->orderBy('id')
            ->first();

        $producto->stock_actual = $stockActual;

        if ($proximoLote) {
            $producto->costo_unitario = $proximoLote->costo_unitario;
        }

        $producto->save();
    }

    private function revertirInventarioCompra(Compra $compra)
    {
        $movimientosInsumos = MovimientoInventario::with(['detalleLotes.lote', 'insumo'])
            ->where('referencia', $compra->numero)
            ->where('tipo_movimiento', 'Entrada compra')
            ->get();

        foreach ($movimientosInsumos as $movimiento) {
            foreach ($movimiento->detalleLotes as $detalleLote) {
                $lote = $detalleLote->lote;

                if (!$lote) {
                    continue;
                }

                if ($lote->cantidad_disponible < $detalleLote->cantidad) {
                    throw new \Exception(
                        'No se puede anular la compra ' . $compra->numero .
                            ' porque parte del insumo "' . $movimiento->insumo->nombre .
                            '" ya fue consumido o utilizado.'
                    );
                }
            }

            $salida = MovimientoInventario::create([
                'insumo_id' => $movimiento->insumo_id,
                'tipo_movimiento' => 'Salida ajuste',
                'cantidad' => $movimiento->cantidad,
                'costo_unitario' => $movimiento->costo_unitario,
                'total' => $movimiento->total,
                'referencia' => $compra->numero,
                'observacion' => 'Reversión automática por anulación de compra ' . $compra->numero,
            ]);

            foreach ($movimiento->detalleLotes as $detalleLote) {
                $lote = $detalleLote->lote;

                if (!$lote) {
                    continue;
                }

                $lote->cantidad_disponible = $lote->cantidad_disponible - $detalleLote->cantidad;

                if ($lote->cantidad_disponible <= 0) {
                    $lote->cantidad_disponible = 0;
                    $lote->activo = false;
                }

                $lote->save();

                MovimientoInventarioLote::create([
                    'movimiento_inventario_id' => $salida->id,
                    'lote_insumo_id' => $lote->id,
                    'cantidad' => $detalleLote->cantidad,
                    'costo_unitario' => $detalleLote->costo_unitario,
                    'total' => $detalleLote->total,
                ]);
            }

            if ($movimiento->insumo) {
                $this->actualizarCostoActualPepsInsumo($movimiento->insumo);
            }
        }

        $movimientosProductos = MovimientoProducto::with(['detalleLotes.lote', 'producto'])
            ->where('referencia', $compra->numero)
            ->where('tipo_movimiento', 'Entrada compra')
            ->get();

        foreach ($movimientosProductos as $movimiento) {
            foreach ($movimiento->detalleLotes as $detalleLote) {
                $lote = $detalleLote->lote;

                if (!$lote) {
                    continue;
                }

                if ($lote->cantidad_disponible < $detalleLote->cantidad) {
                    throw new \Exception(
                        'No se puede anular la compra ' . $compra->numero .
                            ' porque parte del producto "' . $movimiento->producto->nombre .
                            '" ya fue vendido, producido o utilizado.'
                    );
                }
            }

            $salida = MovimientoProducto::create([
                'producto_id' => $movimiento->producto_id,
                'tipo_movimiento' => 'Salida ajuste',
                'cantidad' => $movimiento->cantidad,
                'costo_unitario' => $movimiento->costo_unitario,
                'total' => $movimiento->total,
                'referencia' => $compra->numero,
                'observacion' => 'Reversión automática por anulación de compra ' . $compra->numero,
            ]);

            foreach ($movimiento->detalleLotes as $detalleLote) {
                $lote = $detalleLote->lote;

                if (!$lote) {
                    continue;
                }

                $lote->cantidad_disponible = $lote->cantidad_disponible - $detalleLote->cantidad;

                if ($lote->cantidad_disponible <= 0) {
                    $lote->cantidad_disponible = 0;
                    $lote->activo = false;
                }

                $lote->save();

                MovimientoProductoLote::create([
                    'movimiento_producto_id' => $salida->id,
                    'lote_producto_id' => $lote->id,
                    'cantidad' => $detalleLote->cantidad,
                    'costo_unitario' => $detalleLote->costo_unitario,
                    'total' => $detalleLote->total,
                ]);
            }

            if ($movimiento->producto) {
                $this->actualizarCostoActualPepsProducto($movimiento->producto);
            }
        }
    }
}
