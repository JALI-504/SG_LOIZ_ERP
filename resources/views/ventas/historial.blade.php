@extends('adminlte::page')

@section('title', 'Historial de ventas')

@section('content_header')
    <h1>Historial de ventas</h1>
@stop

@section('content')
    @livewire('ventas.venta-historial')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop