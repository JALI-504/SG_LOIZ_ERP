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

Route::get('/clientes', function () {
    return view('clientes.index');
})->name('clientes.index');

Route::get('/servicios', function () {
    return view('servicios.index');
})->name('servicios.index');

Route::get('/insumos', function () {
    return view('insumos.index');
})->name('insumos.index');

Route::get('/servicios/{servicio}/insumos', function (\App\Models\Servicio $servicio) {
    return view('servicios.insumos', compact('servicio'));
})->name('servicios.insumos');

Route::get('/catalogos', function () {
    return view('catalogos.index');
})->name('catalogos.index');

Route::get('/catalogos/tipos', function () {
    return view('catalogos.tipos');
})->name('catalogos.tipos');

Route::get('/productos', function () {
    return view('productos.index');
})->name('productos.index');