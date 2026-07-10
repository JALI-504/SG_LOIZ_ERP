@extends('adminlte::page')

@section('title', 'Insumos del producto')

@section('content_header')
    <h1>Insumos del producto</h1>
@stop

@section('content')
    @livewire('productos.producto-insumos', ['productoId' => $producto->id])
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop