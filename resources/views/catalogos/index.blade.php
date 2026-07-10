@extends('adminlte::page')

@section('title', 'Catálogos')

@section('content_header')
    <h1>Catálogos del sistema</h1>
@stop

@section('content')
    @livewire('catalogos.catalogo-index')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts

    <script>
        window.addEventListener('open-catalogo-modal', event => {
            $('#catalogoModal').modal('show');
        });

        window.addEventListener('close-catalogo-modal', event => {
            $('#catalogoModal').modal('hide');
        });
    </script>
@stop