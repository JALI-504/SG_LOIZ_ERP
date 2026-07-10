@extends('adminlte::page')

@section('title', 'Insumos del servicio')

@section('content_header')
    <h1>Insumos del servicio</h1>
@stop

@section('content')
    @livewire('servicios.servicio-insumos', ['servicioId' => $servicio->id])
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop