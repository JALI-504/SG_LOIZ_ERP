@extends('adminlte::page')

@section('title', 'Servicios')

@section('content_header')
    <h1>Servicios</h1>
@stop

@section('content')
    @livewire('servicios.servicio-index')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts

    <script>
        window.addEventListener('open-servicio-modal', event => {
            $('#servicioModal').modal('show');
        });

        window.addEventListener('close-servicio-modal', event => {
            $('#servicioModal').modal('hide');
        });
    </script>
@stop