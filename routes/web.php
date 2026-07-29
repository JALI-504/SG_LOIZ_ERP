<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GastoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\CuentaPorPagarController;
use App\Http\Controllers\ReporteFinancieroController;
use App\Http\Controllers\ReporteInventarioController;
use App\Http\Controllers\ReporteCuentasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('dashboard.index');
});

/*
|--------------------------------------------------------------------------
| Rutas protegidas
|--------------------------------------------------------------------------
| Todas estas rutas requieren usuario autenticado.
| Luego cada grupo aplica permisos específicos.
*/

Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', function (Request $request) {
    $credenciales = $request->validate([
        'email' => 'required|email',
        'password' => 'required',
    ]);

    $recordar = $request->has('remember');

    if (Auth::attempt($credenciales, $recordar)) {
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard.index'));
    }

    return back()
        ->withErrors([
            'email' => 'Las credenciales ingresadas no son correctas.',
        ])
        ->onlyInput('email');
})->name('login.post');

Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->name('logout');

Route::middleware(['auth'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::get('/dashboard', function () {
        return view('dashboard.index');
    })->name('dashboard.index')
        ->middleware('permission:ver dashboard');


    /*
    |--------------------------------------------------------------------------
    | Clientes
    |--------------------------------------------------------------------------
    */

    Route::get('/clientes', function () {
        return view('clientes.index');
    })->name('clientes.index')
        ->middleware('permission:ver clientes');


    /*
    |--------------------------------------------------------------------------
    | Servicios
    |--------------------------------------------------------------------------
    */

    Route::get('/servicios', function () {
        return view('servicios.index');
    })->name('servicios.index')
        ->middleware('permission:ver servicios');

    Route::get('/servicios/{servicio}/insumos', function (\App\Models\Servicio $servicio) {
        return view('servicios.insumos', compact('servicio'));
    })->name('servicios.insumos')
        ->middleware('permission:editar servicios');


    /*
    |--------------------------------------------------------------------------
    | Catálogos
    |--------------------------------------------------------------------------
    | Por ahora lo dejamos solo para configuración/administración.
    */

    Route::get('/catalogos', function () {
        return view('catalogos.index');
    })->name('catalogos.index')
        ->middleware('permission:ver configuracion|editar configuracion');

    Route::get('/catalogos/tipos', function () {
        return view('catalogos.tipos');
    })->name('catalogos.tipos')
        ->middleware('permission:ver configuracion|editar configuracion');


    /*
    |--------------------------------------------------------------------------
    | Productos
    |--------------------------------------------------------------------------
    */

    Route::get('/productos', function () {
        return view('productos.index');
    })->name('productos.index')
        ->middleware('permission:ver productos');

    Route::get('/productos/{producto}/insumos', function (\App\Models\Producto $producto) {
        return view('productos.insumos', compact('producto'));
    })->name('productos.insumos')
        ->middleware('permission:editar productos');

    Route::get('/productos/{producto}/movimientos', function (\App\Models\Producto $producto) {
        return view('productos.movimientos', compact('producto'));
    })->name('productos.movimientos')
        ->middleware('permission:ver inventario');


    /*
    |--------------------------------------------------------------------------
    | Insumos
    |--------------------------------------------------------------------------
    */

    Route::get('/insumos', function () {
        return view('insumos.index');
    })->name('insumos.index')
        ->middleware('permission:ver insumos');

    Route::get('/insumos/{insumo}/movimientos', function (\App\Models\Insumo $insumo) {
        return view('insumos.movimientos', compact('insumo'));
    })->name('insumos.movimientos')
        ->middleware('permission:ver inventario');


    /*
    |--------------------------------------------------------------------------
    | Ventas
    |--------------------------------------------------------------------------
    */

    Route::get('/ventas', function () {
        return view('ventas.index');
    })->name('ventas.index')
        ->middleware('permission:crear ventas');

    Route::get('/ventas/historial', function () {
        return view('ventas.historial');
    })->name('ventas.historial')
        ->middleware('permission:ver historial ventas');

    Route::get('/ventas/cuentas-por-cobrar', function () {
        return view('ventas.cuentas-por-cobrar');
    })->name('ventas.cuentas-por-cobrar')
        ->middleware('permission:ver cuentas por cobrar');

    Route::get('/ventas/pagos/{pago}/recibo', function (\App\Models\PagoVenta $pago) {
        $pago->load(['venta.cliente', 'venta.pagos']);

        $configuracion = \App\Models\ConfiguracionEmpresa::actual();

        return view('ventas.recibo-abono', compact('pago', 'configuracion'));
    })->name('ventas.pagos.recibo')
        ->middleware('permission:imprimir recibos ventas');

    Route::get('/ventas/{venta}/recibo', function (\App\Models\Venta $venta) {
        $venta->load(['cliente', 'detalles', 'pagos']);

        $configuracion = \App\Models\ConfiguracionEmpresa::actual();

        return view('ventas.recibo', compact('venta', 'configuracion'));
    })->name('ventas.recibo')
        ->middleware('permission:imprimir recibos ventas');


    /*
    |--------------------------------------------------------------------------
    | Configuración
    |--------------------------------------------------------------------------
    */

    Route::get('/configuracion/empresa', function () {
        return view('configuracion.empresa');
    })->name('configuracion.empresa')
        ->middleware('permission:ver configuracion|editar configuracion');


    /*
    |--------------------------------------------------------------------------
    | Reportes
    |--------------------------------------------------------------------------
    */

    Route::get('/reportes/ventas', function () {
        return view('reportes.ventas');
    })->name('reportes.ventas')
        ->middleware('permission:ver reporte ventas');

    Route::get('/reportes/financiero', [ReporteFinancieroController::class, 'index'])
        ->name('reportes.financiero')
        ->middleware('permission:ver reporte financiero');

    Route::get('/reportes/financiero/exportar-excel', [ReporteFinancieroController::class, 'exportarExcel'])
        ->name('reportes.financiero.excel')
        ->middleware('permission:ver reporte financiero');

    Route::get('/reportes/inventario', [ReporteInventarioController::class, 'index'])
        ->name('reportes.inventario')
        ->middleware('permission:ver reporte inventario');

    Route::get('/reportes/cuentas', [ReporteCuentasController::class, 'index'])
        ->name('reportes.cuentas')
        ->middleware('permission:ver reporte cuentas');


    /*
    |--------------------------------------------------------------------------
    | Gastos
    |--------------------------------------------------------------------------
    */

    Route::get('/gastos', function () {
        return view('gastos.index');
    })->name('gastos.index')
        ->middleware('permission:ver gastos');

    Route::get('/gastos/crear', [GastoController::class, 'create'])
        ->name('gastos.create')
        ->middleware('permission:crear gastos');

    Route::post('/gastos', [GastoController::class, 'store'])
        ->name('gastos.store')
        ->middleware('permission:crear gastos');

    Route::get('/gastos/{gasto}/editar', [GastoController::class, 'edit'])
        ->name('gastos.edit')
        ->middleware('permission:editar gastos');

    Route::put('/gastos/{gasto}', [GastoController::class, 'update'])
        ->name('gastos.update')
        ->middleware('permission:editar gastos');


    /*
    |--------------------------------------------------------------------------
    | Proveedores
    |--------------------------------------------------------------------------
    */

    Route::get('/proveedores', [ProveedorController::class, 'index'])
        ->name('proveedores.index')
        ->middleware('permission:ver proveedores');

    Route::get('/proveedores/crear', [ProveedorController::class, 'create'])
        ->name('proveedores.create')
        ->middleware('permission:crear proveedores');

    Route::post('/proveedores', [ProveedorController::class, 'store'])
        ->name('proveedores.store')
        ->middleware('permission:crear proveedores');

    Route::get('/proveedores/{proveedor}/editar', [ProveedorController::class, 'edit'])
        ->name('proveedores.edit')
        ->middleware('permission:editar proveedores');

    Route::put('/proveedores/{proveedor}', [ProveedorController::class, 'update'])
        ->name('proveedores.update')
        ->middleware('permission:editar proveedores');

    Route::patch('/proveedores/{proveedor}/estado', [ProveedorController::class, 'cambiarEstado'])
        ->name('proveedores.estado')
        ->middleware('permission:editar proveedores');


    /*
    |--------------------------------------------------------------------------
    | Cuentas por pagar
    |--------------------------------------------------------------------------
    */

    Route::get('/compras/cuentas-por-pagar', [CuentaPorPagarController::class, 'index'])
        ->name('compras.cuentas-por-pagar')
        ->middleware('permission:ver cuentas por pagar');

    Route::post('/compras/{compra}/registrar-pago', [CuentaPorPagarController::class, 'pagar'])
        ->name('compras.registrar-pago')
        ->middleware('permission:registrar pagos proveedores');

    Route::patch('/compras/pagos/{pago}/anular', [CuentaPorPagarController::class, 'anularPago'])
        ->name('compras.pagos.anular')
        ->middleware('permission:anular pagos proveedores');

    Route::get('/compras/pagos/{pago}/recibo', function (\App\Models\PagoCompra $pago) {
        $pago->load(['compra.proveedor', 'compra.pagos']);

        $configuracion = \App\Models\ConfiguracionEmpresa::actual();

        return view('compras.recibo-pago', compact('pago', 'configuracion'));
    })->name('compras.pagos.recibo')
        ->middleware('permission:ver cuentas por pagar');


    /*
    |--------------------------------------------------------------------------
    | Compras
    |--------------------------------------------------------------------------
    */

    Route::get('/compras', [CompraController::class, 'index'])
        ->name('compras.index')
        ->middleware('permission:ver compras');

    Route::get('/compras/crear', [CompraController::class, 'create'])
        ->name('compras.create')
        ->middleware('permission:crear compras');

    Route::post('/compras', [CompraController::class, 'store'])
        ->name('compras.store')
        ->middleware('permission:crear compras');

    Route::get('/compras/{compra}', [CompraController::class, 'show'])
        ->name('compras.show')
        ->middleware('permission:ver compras');

    Route::patch('/compras/{compra}/anular', [CompraController::class, 'anular'])
        ->name('compras.anular')
        ->middleware('permission:anular compras');
});