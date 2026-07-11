<?php

namespace App\Http\Livewire\Ventas;

use App\Models\Catalogo;
use App\Models\Venta;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\MovimientoInventario;
use App\Models\MovimientoInventarioLote;
use App\Models\MovimientoProducto;
use App\Models\MovimientoProductoLote;
use Illuminate\Support\Facades\DB;

class VentaHistorial extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $fechaDesde;
    public $fechaHasta;
    public $filtroEstado = 'todos';
    public $filtroMetodoPago = 'todos';
    public $perPage = 10;

    public $estadosVenta = [];
    public $metodosPago = [];

    public $ventaSeleccionadaId = null;

    public function mount()
    {
        $this->estadosVenta = Catalogo::opciones('estado_venta')->pluck('nombre')->toArray();
        $this->metodosPago = Catalogo::opciones('metodo_pago')->pluck('nombre')->toArray();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFechaDesde()
    {
        $this->resetPage();
    }

    public function updatingFechaHasta()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function updatingFiltroMetodoPago()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function verDetalle($ventaId)
    {
        if ($this->ventaSeleccionadaId == $ventaId) {
            $this->ventaSeleccionadaId = null;
            return;
        }

        $this->ventaSeleccionadaId = $ventaId;
    }

    public function cerrarDetalle()
    {
        $this->ventaSeleccionadaId = null;
    }

    public function anularVenta($ventaId)
    {
        $venta = Venta::with('detalles')->findOrFail($ventaId);

        if ($venta->estado === 'Anulada') {
            session()->flash('error', 'Esta venta ya está anulada.');
            return;
        }

        try {
            DB::transaction(function () use ($venta) {
                $this->revertirMovimientosProductos($venta);
                $this->revertirMovimientosInsumos($venta);

                $observacionAnterior = $venta->observacion ? $venta->observacion . "\n" : '';

                $venta->update([
                    'estado' => 'Anulada',
                    'observacion' => $observacionAnterior . 'Venta anulada el ' . now()->format('d/m/Y H:i'),
                ]);
            });

            $this->ventaSeleccionadaId = null;

            session()->flash('message', 'Venta anulada correctamente. El inventario fue restaurado.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    private function revertirMovimientosProductos(Venta $venta)
    {
        $movimientos = MovimientoProducto::with(['detalleLotes.lote', 'producto'])
            ->where('referencia', $venta->numero)
            ->where('tipo_movimiento', 'Salida venta')
            ->get();

        foreach ($movimientos as $movimiento) {
            $devolucion = MovimientoProducto::create([
                'producto_id' => $movimiento->producto_id,
                'tipo_movimiento' => 'Devolucion',
                'cantidad' => $movimiento->cantidad,
                'costo_unitario' => $movimiento->costo_unitario,
                'total' => $movimiento->total,
                'referencia' => $venta->numero,
                'observacion' => 'Devolución automática por anulación de venta.',
            ]);

            foreach ($movimiento->detalleLotes as $detalle) {
                $lote = $detalle->lote;

                if (!$lote) {
                    continue;
                }

                $lote->cantidad_disponible = $lote->cantidad_disponible + $detalle->cantidad;
                $lote->activo = true;
                $lote->save();

                MovimientoProductoLote::create([
                    'movimiento_producto_id' => $devolucion->id,
                    'lote_producto_id' => $lote->id,
                    'cantidad' => $detalle->cantidad,
                    'costo_unitario' => $detalle->costo_unitario,
                    'total' => $detalle->total,
                ]);
            }

            if ($movimiento->producto) {
                $this->actualizarCostoActualPepsProducto($movimiento->producto);
            }
        }
    }

    private function revertirMovimientosInsumos(Venta $venta)
    {
        $movimientos = MovimientoInventario::with(['detalleLotes.lote', 'insumo'])
            ->where('referencia', $venta->numero)
            ->where('tipo_movimiento', 'Salida venta')
            ->get();

        foreach ($movimientos as $movimiento) {
            $devolucion = MovimientoInventario::create([
                'insumo_id' => $movimiento->insumo_id,
                'tipo_movimiento' => 'Devolucion',
                'cantidad' => $movimiento->cantidad,
                'costo_unitario' => $movimiento->costo_unitario,
                'total' => $movimiento->total,
                'referencia' => $venta->numero,
                'observacion' => 'Devolución automática por anulación de venta.',
            ]);

            foreach ($movimiento->detalleLotes as $detalle) {
                $lote = $detalle->lote;

                if (!$lote) {
                    continue;
                }

                $lote->cantidad_disponible = $lote->cantidad_disponible + $detalle->cantidad;
                $lote->activo = true;
                $lote->save();

                MovimientoInventarioLote::create([
                    'movimiento_inventario_id' => $devolucion->id,
                    'lote_insumo_id' => $lote->id,
                    'cantidad' => $detalle->cantidad,
                    'costo_unitario' => $detalle->costo_unitario,
                    'total' => $detalle->total,
                ]);
            }

            if ($movimiento->insumo) {
                $this->actualizarCostoActualPepsInsumo($movimiento->insumo);
            }
        }
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

    private function queryVentas()
    {
        return Venta::with('cliente')
            ->when($this->search, function ($query) {
                $search = '%' . $this->search . '%';

                $query->where(function ($q) use ($search) {
                    $q->where('numero', 'like', $search)
                        ->orWhere('metodo_pago', 'like', $search)
                        ->orWhere('estado', 'like', $search)
                        ->orWhereHas('cliente', function ($clienteQuery) use ($search) {
                            $clienteQuery->where('primer_nombre', 'like', $search)
                                ->orWhere('segundo_nombre', 'like', $search)
                                ->orWhere('primer_apellido', 'like', $search)
                                ->orWhere('segundo_apellido', 'like', $search)
                                ->orWhere('dni', 'like', $search)
                                ->orWhere('rtn', 'like', $search)
                                ->orWhere('telefono', 'like', $search);
                        });
                });
            })
            ->when($this->fechaDesde, function ($query) {
                $query->whereDate('fecha', '>=', $this->fechaDesde);
            })
            ->when($this->fechaHasta, function ($query) {
                $query->whereDate('fecha', '<=', $this->fechaHasta);
            })
            ->when($this->filtroEstado !== 'todos', function ($query) {
                $query->where('estado', $this->filtroEstado);
            })
            ->when($this->filtroMetodoPago !== 'todos', function ($query) {
                $query->where('metodo_pago', $this->filtroMetodoPago);
            });
    }

    public function render()
    {
        $query = $this->queryVentas();

        $totalVentas = (clone $query)->count();
        $totalMonto = (clone $query)->sum('total');
        $totalDescuento = (clone $query)->sum('descuento');

        $ventas = $query
            ->orderByDesc('id')
            ->paginate($this->perPage);

        $ventaSeleccionada = null;

        if ($this->ventaSeleccionadaId) {
            $ventaSeleccionada = Venta::with(['cliente', 'detalles', 'pagos'])
                ->find($this->ventaSeleccionadaId);
        }

        return view('livewire.ventas.venta-historial', [
            'ventas' => $ventas,
            'totalVentas' => $totalVentas,
            'totalMonto' => $totalMonto,
            'totalDescuento' => $totalDescuento,
            'ventaSeleccionada' => $ventaSeleccionada,
        ]);
    }
    
}
