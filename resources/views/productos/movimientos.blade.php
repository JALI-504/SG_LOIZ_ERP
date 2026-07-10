@extends('adminlte::page')

@section('title', 'Movimientos del producto')

@section('content_header')
    <h1>Movimientos del producto</h1>
@stop

@section('content')
    @livewire('productos.producto-movimientos', ['productoId' => $producto->id])
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop