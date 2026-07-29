@extends('adminlte::page')

@section('title', 'Órdenes de trabajo')

@section('content_header')
    <h1>Órdenes de trabajo</h1>
@stop

@section('content')
    @livewire('ordenes-trabajo.orden-trabajo-index')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop