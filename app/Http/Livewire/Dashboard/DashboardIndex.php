<?php

namespace App\Http\Livewire\Dashboard;

use App\Models\Compra;
use App\Models\Gasto;
use App\Models\PagoCompra;
use App\Models\Insumo;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardIndex extends Component
{
    private function autorizarVerDashboard()
    {
        if (!auth()->user()->can('ver dashboard')) {
            abort(403, 'No tiene permiso para ver el dashboard.');
        }
    }

    public function mount()
    {
        $this->autorizarVerDashboard();
    }

    public function render()
    {
        $this->autorizarVerDashboard();

        $hoy = now()->format('Y-m-d');

        $ventasHoyQuery = Venta::whereDate('fecha', $hoy);

        $ventasValidasHoyQuery = Venta::whereDate('fecha', $hoy)
            ->where('estado', '!=', 'Anulada');

        $totalVentasHoy = (clone $ventasValidasHoyQuery)->count();

        $totalVendidoHoy = (clone $ventasValidasHoyQuery)->sum('total');

        $totalDescuentosHoy = (clone $ventasValidasHoyQuery)->sum('descuento');

        $totalSubtotalGravadoHoy = (clone $ventasValidasHoyQuery)->sum('subtotal_gravado');
        $totalSubtotalExentoHoy = (clone $ventasValidasHoyQuery)->sum('subtotal_exento');
        $totalSubtotalNoSujetoHoy = (clone $ventasValidasHoyQuery)->sum('subtotal_no_sujeto');
        $totalIsv15Hoy = (clone $ventasValidasHoyQuery)->sum('isv_15');
        $totalRetencionHoy = (clone $ventasValidasHoyQuery)->sum('retencion');

        $totalNetoRecibidoHoy = (clone $ventasValidasHoyQuery)
            ->select(DB::raw('SUM(CASE WHEN neto_recibido > 0 THEN neto_recibido ELSE total - IFNULL(retencion, 0) END) as total'))
            ->value('total') ?? 0;

        $totalFacturasFiscalesHoy = (clone $ventasValidasHoyQuery)
            ->where('es_fiscal', 1)
            ->count();

        $totalRecibosInternosHoy = (clone $ventasValidasHoyQuery)
            ->where('es_fiscal', 0)
            ->count();

        $gastosHoyQuery = Gasto::query()
            ->where('estado', 'Registrado')
            ->whereDate('fecha', $hoy);

        $totalGastosHoy = (clone $gastosHoyQuery)->sum('monto');
        $cantidadGastosHoy = (clone $gastosHoyQuery)->count();

        $pagosProveedoresHoyQuery = PagoCompra::query()
            ->where('estado', 'Activo')
            ->whereDate('fecha', $hoy);

        $totalPagosProveedoresActivosHoy = (clone $pagosProveedoresHoyQuery)->sum('monto');
        $cantidadPagosProveedoresActivosHoy = (clone $pagosProveedoresHoyQuery)->count();

        $totalEgresosOperativosHoy = $totalGastosHoy + $totalPagosProveedoresActivosHoy;
        $flujoNetoEstimadoHoy = $totalNetoRecibidoHoy - $totalEgresosOperativosHoy;

        $cuentasPorPagarPendientes = Compra::query()
            ->where('estado', '!=', 'Anulada')
            ->where('saldo_pendiente', '>', 0)
            ->sum('saldo_pendiente');

        $cantidadCuentasPorPagarPendientes = Compra::query()
            ->where('estado', '!=', 'Anulada')
            ->where('saldo_pendiente', '>', 0)
            ->count();

        $ventasPendientesHoy = (clone $ventasHoyQuery)
            ->where('estado', 'Pendiente')
            ->count();

        $ventasAnuladasHoy = (clone $ventasHoyQuery)
            ->where('estado', 'Anulada')
            ->count();

        $utilidadEstimadaHoy = VentaDetalle::join('ventas', 'venta_detalles.venta_id', '=', 'ventas.id')
            ->whereDate('ventas.fecha', $hoy)
            ->where('ventas.estado', '!=', 'Anulada')
            ->select(DB::raw('SUM(venta_detalles.total - (venta_detalles.costo_unitario * venta_detalles.cantidad)) as utilidad'))
            ->value('utilidad') ?? 0;

        $costoEstimadoHoy = VentaDetalle::join('ventas', 'venta_detalles.venta_id', '=', 'ventas.id')
            ->whereDate('ventas.fecha', $hoy)
            ->where('ventas.estado', '!=', 'Anulada')
            ->select(DB::raw('SUM(venta_detalles.costo_unitario * venta_detalles.cantidad) as costo'))
            ->value('costo') ?? 0;

        $ticketPromedioHoy = $totalVentasHoy > 0
            ? $totalVendidoHoy / $totalVentasHoy
            : 0;

        $productosStockBajo = Producto::where('activo', true)
            ->where('maneja_inventario', true)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->orderBy('stock_actual')
            ->limit(8)
            ->get();

        $insumosStockBajo = Insumo::where('activo', true)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->orderBy('stock_actual')
            ->limit(8)
            ->get();

        $ultimasVentas = Venta::with('cliente')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        $productosMasVendidosHoy = VentaDetalle::join('ventas', 'venta_detalles.venta_id', '=', 'ventas.id')
            ->whereDate('ventas.fecha', $hoy)
            ->where('ventas.estado', '!=', 'Anulada')
            ->where('venta_detalles.tipo_item', 'Producto')
            ->select(
                'venta_detalles.codigo',
                'venta_detalles.descripcion',
                DB::raw('SUM(venta_detalles.cantidad) as cantidad_total'),
                DB::raw('SUM(venta_detalles.total) as total_vendido')
            )
            ->groupBy('venta_detalles.codigo', 'venta_detalles.descripcion')
            ->orderByDesc('cantidad_total')
            ->limit(5)
            ->get();

        $serviciosMasVendidosHoy = VentaDetalle::join('ventas', 'venta_detalles.venta_id', '=', 'ventas.id')
            ->whereDate('ventas.fecha', $hoy)
            ->where('ventas.estado', '!=', 'Anulada')
            ->where('venta_detalles.tipo_item', 'Servicio')
            ->select(
                'venta_detalles.codigo',
                'venta_detalles.descripcion',
                DB::raw('SUM(venta_detalles.cantidad) as cantidad_total'),
                DB::raw('SUM(venta_detalles.total) as total_vendido')
            )
            ->groupBy('venta_detalles.codigo', 'venta_detalles.descripcion')
            ->orderByDesc('cantidad_total')
            ->limit(5)
            ->get();

        $graficaVentas7DiasLabels = [];
        $graficaVentas7DiasMontos = [];
        $graficaNetoRecibido7Dias = [];
        $graficaEgresos7Dias = [];
        $graficaFlujo7Dias = [];

        for ($i = 6; $i >= 0; $i--) {
            $fecha = now()->subDays($i)->format('Y-m-d');
            $label = now()->subDays($i)->format('d/m');

            $ventasDiaQuery = Venta::query()
                ->where('estado', '!=', 'Anulada')
                ->whereDate('fecha', $fecha);

            $totalVendidoDia = (clone $ventasDiaQuery)->sum('total');

            $netoRecibidoDia = (clone $ventasDiaQuery)
                ->select(DB::raw('SUM(CASE WHEN neto_recibido > 0 THEN neto_recibido ELSE total - IFNULL(retencion, 0) END) as total'))
                ->value('total') ?? 0;

            $totalGastosDia = Gasto::query()
                ->where('estado', 'Registrado')
                ->whereDate('fecha', $fecha)
                ->sum('monto');

            $totalPagosProveedoresDia = PagoCompra::query()
                ->where('estado', 'Activo')
                ->whereDate('fecha', $fecha)
                ->sum('monto');

            $egresosDia = $totalGastosDia + $totalPagosProveedoresDia;
            $flujoDia = $netoRecibidoDia - $egresosDia;

            $graficaVentas7DiasLabels[] = $label;
            $graficaVentas7DiasMontos[] = round($totalVendidoDia, 2);
            $graficaNetoRecibido7Dias[] = round($netoRecibidoDia, 2);
            $graficaEgresos7Dias[] = round($egresosDia, 2);
            $graficaFlujo7Dias[] = round($flujoDia, 2);
        }

        $graficaProductosLabels = $productosMasVendidosHoy->map(function ($item) {
            return strlen($item->descripcion) > 35
                ? substr($item->descripcion, 0, 35) . '...'
                : $item->descripcion;
        })->toArray();

        $graficaProductosCantidades = $productosMasVendidosHoy->pluck('cantidad_total')
            ->map(function ($cantidad) {
                return (float) $cantidad;
            })
            ->toArray();

        $graficaServiciosLabels = $serviciosMasVendidosHoy->map(function ($item) {
            return strlen($item->descripcion) > 35
                ? substr($item->descripcion, 0, 35) . '...'
                : $item->descripcion;
        })->toArray();

        $graficaServiciosCantidades = $serviciosMasVendidosHoy->pluck('cantidad_total')
            ->map(function ($cantidad) {
                return (float) $cantidad;
            })
            ->toArray();

        return view('livewire.dashboard.dashboard-index', [
            'hoy' => $hoy,

            'totalVentasHoy' => $totalVentasHoy,
            'totalVendidoHoy' => $totalVendidoHoy,
            'totalDescuentosHoy' => $totalDescuentosHoy,

            'totalSubtotalGravadoHoy' => $totalSubtotalGravadoHoy,
            'totalSubtotalExentoHoy' => $totalSubtotalExentoHoy,
            'totalSubtotalNoSujetoHoy' => $totalSubtotalNoSujetoHoy,
            'totalIsv15Hoy' => $totalIsv15Hoy,
            'totalRetencionHoy' => $totalRetencionHoy,
            'totalNetoRecibidoHoy' => $totalNetoRecibidoHoy,
            'totalFacturasFiscalesHoy' => $totalFacturasFiscalesHoy,
            'totalRecibosInternosHoy' => $totalRecibosInternosHoy,

            'totalGastosHoy' => $totalGastosHoy,
            'cantidadGastosHoy' => $cantidadGastosHoy,
            'totalPagosProveedoresActivosHoy' => $totalPagosProveedoresActivosHoy,
            'cantidadPagosProveedoresActivosHoy' => $cantidadPagosProveedoresActivosHoy,
            'totalEgresosOperativosHoy' => $totalEgresosOperativosHoy,
            'flujoNetoEstimadoHoy' => $flujoNetoEstimadoHoy,
            'cuentasPorPagarPendientes' => $cuentasPorPagarPendientes,
            'cantidadCuentasPorPagarPendientes' => $cantidadCuentasPorPagarPendientes,

            'ventasPendientesHoy' => $ventasPendientesHoy,
            'ventasAnuladasHoy' => $ventasAnuladasHoy,
            'utilidadEstimadaHoy' => $utilidadEstimadaHoy,
            'costoEstimadoHoy' => $costoEstimadoHoy,
            'ticketPromedioHoy' => $ticketPromedioHoy,

            'productosStockBajo' => $productosStockBajo,
            'insumosStockBajo' => $insumosStockBajo,
            'ultimasVentas' => $ultimasVentas,
            'productosMasVendidosHoy' => $productosMasVendidosHoy,
            'serviciosMasVendidosHoy' => $serviciosMasVendidosHoy,

            'graficaVentas7DiasLabels' => $graficaVentas7DiasLabels,
            'graficaVentas7DiasMontos' => $graficaVentas7DiasMontos,
            'graficaNetoRecibido7Dias' => $graficaNetoRecibido7Dias,
            'graficaEgresos7Dias' => $graficaEgresos7Dias,
            'graficaFlujo7Dias' => $graficaFlujo7Dias,

            'graficaProductosLabels' => $graficaProductosLabels,
            'graficaProductosCantidades' => $graficaProductosCantidades,
            'graficaServiciosLabels' => $graficaServiciosLabels,
            'graficaServiciosCantidades' => $graficaServiciosCantidades,
        ]);
    }
}
