<?php

namespace App\Http\Livewire\Reportes;

use App\Models\Catalogo;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class ReporteVentas extends Component
{
    public $fechaDesde;
    public $fechaHasta;

    public $filtroMetodoPago = 'todos';
    public $filtroTipoItem = 'todos';

    public $metodosPago = [];

    public function mount()
    {
        $this->fechaDesde = now()->format('Y-m-d');
        $this->fechaHasta = now()->format('Y-m-d');

        $this->metodosPago = Catalogo::opciones('metodo_pago')
            ->pluck('nombre')
            ->toArray();
    }

    public function limpiarFiltros()
    {
        $this->fechaDesde = now()->format('Y-m-d');
        $this->fechaHasta = now()->format('Y-m-d');
        $this->filtroMetodoPago = 'todos';
        $this->filtroTipoItem = 'todos';
    }

    private function queryVentas()
    {
        return Venta::query()
            ->when($this->fechaDesde, function ($query) {
                $query->whereDate('fecha', '>=', $this->fechaDesde);
            })
            ->when($this->fechaHasta, function ($query) {
                $query->whereDate('fecha', '<=', $this->fechaHasta);
            })
            ->when($this->filtroMetodoPago !== 'todos', function ($query) {
                $query->where('metodo_pago', $this->filtroMetodoPago);
            });
    }

    private function queryDetalles()
    {
        return VentaDetalle::query()
            ->join('ventas', 'venta_detalles.venta_id', '=', 'ventas.id')
            ->where('ventas.estado', '!=', 'Anulada')
            ->when($this->fechaDesde, function ($query) {
                $query->whereDate('ventas.fecha', '>=', $this->fechaDesde);
            })
            ->when($this->fechaHasta, function ($query) {
                $query->whereDate('ventas.fecha', '<=', $this->fechaHasta);
            })
            ->when($this->filtroMetodoPago !== 'todos', function ($query) {
                $query->where('ventas.metodo_pago', $this->filtroMetodoPago);
            })
            ->when($this->filtroTipoItem !== 'todos', function ($query) {
                $query->where('venta_detalles.tipo_item', $this->filtroTipoItem);
            });
    }

    public function render()
    {
        $ventasQuery = $this->queryVentas();

        $ventasValidasQuery = (clone $ventasQuery)
            ->where('estado', '!=', 'Anulada');

        $ventasAnuladasQuery = (clone $ventasQuery)
            ->where('estado', 'Anulada');

        $totalVentas = (clone $ventasValidasQuery)->count();

        $totalVendido = (clone $ventasValidasQuery)->sum('total');

        $totalDescuentos = (clone $ventasValidasQuery)->sum('descuento');

        $totalAnuladas = (clone $ventasAnuladasQuery)->count();

        $montoAnulado = (clone $ventasAnuladasQuery)->sum('total');

        $ticketPromedio = $totalVentas > 0
            ? $totalVendido / $totalVentas
            : 0;

        $detallesQuery = $this->queryDetalles();

        $utilidadEstimada = (clone $detallesQuery)
            ->select(DB::raw('SUM(venta_detalles.total - (venta_detalles.costo_unitario * venta_detalles.cantidad)) as utilidad'))
            ->value('utilidad') ?? 0;

        $costoEstimado = (clone $detallesQuery)
            ->select(DB::raw('SUM(venta_detalles.costo_unitario * venta_detalles.cantidad) as costo'))
            ->value('costo') ?? 0;

        $ventasPorMetodo = (clone $ventasValidasQuery)
            ->select(
                'metodo_pago',
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('metodo_pago')
            ->orderByDesc('total')
            ->get();

        $itemsMasVendidos = (clone $detallesQuery)
            ->select(
                'venta_detalles.tipo_item',
                'venta_detalles.codigo',
                'venta_detalles.descripcion',
                DB::raw('SUM(venta_detalles.cantidad) as cantidad_total'),
                DB::raw('SUM(venta_detalles.total) as total_vendido'),
                DB::raw('SUM(venta_detalles.total - (venta_detalles.costo_unitario * venta_detalles.cantidad)) as utilidad')
            )
            ->groupBy(
                'venta_detalles.tipo_item',
                'venta_detalles.codigo',
                'venta_detalles.descripcion'
            )
            ->orderByDesc('cantidad_total')
            ->limit(10)
            ->get();

        $ultimasVentas = (clone $ventasQuery)
            ->with('cliente')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('livewire.reportes.reporte-ventas', [
            'totalVentas' => $totalVentas,
            'totalVendido' => $totalVendido,
            'totalDescuentos' => $totalDescuentos,
            'totalAnuladas' => $totalAnuladas,
            'montoAnulado' => $montoAnulado,
            'ticketPromedio' => $ticketPromedio,
            'utilidadEstimada' => $utilidadEstimada,
            'costoEstimado' => $costoEstimado,
            'ventasPorMetodo' => $ventasPorMetodo,
            'itemsMasVendidos' => $itemsMasVendidos,
            'ultimasVentas' => $ultimasVentas,
        ]);
    }
}
