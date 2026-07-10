@extends('adminlte::page')

@section('title', 'Reporte de ventas')

@section('content_header')
    <h1>Reporte de ventas</h1>
@stop

@section('content')
    @livewire('reportes.reporte-ventas')
@stop

@section('css')
    @livewireStyles

    <style>
        @media print {
            .main-header,
            .main-sidebar,
            .main-footer,
            .control-sidebar,
            .content-header,
            .no-print {
                display: none !important;
            }

            .content-wrapper {
                margin-left: 0 !important;
                padding: 0 !important;
            }

            .content {
                padding: 0 !important;
            }

            .card {
                page-break-inside: avoid;
                box-shadow: none !important;
                border: 1px solid #000 !important;
            }

            .small-box {
                box-shadow: none !important;
                border: 1px solid #000 !important;
            }

            body {
                font-size: 12px;
            }

            table {
                font-size: 11px;
            }
        }
    </style>
@stop

@section('js')
    @livewireScripts
@stop