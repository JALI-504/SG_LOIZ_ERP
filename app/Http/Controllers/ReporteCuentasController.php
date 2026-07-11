<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Venta;
use Illuminate\Http\Request;

class ReporteCuentasController extends Controller
{
    public function index(Request $request)
    {
        $fechaDesde = $request->fecha_desde;
        $fechaHasta = $request->fecha_hasta;

        $ventasPorCobrarQuery = Venta::with('cliente')
            ->where('estado', '!=', 'Anulada')
            ->where('saldo_pendiente', '>', 0)
            ->when($fechaDesde, function ($query) use ($fechaDesde) {
                $query->whereDate('fecha', '>=', $fechaDesde);
            })
            ->when($fechaHasta, function ($query) use ($fechaHasta) {
                $query->whereDate('fecha', '<=', $fechaHasta);
            });

        $comprasPorPagarQuery = Compra::with('proveedor')
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

        $totalComprasOriginal = $comprasPorPagar->sum('total');
        $totalComprasPagado = $comprasPorPagar->sum('monto_pagado');

        $diferencia = $totalPorCobrar - $totalPorPagar;

        return view('reportes.cuentas', [
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,

            'ventasPorCobrar' => $ventasPorCobrar,
            'comprasPorPagar' => $comprasPorPagar,

            'totalPorCobrar' => $totalPorCobrar,
            'totalPorPagar' => $totalPorPagar,

            'totalVentasOriginal' => $totalVentasOriginal,
            'totalVentasPagado' => $totalVentasPagado,

            'totalComprasOriginal' => $totalComprasOriginal,
            'totalComprasPagado' => $totalComprasPagado,

            'diferencia' => $diferencia,
        ]);
    }
}
