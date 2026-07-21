<?php

namespace App\Http\Livewire\Dashboard;

use App\Models\Insumo;
use App\Models\Producto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DashboardIndex extends Component
{
    public function render()
    {
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
        ]);
    }
}
