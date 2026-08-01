@extends('adminlte::page')

@section('title', 'Cierre de caja')

@section('content_header')
    <h1>Cierre de caja</h1>
@stop

@section('content')
    @livewire('cierres-caja.cierre-caja-index')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop