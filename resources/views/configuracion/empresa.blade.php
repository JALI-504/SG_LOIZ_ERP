@extends('adminlte::page')

@section('title', 'Configuración del negocio')

@section('content_header')
    <h1>Configuración del negocio</h1>
@stop

@section('content')
    @livewire('configuracion.configuracion-empresa-index')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop