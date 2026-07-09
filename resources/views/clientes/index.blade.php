@extends('adminlte::page')

@section('title', 'Clientes')

@section('content_header')
    <h1>Clientes</h1>
@stop

@section('content')
    @livewire('clientes.cliente-index')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts

    <script>
        function formatearTelefono(valor) {
            let numeros = valor.replace(/\D/g, '').substring(0, 8);

            if (numeros.length > 4) {
                return numeros.substring(0, 4) + '-' + numeros.substring(4, 8);
            }

            return numeros;
        }

        function formatearDni(valor) {
            let numeros = valor.replace(/\D/g, '').substring(0, 13);

            if (numeros.length > 8) {
                return numeros.substring(0, 4) + '-' +
                       numeros.substring(4, 8) + '-' +
                       numeros.substring(8, 13);
            }

            if (numeros.length > 4) {
                return numeros.substring(0, 4) + '-' +
                       numeros.substring(4, 8);
            }

            return numeros;
        }

        window.addEventListener('open-cliente-modal', event => {
            $('#clienteModal').modal('show');
        });

        window.addEventListener('close-cliente-modal', event => {
            $('#clienteModal').modal('hide');
        });
    </script>
@stop