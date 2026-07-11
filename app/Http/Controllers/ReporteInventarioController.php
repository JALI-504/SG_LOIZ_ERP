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
        $tipo = $request->tipo ?: 'todos';

        $insumosStockBajo = Insumo::query()
            ->where('activo', true)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->orderBy('stock_actual')
            ->get();

        $productosStockBajo = Producto::query()
            ->where('activo', true)
            ->where('maneja_inventario', true)
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->orderBy('stock_actual')
            ->get();

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

        $lotesInsumos = LoteInsumo::with('insumo')
            ->where('activo', true)
            ->where('cantidad_disponible', '>', 0)
            ->orderBy('fecha_entrada')
            ->orderBy('id')
            ->limit(20)
            ->get();

        $lotesProductos = LoteProducto::with('producto')
            ->where('activo', true)
            ->where('cantidad_disponible', '>', 0)
            ->orderBy('fecha_entrada')
            ->orderBy('id')
            ->limit(20)
            ->get();

        $movimientosInsumos = MovimientoInventario::with('insumo')
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        $movimientosProductos = MovimientoProducto::with('producto')
            ->orderByDesc('id')
            ->limit(15)
            ->get();

        return view('reportes.inventario', [
            'tipo' => $tipo,

            'insumosStockBajo' => $insumosStockBajo,
            'productosStockBajo' => $productosStockBajo,

            'totalInsumos' => $totalInsumos,
            'totalProductos' => $totalProductos,

            'valorInventarioInsumos' => $valorInventarioInsumos,
            'valorInventarioProductos' => $valorInventarioProductos,
            'valorInventarioTotal' => $valorInventarioTotal,

            'lotesInsumos' => $lotesInsumos,
            'lotesProductos' => $lotesProductos,

            'movimientosInsumos' => $movimientosInsumos,
            'movimientosProductos' => $movimientosProductos,
        ]);
    }
}
