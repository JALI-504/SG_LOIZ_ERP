@extends('adminlte::page')

@section('title', 'Producción')

@section('content_header')
    <h1>Producción</h1>
@stop

@section('content')
    @livewire('produccion.produccion-index')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop