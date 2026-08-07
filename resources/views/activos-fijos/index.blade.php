@extends('adminlte::page')

@section('title', 'Activos fijos')

@section('content_header')
    <h1>Activos fijos</h1>
@stop

@section('content')
    @livewire('activos-fijos.activo-fijo-index')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop