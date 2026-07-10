@extends('adminlte::page')

@section('title', 'Tipos de catálogo')

@section('content_header')
    <h1>Tipos de catálogo</h1>
@stop

@section('content')
    @livewire('catalogos.tipo-catalogo-index')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts

    <script>
        window.addEventListener('open-tipo-catalogo-modal', event => {
            $('#tipoCatalogoModal').modal('show');
        });

        window.addEventListener('close-tipo-catalogo-modal', event => {
            $('#tipoCatalogoModal').modal('hide');
        });
    </script>
@stop