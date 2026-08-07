@extends('adminlte::page')

@section('title', 'Cuentas bancarias')

@section('content_header')
    <h1>Cuentas bancarias</h1>
@stop

@section('content')
    @livewire('bancos.cuenta-bancaria-index')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop