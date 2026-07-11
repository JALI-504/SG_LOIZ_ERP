@extends('adminlte::page')

@section('title', 'Compras')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Compras</h1>

        <a href="{{ route('compras.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Nueva compra
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
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ number_format($totalCompras, 0) }}</h4>
                    <p>Compras encontradas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>L {{ number_format($montoCompras, 2) }}</h4>
                    <p>Monto comprado</p>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>L {{ number_format($saldoPendiente, 2) }}</h4>
                    <p>Saldo pendiente</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h4>L {{ number_format($montoAnulado, 2) }}</h4>
                    <p>Monto anulado</p>
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
            <form method="GET" action="{{ route('compras.index') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label>Buscar</label>
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Número, proveedor, comprobante..."
                               value="{{ request('search') }}">
                    </div>

                    <div class="col-md-2">
                        <label>Desde</label>
                        <input type="date"
                               name="fecha_desde"
                               class="form-control"
                               value="{{ request('fecha_desde') }}">
                    </div>

                    <div class="col-md-2">
                        <label>Hasta</label>
                        <input type="date"
                               name="fecha_hasta"
                               class="form-control"
                               value="{{ request('fecha_hasta') }}">
                    </div>

                    <div class="col-md-2">
                        <label>Tipo pago</label>
                        <select name="tipo_pago" class="form-control">
                            <option value="todos">Todos</option>
                            <option value="Contado" {{ request('tipo_pago') === 'Contado' ? 'selected' : '' }}>
                                Contado
                            </option>
                            <option value="Crédito" {{ request('tipo_pago') === 'Crédito' ? 'selected' : '' }}>
                                Crédito
                            </option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label>Estado</label>
                        <select name="estado" class="form-control">
                            <option value="todos">Todos</option>
                            <option value="Registrada" {{ request('estado') === 'Registrada' ? 'selected' : '' }}>
                                Registrada
                            </option>
                            <option value="Anulada" {{ request('estado') === 'Anulada' ? 'selected' : '' }}>
                                Anulada
                            </option>
                        </select>
                    </div>

                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-1">
                            Filtrar
                        </button>
                    </div>
                </div>

                <div class="mt-2">
                    <a href="{{ route('compras.index') }}" class="btn btn-secondary btn-sm">
                        Limpiar filtros
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de compras</h3>
        </div>

        <div class="card-body">
            <div class="alert alert-info">
                Aquí se muestran las compras registradas. En esta primera versión la compra queda guardada formalmente, pero todavía no crea entradas automáticas al inventario PEPS.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Número</th>
                            <th>Proveedor</th>
                            <th>Comprobante</th>
                            <th>Tipo pago</th>
                            <th>Total</th>
                            <th>Pagado</th>
                            <th>Saldo</th>
                            <th>Estado</th>
                            <th width="170">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($compras as $compra)
                            <tr class="{{ $compra->estado === 'Anulada' ? 'table-secondary' : '' }}">
                                <td>
                                    {{ $compra->fecha }}

                                    @if ($compra->hora)
                                        <br>
                                        <small>{{ $compra->hora }}</small>
                                    @endif
                                </td>

                                <td>
                                    <strong>{{ $compra->numero }}</strong>
                                </td>

                                <td>
                                    @if ($compra->proveedor)
                                        <strong>{{ $compra->proveedor->nombre_comercial }}</strong>

                                        @if ($compra->proveedor->rtn)
                                            <br>
                                            <small>RTN: {{ $compra->proveedor->rtn }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">Sin proveedor</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $compra->tipo_comprobante }}

                                    @if ($compra->numero_comprobante)
                                        <br>
                                        <small>{{ $compra->numero_comprobante }}</small>
                                    @endif
                                </td>

                                <td>
                                    @if ($compra->tipo_pago === 'Contado')
                                        <span class="badge badge-success">Contado</span>
                                    @else
                                        <span class="badge badge-warning">Crédito</span>
                                    @endif

                                    <br>
                                    <small>{{ $compra->metodo_pago }}</small>
                                </td>

                                <td>
                                    <strong>L {{ number_format($compra->total, 2) }}</strong>
                                </td>

                                <td>
                                    L {{ number_format($compra->monto_pagado, 2) }}
                                </td>

                                <td>
                                    @if ($compra->saldo_pendiente > 0)
                                        <strong class="text-danger">
                                            L {{ number_format($compra->saldo_pendiente, 2) }}
                                        </strong>
                                    @else
                                        <strong class="text-success">
                                            L 0.00
                                        </strong>
                                    @endif
                                </td>

                                <td>
                                    @if ($compra->estado === 'Registrada')
                                        <span class="badge badge-success">Registrada</span>
                                    @else
                                        <span class="badge badge-secondary">Anulada</span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('compras.show', $compra->id) }}"
                                       class="btn btn-primary btn-xs">
                                        Ver
                                    </a>

                                    @if ($compra->estado !== 'Anulada')
                                        <form action="{{ route('compras.anular', $compra->id) }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit"
                                                    class="btn btn-danger btn-xs"
                                                    onclick="return confirm('¿Seguro que desea anular esta compra?')">
                                                Anular
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">
                                    No hay compras registradas con los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $compras->links() }}
        </div>
    </div>
@stop