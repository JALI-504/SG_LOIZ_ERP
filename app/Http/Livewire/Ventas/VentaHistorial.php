<?php

namespace App\Http\Livewire\Ventas;

use App\Models\Catalogo;
use App\Models\Venta;
use Livewire\Component;
use Livewire\WithPagination;

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
            $ventaSeleccionada = Venta::with(['cliente', 'detalles'])
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
