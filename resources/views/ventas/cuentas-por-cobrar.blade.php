@extends('adminlte::page')

@section('title', 'Cuentas por cobrar')

@section('content_header')
    <h1>Cuentas por cobrar</h1>
@stop

@section('content')
    @livewire('ventas.cuentas-por-cobrar')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts

    <script>
        window.addEventListener('open-abono-modal', event => {
            $('#abonoModal').modal('show');
        });

        window.addEventListener('close-abono-modal', event => {
            $('#abonoModal').modal('hide');
        });
    </script>
@stop