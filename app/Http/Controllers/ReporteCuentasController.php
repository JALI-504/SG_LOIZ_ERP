<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Venta;
use Illuminate\Http\Request;

class ReporteCuentasController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('ver reporte cuentas')) {
            abort(403, 'No tiene permiso para ver el reporte de cuentas.');
        }
        
        $fechaDesde = $request->fecha_desde;
        $fechaHasta = $request->fecha_hasta;
        $filtroComprobante = $request->filtro_comprobante ?? 'todos';

        $ventasPorCobrarQuery = Venta::with(['cliente', 'pagos'])
            ->where('estado', '!=', 'Anulada')
            ->where('saldo_pendiente', '>', 0)
            ->when($fechaDesde, function ($query) use ($fechaDesde) {
                $query->whereDate('fecha', '>=', $fechaDesde);
            })
            ->when($fechaHasta, function ($query) use ($fechaHasta) {
                $query->whereDate('fecha', '<=', $fechaHasta);
            })
            ->when($filtroComprobante === 'fiscal', function ($query) {
                $query->where('es_fiscal', 1);
            })
            ->when($filtroComprobante === 'interno', function ($query) {
                $query->where('es_fiscal', 0);
            });

        $comprasPorPagarQuery = Compra::with(['proveedor', 'pagos'])
            ->where('estado', '!=', 'Anulada')
            ->where('saldo_pendiente', '>', 0)
            ->when($fechaDesde, function ($query) use ($fechaDesde) {
                $query->whereDate('fecha', '>=', $fechaDesde);
            })
            ->when($fechaHasta, function ($query) use ($fechaHasta) {
                $query->whereDate('fecha', '<=', $fechaHasta);
            });

        $ventasPorCobrar = $ventasPorCobrarQuery
            ->orderBy('fecha')
            ->orderBy('id')
            ->get();

        $comprasPorPagar = $comprasPorPagarQuery
            ->orderBy('fecha')
            ->orderBy('id')
            ->get();

        $totalPorCobrar = $ventasPorCobrar->sum('saldo_pendiente');
        $totalPorPagar = $comprasPorPagar->sum('saldo_pendiente');

        $totalVentasOriginal = $ventasPorCobrar->sum('total');
        $totalVentasPagado = $ventasPorCobrar->sum('monto_pagado');

        $totalPagosRecibidosClientes = $ventasPorCobrar->sum(function ($venta) {
            return $venta->pagos->where('estado', 'Activo')->sum('monto');
        });

        $totalRetencionVentas = $ventasPorCobrar->sum('retencion');

        $totalFacturasFiscalesPendientes = $ventasPorCobrar
            ->where('es_fiscal', 1)
            ->count();

        $totalRecibosInternosPendientes = $ventasPorCobrar
            ->where('es_fiscal', 0)
            ->count();

        $totalComprasOriginal = $comprasPorPagar->sum('total');
        $totalComprasPagado = $comprasPorPagar->sum(function ($compra) {
            return $compra->pagos->where('estado', 'Activo')->sum('monto');
        });

        $diferencia = $totalPorCobrar - $totalPorPagar;

        return view('reportes.cuentas', [
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'filtroComprobante' => $filtroComprobante,

            'ventasPorCobrar' => $ventasPorCobrar,
            'comprasPorPagar' => $comprasPorPagar,

            'totalPorCobrar' => $totalPorCobrar,
            'totalPorPagar' => $totalPorPagar,

            'totalVentasOriginal' => $totalVentasOriginal,
            'totalVentasPagado' => $totalVentasPagado,
            'totalPagosRecibidosClientes' => $totalPagosRecibidosClientes,
            'totalRetencionVentas' => $totalRetencionVentas,

            'totalFacturasFiscalesPendientes' => $totalFacturasFiscalesPendientes,
            'totalRecibosInternosPendientes' => $totalRecibosInternosPendientes,

            'totalComprasOriginal' => $totalComprasOriginal,
            'totalComprasPagado' => $totalComprasPagado,

            'diferencia' => $diferencia,
        ]);
    }
}
