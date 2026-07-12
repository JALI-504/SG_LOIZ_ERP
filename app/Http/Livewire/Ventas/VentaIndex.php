<?php

namespace App\Http\Livewire\Ventas;

use App\Models\Catalogo;
use App\Models\Cliente;
use App\Models\Insumo;
use App\Models\LoteInsumo;
use App\Models\LoteProducto;
use App\Models\MovimientoInventario;
use App\Models\MovimientoInventarioLote;
use App\Models\MovimientoProducto;
use App\Models\MovimientoProductoLote;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Venta;
use App\Models\VentaDetalle;
use App\Models\PagoVenta;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class VentaIndex extends Component
{
    public $cliente_id;
    public $searchCliente = '';

    public $searchItem = '';
    public $tipoFiltro = 'todos';

    public $metodo_pago = 'Efectivo';
    public $estado = 'Pagada';
    public $observacion;

    public $monto_inicial = 0;
    public $referencia_pago_inicial;

    public $metodosPago = [];
    public $estadosVenta = [];

    public $carrito = [];

    public $subtotal = 0;
    public $descuento_general = 0;
    public $descuento_total = 0;

    public $subtotal_gravado = 0;
    public $subtotal_exento = 0;
    public $subtotal_no_sujeto = 0;
    public $isv_15 = 0;

    public $impuesto = 0;
    public $retencion = 0;
    public $neto_recibido = 0;
    public $total = 0;

    public function mount()
    {
        $this->metodosPago = Catalogo::opciones('metodo_pago')->pluck('nombre')->toArray();
        $this->estadosVenta = Catalogo::opciones('estado_venta')->pluck('nombre')->toArray();

        $this->metodo_pago = $this->metodosPago[0] ?? 'Efectivo';
        $this->estado = 'Pagada';
    }

    public function updated($propertyName)
    {
        if (
            $propertyName === 'descuento_general' ||
            $propertyName === 'retencion' ||
            strpos($propertyName, 'carrito.') === 0
        ) {
            $this->recalcularCarrito();
        }
    }

    public function agregarProducto($productoId)
    {
        $producto = Producto::findOrFail($productoId);

        if (!$producto->activo) {
            session()->flash('error', 'El producto seleccionado está inactivo.');
            return;
        }

        if ($producto->maneja_inventario && $producto->stock_actual <= 0) {
            session()->flash('error', 'El producto no tiene stock disponible.');
            return;
        }

        $index = $this->buscarIndiceCarrito('Producto', $producto->id);

        if ($index !== null) {
            $this->carrito[$index]['cantidad']++;
            $this->recalcularCarrito();
            return;
        }

        $this->carrito[] = [
            'tipo_item' => 'Producto',
            'item_id' => $producto->id,
            'codigo' => $producto->codigo,
            'descripcion' => $producto->nombre,
            'cantidad' => 1,
            'precio_unitario' => (float) $producto->precio_venta,
            'costo_unitario' => (float) $producto->costo_unitario,

            'tipo_impuesto' => $producto->tipo_impuesto ?? 'Gravado 15%',
            'porcentaje_isv' => (float) ($producto->porcentaje_isv ?? 15),

            'descuento' => 0,
            'descuento_total_linea' => 0,

            'subtotal_gravado' => 0,
            'subtotal_exento' => 0,
            'subtotal_no_sujeto' => 0,
            'impuesto' => 0,

            'subtotal' => (float) $producto->precio_venta,
            'total' => (float) $producto->precio_venta,
        ];

        $this->recalcularCarrito();
    }

    public function agregarServicio($servicioId)
    {
        $servicio = Servicio::findOrFail($servicioId);

        if (!$servicio->activo) {
            session()->flash('error', 'El servicio seleccionado está inactivo.');
            return;
        }

        $index = $this->buscarIndiceCarrito('Servicio', $servicio->id);

        if ($index !== null) {
            $this->carrito[$index]['cantidad']++;
            $this->recalcularCarrito();
            return;
        }

        $this->carrito[] = [
            'tipo_item' => 'Servicio',
            'item_id' => $servicio->id,
            'codigo' => $servicio->codigo,
            'descripcion' => $servicio->nombre,
            'cantidad' => 1,
            'precio_unitario' => (float) $servicio->precio_unitario,
            'costo_unitario' => (float) $servicio->costo_unitario,

            'tipo_impuesto' => $servicio->tipo_impuesto ?? 'Gravado 15%',
            'porcentaje_isv' => (float) ($servicio->porcentaje_isv ?? 15),

            'descuento' => 0,
            'descuento_total_linea' => 0,

            'subtotal_gravado' => 0,
            'subtotal_exento' => 0,
            'subtotal_no_sujeto' => 0,
            'impuesto' => 0,

            'subtotal' => (float) $servicio->precio_unitario,
            'total' => (float) $servicio->precio_unitario,
        ];

        $this->recalcularCarrito();
    }

    private function buscarIndiceCarrito($tipoItem, $itemId)
    {
        foreach ($this->carrito as $index => $item) {
            if (
                $item['tipo_item'] === $tipoItem &&
                (int) $item['item_id'] === (int) $itemId
            ) {
                return $index;
            }
        }

        return null;
    }

    public function aumentarCantidad($index)
    {
        if (!isset($this->carrito[$index])) {
            return;
        }

        $this->carrito[$index]['cantidad']++;
        $this->recalcularCarrito();
    }

    public function disminuirCantidad($index)
    {
        if (!isset($this->carrito[$index])) {
            return;
        }

        if ($this->carrito[$index]['cantidad'] <= 1) {
            return;
        }

        $this->carrito[$index]['cantidad']--;
        $this->recalcularCarrito();
    }

    public function eliminarItem($index)
    {
        if (!isset($this->carrito[$index])) {
            return;
        }

        unset($this->carrito[$index]);
        $this->carrito = array_values($this->carrito);

        $this->recalcularCarrito();
    }

    public function limpiarCarrito()
    {
        $this->carrito = [];

        $this->subtotal = 0;
        $this->descuento_general = 0;
        $this->descuento_total = 0;

        $this->subtotal_gravado = 0;
        $this->subtotal_exento = 0;
        $this->subtotal_no_sujeto = 0;
        $this->isv_15 = 0;

        $this->impuesto = 0;
        $this->retencion = 0;
        $this->neto_recibido = 0;
        $this->total = 0;
    }

    private function recalcularCarrito()
    {
        $subtotal = 0;
        $descuentosLinea = 0;

        /*
    |--------------------------------------------------------------------------
    | Primera pasada: calcular subtotal bruto y descuentos de línea
    |--------------------------------------------------------------------------
    */
        foreach ($this->carrito as $index => $item) {
            $cantidad = (float) ($item['cantidad'] ?? 0);
            $precioUnitario = (float) ($item['precio_unitario'] ?? 0);
            $descuentoLinea = (float) ($item['descuento'] ?? 0);

            if ($cantidad < 0) {
                $cantidad = 0;
            }

            if ($precioUnitario < 0) {
                $precioUnitario = 0;
            }

            $subtotalItem = $cantidad * $precioUnitario;

            if ($descuentoLinea < 0) {
                $descuentoLinea = 0;
            }

            if ($descuentoLinea > $subtotalItem) {
                $descuentoLinea = $subtotalItem;
            }

            $totalAntesDescuentoGeneral = $subtotalItem - $descuentoLinea;

            $this->carrito[$index]['cantidad'] = $cantidad;
            $this->carrito[$index]['precio_unitario'] = $precioUnitario;
            $this->carrito[$index]['descuento'] = $descuentoLinea;
            $this->carrito[$index]['subtotal'] = round($subtotalItem, 2);
            $this->carrito[$index]['total_antes_descuento_general'] = round($totalAntesDescuentoGeneral, 2);

            $subtotal += $subtotalItem;
            $descuentosLinea += $descuentoLinea;
        }

        /*
    |--------------------------------------------------------------------------
    | Validar descuento general
    |--------------------------------------------------------------------------
    */
        $descuentoGeneral = (float) $this->descuento_general;

        if ($descuentoGeneral < 0) {
            $descuentoGeneral = 0;
        }

        $baseParaDescuentoGeneral = $subtotal - $descuentosLinea;

        if ($descuentoGeneral > $baseParaDescuentoGeneral) {
            $descuentoGeneral = $baseParaDescuentoGeneral;
        }

        /*
    |--------------------------------------------------------------------------
    | Segunda pasada: distribuir descuento general y calcular impuestos
    |--------------------------------------------------------------------------
    */
        $subtotalGravado = 0;
        $subtotalExento = 0;
        $subtotalNoSujeto = 0;
        $isv15 = 0;
        $totalVenta = 0;
        $descuentoGeneralAsignado = 0;

        $indices = array_keys($this->carrito);
        $ultimoIndice = end($indices);

        foreach ($this->carrito as $index => $item) {
            $totalAntesDescuentoGeneral = (float) ($item['total_antes_descuento_general'] ?? 0);

            if ($baseParaDescuentoGeneral > 0) {
                $proporcion = $totalAntesDescuentoGeneral / $baseParaDescuentoGeneral;
                $descuentoGeneralLinea = round($descuentoGeneral * $proporcion, 2);
            } else {
                $descuentoGeneralLinea = 0;
            }

            /*
        | Ajuste de centavos en la última línea
        */
            if ($index === $ultimoIndice) {
                $descuentoGeneralLinea = round($descuentoGeneral - $descuentoGeneralAsignado, 2);
            }

            $descuentoGeneralAsignado += $descuentoGeneralLinea;

            $totalItem = $totalAntesDescuentoGeneral - $descuentoGeneralLinea;

            if ($totalItem < 0) {
                $totalItem = 0;
            }

            $tipoImpuesto = $item['tipo_impuesto'] ?? 'Gravado 15%';
            $porcentajeIsv = (float) ($item['porcentaje_isv'] ?? 15);

            $subtotalGravadoItem = 0;
            $subtotalExentoItem = 0;
            $subtotalNoSujetoItem = 0;
            $impuestoItem = 0;

            /*
        |--------------------------------------------------------------------------
        | Precio final con ISV incluido
        |--------------------------------------------------------------------------
        | Si el producto vale L 115 y es gravado 15%:
        | Base gravada = 115 / 1.15 = 100
        | ISV = 15
        */
            if ($tipoImpuesto === 'Gravado 15%' && $porcentajeIsv > 0) {
                $factor = 1 + ($porcentajeIsv / 100);

                $subtotalGravadoItem = round($totalItem / $factor, 2);
                $impuestoItem = round($totalItem - $subtotalGravadoItem, 2);
            } elseif ($tipoImpuesto === 'Exento') {
                $subtotalExentoItem = round($totalItem, 2);
            } else {
                $subtotalNoSujetoItem = round($totalItem, 2);
            }

            $descuentoTotalLinea = (float) ($item['descuento'] ?? 0) + $descuentoGeneralLinea;

            $this->carrito[$index]['descuento_general_linea'] = round($descuentoGeneralLinea, 2);
            $this->carrito[$index]['descuento_total_linea'] = round($descuentoTotalLinea, 2);

            $this->carrito[$index]['tipo_impuesto'] = $tipoImpuesto;
            $this->carrito[$index]['porcentaje_isv'] = $porcentajeIsv;

            $this->carrito[$index]['subtotal_gravado'] = $subtotalGravadoItem;
            $this->carrito[$index]['subtotal_exento'] = $subtotalExentoItem;
            $this->carrito[$index]['subtotal_no_sujeto'] = $subtotalNoSujetoItem;
            $this->carrito[$index]['impuesto'] = $impuestoItem;

            $this->carrito[$index]['total'] = round($totalItem, 2);

            $subtotalGravado += $subtotalGravadoItem;
            $subtotalExento += $subtotalExentoItem;
            $subtotalNoSujeto += $subtotalNoSujetoItem;
            $isv15 += $impuestoItem;
            $totalVenta += $totalItem;
        }

        $this->descuento_general = round($descuentoGeneral, 2);
        $this->subtotal = round($subtotal, 2);
        $this->descuento_total = round($descuentosLinea + $descuentoGeneral, 2);

        $this->subtotal_gravado = round($subtotalGravado, 2);
        $this->subtotal_exento = round($subtotalExento, 2);
        $this->subtotal_no_sujeto = round($subtotalNoSujeto, 2);
        $this->isv_15 = round($isv15, 2);

        $this->impuesto = $this->isv_15;
        $this->total = round($totalVenta, 2);

        $this->retencion = (float) $this->retencion;

        if ($this->retencion < 0) {
            $this->retencion = 0;
        }

        if ($this->retencion > $this->total) {
            $this->retencion = $this->total;
        }

        $this->neto_recibido = round($this->total - $this->retencion, 2);
    }

    public function guardarVenta()
    {
        $this->recalcularCarrito();

        if (count($this->carrito) === 0) {
            session()->flash('error', 'Debe agregar al menos un producto o servicio a la venta.');
            return;
        }

        $this->validate([
            'cliente_id' => 'nullable|exists:clientes,id',
            'metodo_pago' => 'required|max:50',
            'estado' => 'required|max:30',
            'descuento_general' => 'nullable|numeric|min:0',
            'retencion' => 'nullable|numeric|min:0',
            'monto_inicial' => 'nullable|numeric|min:0',
            'referencia_pago_inicial' => 'nullable|max:100',
            'observacion' => 'nullable|max:500',
        ]);

        if ($this->total < 0) {
            session()->flash('error', 'El total de la venta no puede ser negativo.');
            return;
        }

        try {
            DB::transaction(function () {
                $this->validarDisponibilidadCarrito();

                $montoInicial = (float) $this->monto_inicial;
                $retencion = (float) $this->retencion;
                $netoRecibido = (float) $this->neto_recibido;

                if ($montoInicial < 0) {
                    $montoInicial = 0;
                }

                if ($retencion < 0) {
                    $retencion = 0;
                }

                if ($retencion > $this->total) {
                    $retencion = $this->total;
                }

                if ($netoRecibido < 0) {
                    $netoRecibido = 0;
                }

                /*
|--------------------------------------------------------------------------
| Si la venta está pagada:
| La venta se considera cancelada por:
| efectivo/banco recibido + retención aplicada.
|--------------------------------------------------------------------------
*/
                if ($this->estado === 'Pagada') {
                    $montoPagado = $this->total;
                    $saldoPendiente = 0;
                    $estadoFinal = 'Pagada';
                    $montoParaPago = $netoRecibido;
                } else {
                    if ($montoInicial > $netoRecibido) {
                        $montoInicial = $netoRecibido;
                    }

                    $montoPagado = $montoInicial + $retencion;
                    $saldoPendiente = $this->total - $montoPagado;

                    if ($saldoPendiente < 0) {
                        $saldoPendiente = 0;
                    }

                    $estadoFinal = $saldoPendiente <= 0 ? 'Pagada' : 'Pendiente';
                    $montoParaPago = $montoInicial;
                }

                $venta = Venta::create([
                    'cliente_id' => $this->cliente_id ?: null,
                    'metodo_pago' => $this->metodo_pago,
                    'estado' => $estadoFinal,

                    'subtotal' => $this->subtotal,
                    'descuento' => $this->descuento_total,

                    'subtotal_gravado' => $this->subtotal_gravado,
                    'subtotal_exento' => $this->subtotal_exento,
                    'subtotal_no_sujeto' => $this->subtotal_no_sujeto,

                    'impuesto' => $this->impuesto,
                    'isv_15' => $this->isv_15,

                    'total' => $this->total,
                    'retencion' => $this->retencion,
                    'neto_recibido' => $this->neto_recibido,

                    'monto_pagado' => $montoPagado,
                    'saldo_pendiente' => $saldoPendiente,
                    'observacion' => $this->observacion,
                ]);

                if ($montoParaPago > 0) {
                    $observacionPago = $estadoFinal === 'Pagada'
                        ? 'Pago completo registrado al momento de la venta.'
                        : 'Abono inicial registrado al momento de la venta.';

                    if ($retencion > 0) {
                        $observacionPago .= ' Retención aplicada: L ' . number_format($retencion, 2);
                    }

                    PagoVenta::create([
                        'venta_id' => $venta->id,
                        'monto' => $montoParaPago,
                        'metodo_pago' => $this->metodo_pago,
                        'referencia' => $this->referencia_pago_inicial,
                        'observacion' => $observacionPago,
                    ]);
                }

                foreach ($this->carrito as $item) {
                    $costoUnitarioReal = 0;

                    if ($item['tipo_item'] === 'Producto') {
                        $producto = Producto::findOrFail($item['item_id']);
                        $costoUnitarioReal = $this->procesarProductoVenta($producto, $item['cantidad'], $venta);
                    }

                    if ($item['tipo_item'] === 'Servicio') {
                        $servicio = Servicio::with('insumos')->findOrFail($item['item_id']);
                        $costoUnitarioReal = $this->procesarServicioVenta($servicio, $item['cantidad'], $venta);
                    }

                    VentaDetalle::create([
                        'venta_id' => $venta->id,
                        'tipo_item' => $item['tipo_item'],
                        'item_id' => $item['item_id'],
                        'codigo' => $item['codigo'],
                        'descripcion' => $item['descripcion'],
                        'cantidad' => $item['cantidad'],
                        'precio_unitario' => $item['precio_unitario'],
                        'costo_unitario' => $costoUnitarioReal,

                        'tipo_impuesto' => $item['tipo_impuesto'] ?? 'Gravado 15%',
                        'porcentaje_isv' => $item['porcentaje_isv'] ?? 15,

                        'descuento' => $item['descuento_total_linea'] ?? $item['descuento'],

                        'subtotal_gravado' => $item['subtotal_gravado'] ?? 0,
                        'subtotal_exento' => $item['subtotal_exento'] ?? 0,
                        'subtotal_no_sujeto' => $item['subtotal_no_sujeto'] ?? 0,
                        'impuesto' => $item['impuesto'] ?? 0,

                        'subtotal' => $item['subtotal'],
                        'total' => $item['total'],
                    ]);
                }

                session()->flash('message', 'Venta registrada correctamente. Número: ' . $venta->numero);
            });

            $this->resetearVenta();
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    private function validarDisponibilidadCarrito()
    {
        foreach ($this->carrito as $item) {
            if ($item['tipo_item'] === 'Producto') {
                $producto = Producto::findOrFail($item['item_id']);

                if ($producto->maneja_inventario && $producto->stock_actual < $item['cantidad']) {
                    throw new \Exception('Stock insuficiente para el producto: ' . $producto->nombre);
                }
            }

            if ($item['tipo_item'] === 'Servicio') {
                $servicio = Servicio::with('insumos')->findOrFail($item['item_id']);

                foreach ($servicio->insumos as $insumo) {
                    $cantidadNecesaria = (float) $insumo->pivot->cantidad_por_unidad * (float) $item['cantidad'];

                    if ($insumo->stock_actual < $cantidadNecesaria) {
                        throw new \Exception(
                            'Stock insuficiente del insumo "' . $insumo->nombre .
                                '" para vender el servicio "' . $servicio->nombre . '".'
                        );
                    }
                }
            }
        }
    }

    private function procesarProductoVenta(Producto $producto, $cantidad, Venta $venta)
    {
        if (!$producto->maneja_inventario) {
            return (float) $producto->costo_unitario;
        }

        $movimiento = MovimientoProducto::create([
            'producto_id' => $producto->id,
            'tipo_movimiento' => 'Salida venta',
            'cantidad' => $cantidad,
            'costo_unitario' => 0,
            'total' => 0,
            'referencia' => $venta->numero,
            'observacion' => 'Salida automática por venta.',
        ]);

        $cantidadPendiente = (float) $cantidad;
        $totalCostoSalida = 0;

        $lotes = LoteProducto::where('producto_id', $producto->id)
            ->where('activo', true)
            ->where('cantidad_disponible', '>', 0)
            ->orderBy('fecha_entrada')
            ->orderBy('id')
            ->get();

        foreach ($lotes as $lote) {
            if ($cantidadPendiente <= 0) {
                break;
            }

            $cantidadTomada = min($cantidadPendiente, (float) $lote->cantidad_disponible);
            $totalDetalle = $cantidadTomada * (float) $lote->costo_unitario;

            MovimientoProductoLote::create([
                'movimiento_producto_id' => $movimiento->id,
                'lote_producto_id' => $lote->id,
                'cantidad' => $cantidadTomada,
                'costo_unitario' => $lote->costo_unitario,
                'total' => $totalDetalle,
            ]);

            $lote->cantidad_disponible = $lote->cantidad_disponible - $cantidadTomada;

            if ($lote->cantidad_disponible <= 0) {
                $lote->cantidad_disponible = 0;
                $lote->activo = false;
            }

            $lote->save();

            $cantidadPendiente -= $cantidadTomada;
            $totalCostoSalida += $totalDetalle;
        }

        if ($cantidadPendiente > 0) {
            throw new \Exception('No hay lotes suficientes para el producto: ' . $producto->nombre);
        }

        $costoUnitario = $cantidad > 0 ? $totalCostoSalida / $cantidad : 0;

        $movimiento->update([
            'costo_unitario' => $costoUnitario,
            'total' => $totalCostoSalida,
        ]);

        $this->actualizarCostoActualPepsProducto($producto);

        return $costoUnitario;
    }

    private function procesarServicioVenta(Servicio $servicio, $cantidad, Venta $venta)
    {
        if ($servicio->insumos->count() === 0) {
            return (float) $servicio->costo_unitario;
        }

        $totalCostoServicio = 0;

        foreach ($servicio->insumos as $insumo) {
            $cantidadNecesaria = (float) $insumo->pivot->cantidad_por_unidad * (float) $cantidad;

            $totalCostoServicio += $this->descontarInsumoPorServicio(
                $insumo,
                $cantidadNecesaria,
                $venta,
                $servicio
            );
        }

        return $cantidad > 0 ? $totalCostoServicio / $cantidad : 0;
    }

    private function descontarInsumoPorServicio(Insumo $insumo, $cantidad, Venta $venta, Servicio $servicio)
    {
        $movimiento = MovimientoInventario::create([
            'insumo_id' => $insumo->id,
            'tipo_movimiento' => 'Salida venta',
            'cantidad' => $cantidad,
            'costo_unitario' => 0,
            'total' => 0,
            'referencia' => $venta->numero,
            'observacion' => 'Salida automática por venta del servicio: ' . $servicio->nombre,
        ]);

        $cantidadPendiente = (float) $cantidad;
        $totalCostoSalida = 0;

        $lotes = LoteInsumo::where('insumo_id', $insumo->id)
            ->where('activo', true)
            ->where('cantidad_disponible', '>', 0)
            ->orderBy('fecha_entrada')
            ->orderBy('id')
            ->get();

        foreach ($lotes as $lote) {
            if ($cantidadPendiente <= 0) {
                break;
            }

            $cantidadTomada = min($cantidadPendiente, (float) $lote->cantidad_disponible);
            $totalDetalle = $cantidadTomada * (float) $lote->costo_unitario;

            MovimientoInventarioLote::create([
                'movimiento_inventario_id' => $movimiento->id,
                'lote_insumo_id' => $lote->id,
                'cantidad' => $cantidadTomada,
                'costo_unitario' => $lote->costo_unitario,
                'total' => $totalDetalle,
            ]);

            $lote->cantidad_disponible = $lote->cantidad_disponible - $cantidadTomada;

            if ($lote->cantidad_disponible <= 0) {
                $lote->cantidad_disponible = 0;
                $lote->activo = false;
            }

            $lote->save();

            $cantidadPendiente -= $cantidadTomada;
            $totalCostoSalida += $totalDetalle;
        }

        if ($cantidadPendiente > 0) {
            throw new \Exception('No hay lotes suficientes para el insumo: ' . $insumo->nombre);
        }

        $costoUnitario = $cantidad > 0 ? $totalCostoSalida / $cantidad : 0;

        $movimiento->update([
            'costo_unitario' => $costoUnitario,
            'total' => $totalCostoSalida,
        ]);

        $this->actualizarCostoActualPepsInsumo($insumo);

        return $totalCostoSalida;
    }

    private function actualizarCostoActualPepsProducto(Producto $producto)
    {
        $stockActual = LoteProducto::where('producto_id', $producto->id)
            ->where('activo', true)
            ->sum('cantidad_disponible');

        $proximoLote = LoteProducto::where('producto_id', $producto->id)
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

    private function actualizarCostoActualPepsInsumo(Insumo $insumo)
    {
        $stockActual = LoteInsumo::where('insumo_id', $insumo->id)
            ->where('activo', true)
            ->sum('cantidad_disponible');

        $proximoLote = LoteInsumo::where('insumo_id', $insumo->id)
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

    private function resetearVenta()
    {
        $this->cliente_id = null;
        $this->searchCliente = '';
        $this->searchItem = '';
        $this->tipoFiltro = 'todos';
        $this->metodo_pago = $this->metodosPago[0] ?? 'Efectivo';
        $this->estado = 'Pagada';
        $this->observacion = null;
        $this->monto_inicial = 0;
        $this->referencia_pago_inicial = null;

        $this->limpiarCarrito();
    }

    public function render()
    {
        $clientes = Cliente::where('activo', true)
            ->when($this->searchCliente, function ($query) {
                $search = '%' . $this->searchCliente . '%';

                $query->where(function ($q) use ($search) {
                    $q->where('primer_nombre', 'like', $search)
                        ->orWhere('segundo_nombre', 'like', $search)
                        ->orWhere('primer_apellido', 'like', $search)
                        ->orWhere('segundo_apellido', 'like', $search)
                        ->orWhere('dni', 'like', $search)
                        ->orWhere('telefono', 'like', $search)
                        ->orWhere('rtn', 'like', $search);
                });
            })
            ->orderBy('primer_nombre')
            ->limit(10)
            ->get();

        $productos = collect();

        if ($this->tipoFiltro === 'todos' || $this->tipoFiltro === 'productos') {
            $productos = Producto::where('activo', true)
                ->when($this->searchItem, function ($query) {
                    $search = '%' . $this->searchItem . '%';

                    $query->where(function ($q) use ($search) {
                        $q->where('codigo', 'like', $search)
                            ->orWhere('codigo_barra', 'like', $search)
                            ->orWhere('nombre', 'like', $search)
                            ->orWhere('categoria', 'like', $search);
                    });
                })
                ->orderBy('nombre')
                ->limit(10)
                ->get();
        }

        $servicios = collect();

        if ($this->tipoFiltro === 'todos' || $this->tipoFiltro === 'servicios') {
            $servicios = Servicio::where('activo', true)
                ->when($this->searchItem, function ($query) {
                    $search = '%' . $this->searchItem . '%';

                    $query->where(function ($q) use ($search) {
                        $q->where('codigo', 'like', $search)
                            ->orWhere('nombre', 'like', $search)
                            ->orWhere('tipo_servicio', 'like', $search);
                    });
                })
                ->orderBy('nombre')
                ->limit(10)
                ->get();
        }

        return view('livewire.ventas.venta-index', [
            'clientes' => $clientes,
            'productos' => $productos,
            'servicios' => $servicios,
        ]);
    }
}
