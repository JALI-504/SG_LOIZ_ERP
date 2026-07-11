@extends('adminlte::page')

@section('title', 'Cuentas por pagar')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Cuentas por pagar</h1>

        <a href="{{ route('compras.index') }}" class="btn btn-secondary">
            <i class="fas fa-shopping-cart"></i> Ver compras
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
                    <h4>{{ number_format($totalCuentas, 0) }}</h4>
                    <p>Cuentas pendientes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>L {{ number_format($totalComprado, 2) }}</h4>
                    <p>Total comprado</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h4>L {{ number_format($totalPagado, 2) }}</h4>
                    <p>Total pagado</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4>L {{ number_format($totalPendiente, 2) }}</h4>
                    <p>Saldo pendiente</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-circle"></i>
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
            <form method="GET" action="{{ route('compras.cuentas-por-pagar') }}">
                <div class="row">
                    <div class="col-md-4">
                        <label>Buscar</label>
                        <input type="text"
                               name="search"
                               class="form-control"
                               placeholder="Compra, comprobante, proveedor, RTN..."
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

                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-2">
                            Filtrar
                        </button>

                        <a href="{{ route('compras.cuentas-por-pagar') }}" class="btn btn-secondary">
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
            <h3 class="card-title">Compras pendientes de pago</h3>
        </div>

        <div class="card-body">
            <div class="alert alert-info">
                Aquí se muestran las compras al crédito o compras con saldo pendiente. Puedes registrar pagos parciales hasta cancelar el saldo completo.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Compra</th>
                            <th>Proveedor</th>
                            <th>Total</th>
                            <th>Pagado</th>
                            <th>Saldo</th>
                            <th width="260">Registrar pago</th>
                            <th width="90">Acción</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($compras as $compra)
                            <tr>
                                <td>
                                    {{ $compra->fecha }}

                                    @if ($compra->hora)
                                        <br>
                                        <small>{{ $compra->hora }}</small>
                                    @endif
                                </td>

                                <td>
                                    <strong>{{ $compra->numero }}</strong><br>

                                    <small>
                                        {{ $compra->tipo_comprobante }}

                                        @if ($compra->numero_comprobante)
                                            | {{ $compra->numero_comprobante }}
                                        @endif
                                    </small>

                                    <br>

                                    <span class="badge badge-warning">
                                        {{ $compra->tipo_pago }}
                                    </span>
                                </td>

                                <td>
                                    @if ($compra->proveedor)
                                        <strong>{{ $compra->proveedor->nombre_comercial }}</strong>

                                        @if ($compra->proveedor->rtn)
                                            <br>
                                            <small>RTN: {{ $compra->proveedor->rtn }}</small>
                                        @endif

                                        @if ($compra->proveedor->telefono)
                                            <br>
                                            <small>Tel: {{ $compra->proveedor->telefono }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">Sin proveedor</span>
                                    @endif
                                </td>

                                <td>
                                    <strong>L {{ number_format($compra->total, 2) }}</strong>
                                </td>

                                <td>
                                    L {{ number_format($compra->monto_pagado, 2) }}
                                </td>

                                <td>
                                    <strong class="text-danger">
                                        L {{ number_format($compra->saldo_pendiente, 2) }}
                                    </strong>
                                </td>

                                <td>
                                    <form method="POST" action="{{ route('compras.registrar-pago', $compra->id) }}">
                                        @csrf

                                        <div class="form-group mb-1">
                                            <input type="number"
                                                   step="0.01"
                                                   min="0.01"
                                                   max="{{ $compra->saldo_pendiente }}"
                                                   name="monto"
                                                   class="form-control form-control-sm"
                                                   placeholder="Monto a pagar"
                                                   required>
                                        </div>

                                        <div class="form-group mb-1">
                                            <select name="metodo_pago"
                                                    class="form-control form-control-sm"
                                                    required>
                                                @foreach ($metodosPago as $metodo)
                                                    <option value="{{ $metodo }}">
                                                        {{ $metodo }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group mb-1">
                                            <input type="text"
                                                   name="referencia"
                                                   class="form-control form-control-sm"
                                                   placeholder="Referencia opcional">
                                        </div>

                                        <div class="form-group mb-1">
                                            <input type="text"
                                                   name="observacion"
                                                   class="form-control form-control-sm"
                                                   placeholder="Observación opcional">
                                        </div>

                                        <button type="submit"
                                                class="btn btn-success btn-sm btn-block"
                                                onclick="return confirm('¿Desea registrar este pago?')">
                                            Registrar pago
                                        </button>
                                    </form>
                                </td>

                                <td>
                                    <a href="{{ route('compras.show', $compra->id) }}"
                                       class="btn btn-primary btn-xs">
                                        Ver
                                    </a>
                                </td>
                            </tr>

                            @if ($compra->pagos->count() > 0)
                                <tr>
                                    <td colspan="8">
                                        <strong>Pagos registrados:</strong>

                                        <div class="table-responsive mt-2">
                                            <table class="table table-bordered table-sm mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Fecha</th>
                                                        <th>Monto</th>
                                                        <th>Método</th>
                                                        <th>Referencia</th>
                                                        <th>Observación</th>
                                                        <th>Acción</th>
                                                    </tr>
                                                </thead>

                                                <tbody>
                                                    @foreach ($compra->pagos as $pago)
                                                        <tr>
                                                            <td>
                                                                {{ $pago->fecha }}
                                                                {{ $pago->hora }}
                                                            </td>

                                                            <td>
                                                                <strong>L {{ number_format($pago->monto, 2) }}</strong>
                                                            </td>

                                                            <td>
                                                                {{ $pago->metodo_pago }}
                                                            </td>

                                                            <td>
                                                                {{ $pago->referencia ?? 'Sin referencia' }}
                                                            </td>

                                                            <td>
                                                                {{ $pago->observacion ?? 'Sin observación' }}
                                                            </td>
                                                            <td>
                                                                <a href="{{ route('compras.pagos.recibo', $pago->id) }}"
                                                                target="_blank"
                                                                class="btn btn-success btn-xs">
                                                                    Recibo pago
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    No hay cuentas por pagar pendientes.
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