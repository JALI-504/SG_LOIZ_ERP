@extends('adminlte::page')

@section('title', 'Productos')

@section('content_header')
    <h1>Productos físicos / Productos láser</h1>
@stop

@section('content')
    @livewire('productos.producto-index')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts

    <script>
        window.addEventListener('open-producto-modal', event => {
            $('#productoModal').modal('show');
        });

        window.addEventListener('close-producto-modal', event => {
            $('#productoModal').modal('hide');
        });
    </script>
@stop