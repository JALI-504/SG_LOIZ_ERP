<?php

namespace App\Http\Controllers;

use App\Models\Insumo;
use App\Models\Producto;
use App\Models\LoteInsumo;
use App\Models\LoteProducto;
use App\Models\MovimientoInventario;
use App\Models\MovimientoProducto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReporteInventarioController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->can('ver reporte inventario')) {
            abort(403, 'No tiene permiso para ver el reporte de inventario.');
        }
        
        $tipo = $request->tipo ?: 'todos';
        $fechaDesde = $request->fecha_desde;
        $fechaHasta = $request->fecha_hasta;
        $limite = (int) ($request->limite ?: 20);

        if (!in_array($tipo, ['todos', 'insumos', 'productos'])) {
            $tipo = 'todos';
        }

        if (!in_array($limite, [10, 20, 50, 100])) {
            $limite = 20;
        }

        $mostrarInsumos = $tipo === 'todos' || $tipo === 'insumos';
        $mostrarProductos = $tipo === 'todos' || $tipo === 'productos';

        $insumosStockBajo = $mostrarInsumos
            ? Insumo::query()
            ->where('activo', true)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->orderBy('stock_actual')
            ->get()
            : collect();

        $productosStockBajo = $mostrarProductos
            ? Producto::query()
            ->where('activo', true)
            ->where('maneja_inventario', true)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->orderBy('stock_actual')
            ->get()
            : collect();

        $totalInsumos = Insumo::where('activo', true)->count();

        $totalProductos = Producto::where('activo', true)
            ->where('maneja_inventario', true)
            ->count();

        $valorInventarioInsumos = LoteInsumo::query()
            ->where('activo', true)
            ->where('cantidad_disponible', '>', 0)
            ->sum(DB::raw('cantidad_disponible * costo_unitario'));

        $valorInventarioProductos = LoteProducto::query()
            ->where('activo', true)
            ->where('cantidad_disponible', '>', 0)
            ->sum(DB::raw('cantidad_disponible * costo_unitario'));

        $valorInventarioTotal = $valorInventarioInsumos + $valorInventarioProductos;

        $lotesInsumos = $mostrarInsumos
            ? LoteInsumo::with('insumo')
            ->where('activo', true)
            ->where('cantidad_disponible', '>', 0)
            ->orderBy('fecha_entrada')
            ->orderBy('id')
            ->limit($limite)
            ->get()
            : collect();

        $lotesProductos = $mostrarProductos
            ? LoteProducto::with('producto')
            ->where('activo', true)
            ->where('cantidad_disponible', '>', 0)
            ->orderBy('fecha_entrada')
            ->orderBy('id')
            ->limit($limite)
            ->get()
            : collect();

        $movimientosInsumosQuery = MovimientoInventario::with('insumo')
            ->when($fechaDesde, function ($query) use ($fechaDesde) {
                $query->whereDate('created_at', '>=', $fechaDesde);
            })
            ->when($fechaHasta, function ($query) use ($fechaHasta) {
                $query->whereDate('created_at', '<=', $fechaHasta);
            });

        $movimientosProductosQuery = MovimientoProducto::with('producto')
            ->when($fechaDesde, function ($query) use ($fechaDesde) {
                $query->whereDate('created_at', '>=', $fechaDesde);
            })
            ->when($fechaHasta, function ($query) use ($fechaHasta) {
                $query->whereDate('created_at', '<=', $fechaHasta);
            });

        $totalMovimientosInsumos = $mostrarInsumos
            ? (clone $movimientosInsumosQuery)->count()
            : 0;

        $totalMovimientosProductos = $mostrarProductos
            ? (clone $movimientosProductosQuery)->count()
            : 0;

        $valorMovimientosInsumos = $mostrarInsumos
            ? (clone $movimientosInsumosQuery)->sum('total')
            : 0;

        $valorMovimientosProductos = $mostrarProductos
            ? (clone $movimientosProductosQuery)->sum('total')
            : 0;

        $movimientosInsumos = $mostrarInsumos
            ? $movimientosInsumosQuery
            ->orderByDesc('id')
            ->limit($limite)
            ->get()
            : collect();

        $movimientosProductos = $mostrarProductos
            ? $movimientosProductosQuery
            ->orderByDesc('id')
            ->limit($limite)
            ->get()
            : collect();

        return view('reportes.inventario', [
            'tipo' => $tipo,
            'fechaDesde' => $fechaDesde,
            'fechaHasta' => $fechaHasta,
            'limite' => $limite,

            'mostrarInsumos' => $mostrarInsumos,
            'mostrarProductos' => $mostrarProductos,

            'insumosStockBajo' => $insumosStockBajo,
            'productosStockBajo' => $productosStockBajo,

            'totalInsumos' => $totalInsumos,
            'totalProductos' => $totalProductos,

            'totalInsumosStockBajo' => $insumosStockBajo->count(),
            'totalProductosStockBajo' => $productosStockBajo->count(),

            'valorInventarioInsumos' => $valorInventarioInsumos,
            'valorInventarioProductos' => $valorInventarioProductos,
            'valorInventarioTotal' => $valorInventarioTotal,

            'lotesInsumos' => $lotesInsumos,
            'lotesProductos' => $lotesProductos,

            'movimientosInsumos' => $movimientosInsumos,
            'movimientosProductos' => $movimientosProductos,

            'totalMovimientosInsumos' => $totalMovimientosInsumos,
            'totalMovimientosProductos' => $totalMovimientosProductos,
            'valorMovimientosInsumos' => $valorMovimientosInsumos,
            'valorMovimientosProductos' => $valorMovimientosProductos,
        ]);
    }
}
