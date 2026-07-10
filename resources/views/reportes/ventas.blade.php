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
@stop

@section('js')
    @livewireScripts
@stop