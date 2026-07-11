@extends('adminlte::page')

@section('title', 'Gastos')

@section('content_header')
    <h1>Gastos del negocio</h1>
@stop

@section('content')
    @livewire('gastos.gasto-index')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop