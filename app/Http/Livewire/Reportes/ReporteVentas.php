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
    public $filtroComprobante = 'todos';

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
        $this->filtroComprobante = 'todos';
    }

    public function exportarCsv()
    {
        $ventasQuery = $this->queryVentas();

        $ventasValidasQuery = (clone $ventasQuery)
            ->where('estado', '!=', 'Anulada');

        $totalVentas = (clone $ventasValidasQuery)->count();
        $totalVendido = (clone $ventasValidasQuery)->sum('total');
        $totalDescuentos = (clone $ventasValidasQuery)->sum('descuento');

        $detalles = VentaDetalle::query()
            ->join('ventas', 'venta_detalles.venta_id', '=', 'ventas.id')
            ->leftJoin('clientes', 'ventas.cliente_id', '=', 'clientes.id')
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
            })
            ->select(
                'ventas.fecha',
                'ventas.hora',
                'ventas.numero',
                'ventas.metodo_pago',
                'ventas.estado',
                DB::raw("TRIM(CONCAT_WS(' ', clientes.primer_nombre, clientes.segundo_nombre, clientes.primer_apellido, clientes.segundo_apellido)) as cliente"),
                'venta_detalles.tipo_item',
                'venta_detalles.codigo',
                'venta_detalles.descripcion',
                'venta_detalles.cantidad',
                'venta_detalles.precio_unitario',
                'venta_detalles.costo_unitario',
                'venta_detalles.descuento',
                'venta_detalles.total',
                DB::raw('(venta_detalles.total - (venta_detalles.costo_unitario * venta_detalles.cantidad)) as utilidad')
            )
            ->orderBy('ventas.fecha')
            ->orderBy('ventas.id')
            ->get();

        $fechaDesde = $this->fechaDesde ?: 'inicio';
        $fechaHasta = $this->fechaHasta ?: 'actual';

        $nombreArchivo = 'reporte_ventas_' . $fechaDesde . '_al_' . $fechaHasta . '.csv';

        return response()->streamDownload(function () use ($detalles, $totalVentas, $totalVendido, $totalDescuentos) {
            $handle = fopen('php://output', 'w');

            // BOM para que Excel reconozca tildes y eñes.
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, ['Reporte de ventas'], ';');
            fputcsv($handle, ['Fecha de generación', now()->format('d/m/Y H:i')], ';');
            fputcsv($handle, [], ';');

            fputcsv($handle, ['Resumen'], ';');
            fputcsv($handle, ['Ventas válidas', $totalVentas], ';');
            fputcsv($handle, ['Total vendido', number_format($totalVendido, 2, '.', '')], ';');
            fputcsv($handle, ['Total descuentos', number_format($totalDescuentos, 2, '.', '')], ';');
            fputcsv($handle, [], ';');

            fputcsv($handle, [
                'Fecha',
                'Hora',
                'Número',
                'Cliente',
                'Método pago',
                'Estado',
                'Tipo',
                'Código',
                'Descripción',
                'Cantidad',
                'Precio unitario',
                'Costo unitario',
                'Descuento',
                'Total',
                'Utilidad estimada',
            ], ';');

            foreach ($detalles as $detalle) {
                fputcsv($handle, [
                    $detalle->fecha,
                    $detalle->hora,
                    $detalle->numero,
                    $detalle->cliente ?: 'Consumidor final',
                    $detalle->metodo_pago,
                    $detalle->estado,
                    $detalle->tipo_item,
                    $detalle->codigo,
                    $detalle->descripcion,
                    number_format($detalle->cantidad, 2, '.', ''),
                    number_format($detalle->precio_unitario, 2, '.', ''),
                    number_format($detalle->costo_unitario, 4, '.', ''),
                    number_format($detalle->descuento, 2, '.', ''),
                    number_format($detalle->total, 2, '.', ''),
                    number_format($detalle->utilidad, 2, '.', ''),
                ], ';');
            }

            fclose($handle);
        }, $nombreArchivo, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
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
            })
            ->when($this->filtroComprobante === 'fiscal', function ($query) {
                $query->where('es_fiscal', 1);
            })
            ->when($this->filtroComprobante === 'interno', function ($query) {
                $query->where('es_fiscal', 0);
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
            })
            ->when($this->filtroComprobante === 'fiscal', function ($query) {
                $query->where('ventas.es_fiscal', 1);
            })
            ->when($this->filtroComprobante === 'interno', function ($query) {
                $query->where('ventas.es_fiscal', 0);
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

        $totalSubtotalGravado = (clone $ventasValidasQuery)->sum('subtotal_gravado');
        $totalSubtotalExento = (clone $ventasValidasQuery)->sum('subtotal_exento');
        $totalSubtotalNoSujeto = (clone $ventasValidasQuery)->sum('subtotal_no_sujeto');
        $totalIsv15 = (clone $ventasValidasQuery)->sum('isv_15');
        $totalRetencion = (clone $ventasValidasQuery)->sum('retencion');
        $totalNetoRecibido = (clone $ventasValidasQuery)->sum('neto_recibido');

        $totalFacturasFiscales = (clone $ventasValidasQuery)
            ->where('es_fiscal', 1)
            ->count();

        $totalRecibosInternos = (clone $ventasValidasQuery)
            ->where('es_fiscal', 0)
            ->count();

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
            'totalSubtotalGravado' => $totalSubtotalGravado,
            'totalSubtotalExento' => $totalSubtotalExento,
            'totalSubtotalNoSujeto' => $totalSubtotalNoSujeto,
            'totalIsv15' => $totalIsv15,
            'totalRetencion' => $totalRetencion,
            'totalNetoRecibido' => $totalNetoRecibido,
            'totalFacturasFiscales' => $totalFacturasFiscales,
            'totalRecibosInternos' => $totalRecibosInternos,
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

    public function exportarExcel()
    {
        $detalles = VentaDetalle::query()
            ->join('ventas', 'venta_detalles.venta_id', '=', 'ventas.id')
            ->leftJoin('clientes', 'ventas.cliente_id', '=', 'clientes.id')
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
            })
            ->when($this->filtroComprobante === 'fiscal', function ($query) {
                $query->where('ventas.es_fiscal', 1);
            })
            ->when($this->filtroComprobante === 'interno', function ($query) {
                $query->where('ventas.es_fiscal', 0);
            })
            ->select(
                'ventas.fecha',
                'ventas.hora',
                'ventas.numero',
                'ventas.tipo_comprobante',
                'ventas.es_fiscal',
                'ventas.cai',
                'ventas.rango_autorizado_desde',
                'ventas.rango_autorizado_hasta',
                'ventas.fecha_limite_emision',
                'ventas.metodo_pago',
                'ventas.estado',

                'ventas.subtotal_gravado',
                'ventas.subtotal_exento',
                'ventas.subtotal_no_sujeto',
                'ventas.isv_15',
                'ventas.retencion',
                'ventas.neto_recibido',

                DB::raw("IFNULL(TRIM(CONCAT_WS(' ', clientes.primer_nombre, clientes.segundo_nombre, clientes.primer_apellido, clientes.segundo_apellido)), 'Consumidor final') as cliente"),

                'venta_detalles.tipo_item',
                'venta_detalles.codigo',
                'venta_detalles.descripcion',
                'venta_detalles.cantidad',
                'venta_detalles.precio_unitario',
                'venta_detalles.costo_unitario',
                'venta_detalles.tipo_impuesto',
                'venta_detalles.porcentaje_isv',
                'venta_detalles.subtotal_gravado as detalle_subtotal_gravado',
                'venta_detalles.subtotal_exento as detalle_subtotal_exento',
                'venta_detalles.subtotal_no_sujeto as detalle_subtotal_no_sujeto',
                'venta_detalles.impuesto as detalle_impuesto',
                'venta_detalles.descuento',
                'venta_detalles.total',

                DB::raw('(venta_detalles.total - (venta_detalles.costo_unitario * venta_detalles.cantidad)) as utilidad')
            )
            ->orderBy('ventas.fecha')
            ->orderBy('ventas.id')
            ->get();

        $fechaDesde = $this->fechaDesde ?: 'inicio';
        $fechaHasta = $this->fechaHasta ?: 'actual';

        $nombreArchivo = 'reporte_ventas_' . $fechaDesde . '_al_' . $fechaHasta . '.xls';

        $html = view('reportes.excel.ventas', [
            'detalles' => $detalles,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'generado' => now()->format('d/m/Y H:i'),
        ])->render();

        return response()->streamDownload(function () use ($html) {
            echo $html;
        }, $nombreArchivo, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
        ]);
    }
}
