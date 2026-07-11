<?php

namespace App\Http\Controllers;

use App\Models\Compra;
use App\Models\Gasto;
use App\Models\Venta;
use App\Models\VentaDetalle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteFinancieroController extends Controller
{
    public function index(Request $request)
    {
        $fechaDesde = $request->fecha_desde ?: now()->startOfMonth()->format('Y-m-d');
        $fechaHasta = $request->fecha_hasta ?: now()->format('Y-m-d');

        $ventasQuery = Venta::query()
            ->where('estado', '!=', 'Anulada')
            ->whereDate('fecha', '>=', $fechaDesde)
            ->whereDate('fecha', '<=', $fechaHasta);

        $totalVentas = (clone $ventasQuery)->sum('total');
        $totalDescuentosVentas = (clone $ventasQuery)->sum('descuento');
        $cantidadVentas = (clone $ventasQuery)->count();

        $costoVentas = VentaDetalle::join('ventas', 'venta_detalles.venta_id', '=', 'ventas.id')
            ->where('ventas.estado', '!=', 'Anulada')
            ->whereDate('ventas.fecha', '>=', $fechaDesde)
            ->whereDate('ventas.fecha', '<=', $fechaHasta)
            ->select(DB::raw('SUM(venta_detalles.costo_unitario * venta_detalles.cantidad) as costo'))
            ->value('costo') ?? 0;

        $utilidadBruta = $totalVentas - $costoVentas;

        $gastosQuery = Gasto::query()
            ->where('estado', 'Registrado')
            ->whereDate('fecha', '>=', $fechaDesde)
            ->whereDate('fecha', '<=', $fechaHasta);

        $totalGastos = (clone $gastosQuery)->sum('monto');
        $cantidadGastos = (clone $gastosQuery)->count();

        $utilidadNetaEstimada = $utilidadBruta - $totalGastos;

        $comprasQuery = Compra::query()
            ->where('estado', '!=', 'Anulada')
            ->whereDate('fecha', '>=', $fechaDesde)
            ->whereDate('fecha', '<=', $fechaHasta);

        $totalCompras = (clone $comprasQuery)->sum('total');
        $cantidadCompras = (clone $comprasQuery)->count();

        $cuentasPorCobrar = Venta::query()
            ->where('estado', '!=', 'Anulada')
            ->where('saldo_pendiente', '>', 0)
            ->sum('saldo_pendiente');

        $cuentasPorPagar = Compra::query()
            ->where('estado', '!=', 'Anulada')
            ->where('saldo_pendiente', '>', 0)
            ->sum('saldo_pendiente');

        $ventasPorMetodo = Venta::query()
            ->where('estado', '!=', 'Anulada')
            ->whereDate('fecha', '>=', $fechaDesde)
            ->whereDate('fecha', '<=', $fechaHasta)
            ->select(
                'metodo_pago',
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('metodo_pago')
            ->orderByDesc('total')
            ->get();

        $gastosPorCategoria = Gasto::query()
            ->where('estado', 'Registrado')
            ->whereDate('fecha', '>=', $fechaDesde)
            ->whereDate('fecha', '<=', $fechaHasta)
            ->select(
                'categoria',
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(monto) as total')
            )
            ->groupBy('categoria')
            ->orderByDesc('total')
            ->get();

        $ultimasVentas = Venta::with('cliente')
            ->where('estado', '!=', 'Anulada')
            ->whereDate('fecha', '>=', $fechaDesde)
            ->whereDate('fecha', '<=', $fechaHasta)
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        $ultimosGastos = Gasto::query()
            ->where('estado', 'Registrado')
            ->whereDate('fecha', '>=', $fechaDesde)
            ->whereDate('fecha', '<=', $fechaHasta)
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('reportes.financiero', [
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,

            'totalVentas' => $totalVentas,
            'totalDescuentosVentas' => $totalDescuentosVentas,
            'cantidadVentas' => $cantidadVentas,
            'costoVentas' => $costoVentas,
            'utilidadBruta' => $utilidadBruta,

            'totalGastos' => $totalGastos,
            'cantidadGastos' => $cantidadGastos,
            'utilidadNetaEstimada' => $utilidadNetaEstimada,

            'totalCompras' => $totalCompras,
            'cantidadCompras' => $cantidadCompras,

            'cuentasPorCobrar' => $cuentasPorCobrar,
            'cuentasPorPagar' => $cuentasPorPagar,

            'ventasPorMetodo' => $ventasPorMetodo,
            'gastosPorCategoria' => $gastosPorCategoria,
            'ultimasVentas' => $ultimasVentas,
            'ultimosGastos' => $ultimosGastos,
        ]);
    }

    public function exportarExcel(Request $request)
    {
        $fechaDesde = $request->fecha_desde ?: now()->startOfMonth()->format('Y-m-d');
        $fechaHasta = $request->fecha_hasta ?: now()->format('Y-m-d');

        $ventasQuery = Venta::query()
            ->where('estado', '!=', 'Anulada')
            ->whereDate('fecha', '>=', $fechaDesde)
            ->whereDate('fecha', '<=', $fechaHasta);

        $totalVentas = (clone $ventasQuery)->sum('total');
        $totalDescuentosVentas = (clone $ventasQuery)->sum('descuento');
        $cantidadVentas = (clone $ventasQuery)->count();

        $costoVentas = VentaDetalle::join('ventas', 'venta_detalles.venta_id', '=', 'ventas.id')
            ->where('ventas.estado', '!=', 'Anulada')
            ->whereDate('ventas.fecha', '>=', $fechaDesde)
            ->whereDate('ventas.fecha', '<=', $fechaHasta)
            ->select(DB::raw('SUM(venta_detalles.costo_unitario * venta_detalles.cantidad) as costo'))
            ->value('costo') ?? 0;

        $utilidadBruta = $totalVentas - $costoVentas;

        $gastosQuery = Gasto::query()
            ->where('estado', 'Registrado')
            ->whereDate('fecha', '>=', $fechaDesde)
            ->whereDate('fecha', '<=', $fechaHasta);

        $totalGastos = (clone $gastosQuery)->sum('monto');
        $cantidadGastos = (clone $gastosQuery)->count();

        $utilidadNetaEstimada = $utilidadBruta - $totalGastos;

        $comprasQuery = Compra::query()
            ->where('estado', '!=', 'Anulada')
            ->whereDate('fecha', '>=', $fechaDesde)
            ->whereDate('fecha', '<=', $fechaHasta);

        $totalCompras = (clone $comprasQuery)->sum('total');
        $cantidadCompras = (clone $comprasQuery)->count();

        $cuentasPorCobrar = Venta::query()
            ->where('estado', '!=', 'Anulada')
            ->where('saldo_pendiente', '>', 0)
            ->sum('saldo_pendiente');

        $cuentasPorPagar = Compra::query()
            ->where('estado', '!=', 'Anulada')
            ->where('saldo_pendiente', '>', 0)
            ->sum('saldo_pendiente');

        $ventasPorMetodo = Venta::query()
            ->where('estado', '!=', 'Anulada')
            ->whereDate('fecha', '>=', $fechaDesde)
            ->whereDate('fecha', '<=', $fechaHasta)
            ->select(
                'metodo_pago',
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(total) as total')
            )
            ->groupBy('metodo_pago')
            ->orderByDesc('total')
            ->get();

        $gastosPorCategoria = Gasto::query()
            ->where('estado', 'Registrado')
            ->whereDate('fecha', '>=', $fechaDesde)
            ->whereDate('fecha', '<=', $fechaHasta)
            ->select(
                'categoria',
                DB::raw('COUNT(*) as cantidad'),
                DB::raw('SUM(monto) as total')
            )
            ->groupBy('categoria')
            ->orderByDesc('total')
            ->get();

        $nombreArchivo = 'reporte_financiero_' . $fechaDesde . '_al_' . $fechaHasta . '.xls';

        return response()
            ->view('reportes.excel.financiero', [
                'fechaDesde' => $fechaDesde,
                'fechaHasta' => $fechaHasta,

                'totalVentas' => $totalVentas,
                'totalDescuentosVentas' => $totalDescuentosVentas,
                'cantidadVentas' => $cantidadVentas,
                'costoVentas' => $costoVentas,
                'utilidadBruta' => $utilidadBruta,

                'totalGastos' => $totalGastos,
                'cantidadGastos' => $cantidadGastos,
                'utilidadNetaEstimada' => $utilidadNetaEstimada,

                'totalCompras' => $totalCompras,
                'cantidadCompras' => $cantidadCompras,

                'cuentasPorCobrar' => $cuentasPorCobrar,
                'cuentasPorPagar' => $cuentasPorPagar,

                'ventasPorMetodo' => $ventasPorMetodo,
                'gastosPorCategoria' => $gastosPorCategoria,
            ])
            ->header('Content-Type', 'application/vnd.ms-excel; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"');
    }
}
