@extends('adminlte::page')

@section('title', 'Proveedores')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Proveedores</h1>

        <a href="{{ route('proveedores.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Nuevo proveedor
        </a>
    </div>
@stop

@section('content')
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    {{-- Resumen --}}
    <div class="row">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ number_format($totalProveedores, 0) }}</h4>
                    <p>Proveedores encontrados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-truck"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>{{ number_format($totalActivos, 0) }}</h4>
                    <p>Proveedores activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h4>{{ number_format($totalInactivos, 0) }}</h4>
                    <p>Proveedores inactivos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-ban"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros</h3>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('proveedores.index') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label>Buscar</label>
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Código, nombre, RTN, teléfono, correo..."
                               value="{{ request('search') }}">
                    </div>

                    <div class="col-md-3">
                        <label>Tipo de proveedor</label>
                        <select name="tipo_proveedor" class="form-control">
                            <option value="todos">Todos</option>

                            @foreach ($tiposProveedor as $tipo)
                                <option value="{{ $tipo }}" {{ request('tipo_proveedor') === $tipo ? 'selected' : '' }}>
                                    {{ $tipo }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Estado</label>
                        <select name="estado" class="form-control">
                            <option value="todos">Todos</option>
                            <option value="activo" {{ request('estado') === 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="inactivo" {{ request('estado') === 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-1">
                            Filtrar
                        </button>

                        <a href="{{ route('proveedores.index') }}" class="btn btn-secondary">
                            Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de proveedores</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Código</th>
                            <th>Proveedor</th>
                            <th>Tipo</th>
                            <th>RTN / DNI</th>
                            <th>Contacto</th>
                            <th>Correo</th>
                            <th>Estado</th>
                            <th width="170">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($proveedores as $proveedor)
                            <tr class="{{ $proveedor->activo ? '' : 'table-secondary' }}">
                                <td>
                                    <strong>{{ $proveedor->codigo }}</strong>
                                </td>

                                <td>
                                    <strong>{{ $proveedor->nombre_comercial }}</strong>

                                    @if ($proveedor->nombre_legal)
                                        <br>
                                        <small>{{ $proveedor->nombre_legal }}</small>
                                    @endif

                                    @if ($proveedor->persona_contacto)
                                        <br>
                                        <small>Contacto: {{ $proveedor->persona_contacto }}</small>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge badge-info">
                                        {{ $proveedor->tipo_proveedor }}
                                    </span>
                                </td>

                                <td>
                                    @if ($proveedor->rtn)
                                        RTN: {{ $proveedor->rtn }} <br>
                                    @endif

                                    @if ($proveedor->dni)
                                        DNI: {{ $proveedor->dni }}
                                    @endif

                                    @if (!$proveedor->rtn && !$proveedor->dni)
                                        <span class="text-muted">No registrado</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($proveedor->telefono)
                                        Tel: {{ $proveedor->telefono }} <br>
                                    @endif

                                    @if ($proveedor->whatsapp)
                                        WhatsApp: {{ $proveedor->whatsapp }} <br>
                                    @endif

                                    @if ($proveedor->telefono_contacto)
                                        Contacto: {{ $proveedor->telefono_contacto }}
                                    @endif

                                    @if (!$proveedor->telefono && !$proveedor->whatsapp && !$proveedor->telefono_contacto)
                                        <span class="text-muted">No registrado</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $proveedor->correo ?? 'Sin correo' }}
                                </td>

                                <td>
                                    @if ($proveedor->activo)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-secondary">Inactivo</span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('proveedores.edit', $proveedor->id) }}"
                                       class="btn btn-primary btn-xs">
                                        Editar
                                    </a>

                                    <form action="{{ route('proveedores.estado', $proveedor->id) }}"
                                          method="POST"
                                          class="d-inline">
                                        @csrf
                                        @method('PATCH')

                                        <button type="submit"
                                                class="btn {{ $proveedor->activo ? 'btn-danger' : 'btn-warning' }} btn-xs"
                                                onclick="return confirm('¿Seguro que desea cambiar el estado de este proveedor?')">
                                            {{ $proveedor->activo ? 'Desactivar' : 'Reactivar' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    No hay proveedores registrados con los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $proveedores->links() }}
        </div>
    </div>
@stop