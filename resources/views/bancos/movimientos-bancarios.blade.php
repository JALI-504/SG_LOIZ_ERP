@extends('adminlte::page')

@section('title', 'Movimientos bancarios')

@section('content_header')
    <h1>Movimientos bancarios</h1>
@stop

@section('content')
    @livewire('bancos.movimiento-bancario-index')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop