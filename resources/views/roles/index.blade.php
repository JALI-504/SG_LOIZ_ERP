@extends('adminlte::page')

@section('title', 'Roles y permisos')

@section('content_header')
    <h1>Roles y permisos</h1>
@stop

@section('content')
    @livewire('roles.rol-index')
@stop

@section('css')
    @livewireStyles
@stop

@section('js')
    @livewireScripts
@stop