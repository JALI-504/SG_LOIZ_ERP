@extends('adminlte::page')

@section('title', 'Categorías de activos')

@section('content_header')
    <h1>Categorías de activos</h1>
@stop

@section('content')
    @livewire('activos-fijos.categoria-activo-index')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop