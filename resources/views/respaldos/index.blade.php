@extends('adminlte::page')

@section('title', 'Respaldos')

@section('content_header')
    <h1>Respaldos de base de datos</h1>
@stop

@section('content')
    @livewire('respaldos.respaldo-index')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop