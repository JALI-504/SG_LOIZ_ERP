@extends('adminlte::page')

@section('title', 'Bitácora del sistema')

@section('content_header')
    <h1>Bitácora del sistema</h1>
@stop

@section('content')
    @livewire('bitacora.bitacora-index')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop