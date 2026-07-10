@extends('adminlte::page')

@section('title', 'Movimientos del insumo')

@section('content_header')
    <h1>Movimientos del insumo</h1>
@stop

@section('content')
    @livewire('insumos.insumo-movimientos', ['insumoId' => $insumo->id])
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop