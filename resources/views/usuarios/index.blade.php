@extends('adminlte::page')

@section('title', 'Usuarios')

@section('css')
    @livewireStyles
@stop

@section('content_header')
    <h1>Usuarios y roles</h1>
@stop

@section('content')
    @livewire('usuarios.usuario-index')
@stop

@section('js')
    @livewireScripts
@stop