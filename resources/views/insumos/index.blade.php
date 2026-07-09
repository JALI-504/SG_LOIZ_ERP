@extends('adminlte::page')

@section('title', 'Insumos')

@section('content_header')
    <h1>Insumos e Inventario</h1>
@stop

@section('content')
    @livewire('insumos.insumo-index')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts

    <script>
        window.addEventListener('open-insumo-modal', event => {
            $('#insumoModal').modal('show');
        });

        window.addEventListener('close-insumo-modal', event => {
            $('#insumoModal').modal('hide');
        });

        window.addEventListener('open-movimiento-modal', event => {
            $('#movimientoModal').modal('show');
        });

        window.addEventListener('close-movimiento-modal', event => {
            $('#movimientoModal').modal('hide');
        });
    </script>
@stop