@extends('adminlte::page')

@section('title', 'Apertura de caja')

@section('content_header')
    <h1>Apertura de caja</h1>
@stop

@section('content')
    @livewire('aperturas-caja.apertura-caja-index')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop