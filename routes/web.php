<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\CuentaPorPagarController;
use App\Http\Controllers\ReporteFinancieroController;
use App\Http\Controllers\ReporteInventarioController;

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

// Dashboard

Route::get('/', function () {
    return redirect()->route('dashboard.index');
});

Route::get('/dashboard', function () {
    return view('dashboard.index');
})->name('dashboard.index');


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

Route::get('/ventas/cuentas-por-cobrar', function () {
    return view('ventas.cuentas-por-cobrar');
})->name('ventas.cuentas-por-cobrar');

Route::get('/ventas/pagos/{pago}/recibo', function (\App\Models\PagoVenta $pago) {
    $pago->load(['venta.cliente']);

    $configuracion = \App\Models\ConfiguracionEmpresa::actual();

    return view('ventas.recibo-abono', compact('pago', 'configuracion'));
})->name('ventas.pagos.recibo');

// Recibo Venta:

Route::get('/ventas/{venta}/recibo', function (\App\Models\Venta $venta) {
    $venta->load(['cliente', 'detalles', 'pagos']);

    $configuracion = \App\Models\ConfiguracionEmpresa::actual();

    return view('ventas.recibo', compact('venta', 'configuracion'));
})->name('ventas.recibo');


// Configuracion

Route::get('/configuracion/empresa', function () {
    return view('configuracion.empresa');
})->name('configuracion.empresa');

// Reportes
Route::get('/reportes/ventas', function () {
    return view('reportes.ventas');
})->name('reportes.ventas');

Route::get('/reportes/financiero', [ReporteFinancieroController::class, 'index'])
    ->name('reportes.financiero');

Route::get('/reportes/inventario', [ReporteInventarioController::class, 'index'])
    ->name('reportes.inventario');

// Gastos
Route::get('/gastos', function () {
    return view('gastos.index');
})->name('gastos.index');

Route::get('/gastos/crear', [GastoController::class, 'create'])
    ->name('gastos.create');

Route::post('/gastos', [GastoController::class, 'store'])
    ->name('gastos.store');

Route::get('/gastos/{gasto}/editar', [GastoController::class, 'edit'])
    ->name('gastos.edit');

Route::put('/gastos/{gasto}', [GastoController::class, 'update'])
    ->name('gastos.update');

// Proveedores
Route::get('/proveedores', [ProveedorController::class, 'index'])
    ->name('proveedores.index');

Route::get('/proveedores/crear', [ProveedorController::class, 'create'])
    ->name('proveedores.create');

Route::post('/proveedores', [ProveedorController::class, 'store'])
    ->name('proveedores.store');

Route::get('/proveedores/{proveedor}/editar', [ProveedorController::class, 'edit'])
    ->name('proveedores.edit');

Route::put('/proveedores/{proveedor}', [ProveedorController::class, 'update'])
    ->name('proveedores.update');

Route::patch('/proveedores/{proveedor}/estado', [ProveedorController::class, 'cambiarEstado'])
    ->name('proveedores.estado');

// Cuentas por pagar
Route::get('/compras/cuentas-por-pagar', [CuentaPorPagarController::class, 'index'])
    ->name('compras.cuentas-por-pagar');

Route::post('/compras/{compra}/registrar-pago', [CuentaPorPagarController::class, 'pagar'])
    ->name('compras.registrar-pago');

// Proveedores / COmpras 
Route::get('/compras', [CompraController::class, 'index'])
    ->name('compras.index');

Route::get('/compras/crear', [CompraController::class, 'create'])
    ->name('compras.create');

Route::post('/compras', [CompraController::class, 'store'])
    ->name('compras.store');

    // Recibo de pago

Route::get('/compras/pagos/{pago}/recibo', function (\App\Models\PagoCompra $pago) {
    $pago->load(['compra.proveedor']);

    $configuracion = \App\Models\ConfiguracionEmpresa::actual();

    return view('compras.recibo-pago', compact('pago', 'configuracion'));
})->name('compras.pagos.recibo');
// compras  proveedores

Route::get('/compras/{compra}', [CompraController::class, 'show'])
    ->name('compras.show');

Route::patch('/compras/{compra}/anular', [CompraController::class, 'anular'])
    ->name('compras.anular');