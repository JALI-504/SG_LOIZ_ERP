@extends('adminlte::page')

@section('title', 'Configuración del negocio')

@section('content_header')
    <h1>Configuración del negocio</h1>
@stop

@section('content')
    @livewireStyles

    @livewire('configuracion.configuracion-empresa-index')

    @livewireScripts
@stop