@extends('adminlte::page')

@section('title', 'Depreciaciones de activos')

@section('content_header')
    <h1>Depreciaciones de activos</h1>
@stop

@section('content')
    @livewire('activos-fijos.depreciacion-activo-index')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop