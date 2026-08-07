@extends('adminlte::page')

@section('title', 'Conciliaciones bancarias')

@section('content_header')
    <h1>Conciliaciones bancarias</h1>
@stop

@section('content')
    @livewire('bancos.conciliacion-bancaria-index')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop