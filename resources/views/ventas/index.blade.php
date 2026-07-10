@extends('adminlte::page')

@section('title', 'Ventas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Ventas / Punto de Venta</h1>

        <a href="{{ route('ventas.historial') }}" class="btn btn-primary">
            <i class="fas fa-receipt"></i> Historial de ventas
        </a>
    </div>
@stop

@section('content')
    @livewire('ventas.venta-index')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop