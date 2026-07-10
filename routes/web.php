<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// Clientes

Route::get('/clientes', function () {
    return view('clientes.index');
})->name('clientes.index');

// Servicios

Route::get('/servicios', function () {
    return view('servicios.index');
})->name('servicios.index');

Route::get('/servicios/{servicio}/insumos', function (\App\Models\Servicio $servicio) {
    return view('servicios.insumos', compact('servicio'));
})->name('servicios.insumos');

// Rutas Catalogos

Route::get('/catalogos', function () {
    return view('catalogos.index');
})->name('catalogos.index');

Route::get('/catalogos/tipos', function () {
    return view('catalogos.tipos');
})->name('catalogos.tipos');

// Rutas de productos

Route::get('/productos', function () {
    return view('productos.index');
})->name('productos.index');

Route::get('/productos/{producto}/insumos', function (\App\Models\Producto $producto) {
    return view('productos.insumos', compact('producto'));
})->name('productos.insumos');

Route::get('/productos/{producto}/movimientos', function (\App\Models\Producto $producto) {
    return view('productos.movimientos', compact('producto'));
})->name('productos.movimientos');

// Insumos

Route::get('/insumos', function () {
    return view('insumos.index');
})->name('insumos.index');

Route::get('/insumos/{insumo}/movimientos', function (\App\Models\Insumo $insumo) {
    return view('insumos.movimientos', compact('insumo'));
})->name('insumos.movimientos');

// Ventas
Route::get('/ventas', function () {
    return view('ventas.index');
})->name('ventas.index');

Route::get('/ventas/historial', function () {
    return view('ventas.historial');
})->name('ventas.historial');

// Recibo Venta:

Route::get('/ventas/{venta}/recibo', function (\App\Models\Venta $venta) {
    $venta->load(['cliente', 'detalles']);

    $configuracion = \App\Models\ConfiguracionEmpresa::actual();

    return view('ventas.recibo', compact('venta', 'configuracion'));
})->name('ventas.recibo');

// Configuracion

Route::get('/configuracion/empresa', function () {
    return view('configuracion.empresa');
})->name('configuracion.empresa');