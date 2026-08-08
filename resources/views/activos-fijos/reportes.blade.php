@extends('adminlte::page')

@section('title', 'Reportes de activos fijos')

@section('content_header')
    <h1>Reportes de activos fijos</h1>
@stop

@section('content')
    @livewire('activos-fijos.reporte-activo-fijo-index')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop