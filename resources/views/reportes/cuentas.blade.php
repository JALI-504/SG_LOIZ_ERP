@extends('adminlte::page')

@section('title', 'Reporte de cuentas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Reporte de cuentas</h1>

        <button type="button"
                class="btn btn-secondary"
                onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>
@stop

@section('content')
    {{-- Filtros --}}
    <div class="card no-print">
        <div class="card-header">
            <h3 class="card-title">Filtros</h3>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('reportes.cuentas') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label>Desde</label>
                        <input type="date"
                               name="fecha_desde"
                               class="form-control"
                               value="{{ $fechaDesde }}">
                    </div>

                    <div class="col-md-3">
                        <label>Hasta</label>
                        <input type="date"
                               name="fecha_hasta"
                               class="form-control"
                               value="{{ $fechaHasta }}">
                    </div>

                    <div class="col-md-6 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary mr-2">
                            Filtrar
                        </button>

                        <a href="{{ route('reportes.cuentas') }}" class="btn btn-secondary">
                            Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Encabezado --}}
    <div class="card">
        <div class="card-body text-center">
            <h3>Reporte de cuentas por cobrar y por pagar</h3>

            @if ($fechaDesde || $fechaHasta)
                <p class="mb-0">
                    @if ($fechaDesde)
                        Desde <strong>{{ $fechaDesde }}</strong>
                    @endif

                    @if ($fechaHasta)
                        hasta <strong>{{ $fechaHasta }}</strong>
                    @endif
                </p>
            @else
                <p class="mb-0">
                    Todas las cuentas pendientes registradas
                </p>
            @endif
        </div>
    </div>

    {{-- Resumen principal --}}
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>L {{ number_format($totalPorCobrar, 2) }}</h4>
                    <p>Total por cobrar</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4>L {{ number_format($totalPorPagar, 2) }}</h4>
                    <p>Total por pagar</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box {{ $diferencia >= 0 ? 'bg-primary' : 'bg-warning' }}">
                <div class="inner">
                    <h4>L {{ number_format($diferencia, 2) }}</h4>
                    <p>Diferencia neta</p>
                </div>
                <div class="icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ number_format($ventasPorCobrar->count() + $comprasPorPagar->count(), 0) }}</h4>
                    <p>Cuentas pendientes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-list"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Resumen detallado --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Resumen general</h3>
        </div>

        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <td><strong>Ventas pendientes de cobro</strong></td>
                    <td class="text-right">{{ number_format($ventasPorCobrar->count(), 0) }}</td>
                    <td class="text-right">L {{ number_format($totalPorCobrar, 2) }}</td>
                </tr>

                <tr>
                    <td>Total original de ventas pendientes</td>
                    <td></td>
                    <td class="text-right">L {{ number_format($totalVentasOriginal, 2) }}</td>
                </tr>

                <tr>
                    <td>Total pagado por clientes</td>
                    <td></td>
                    <td class="text-right">L {{ number_format($totalVentasPagado, 2) }}</td>
                </tr>

                <tr>
                    <td><strong>Compras pendientes de pago</strong></td>
                    <td class="text-right">{{ number_format($comprasPorPagar->count(), 0) }}</td>
                    <td class="text-right">L {{ number_format($totalPorPagar, 2) }}</td>
                </tr>

                <tr>
                    <td>Total original de compras pendientes</td>
                    <td></td>
                    <td class="text-right">L {{ number_format($totalComprasOriginal, 2) }}</td>
                </tr>

                <tr>
                    <td>Total pagado a proveedores</td>
                    <td></td>
                    <td class="text-right">L {{ number_format($totalComprasPagado, 2) }}</td>
                </tr>

                <tr>
                    <td><strong>Diferencia neta</strong></td>
                    <td></td>
                    <td class="text-right">
                        <strong class="{{ $diferencia >= 0 ? 'text-success' : 'text-danger' }}">
                            L {{ number_format($diferencia, 2) }}
                        </strong>
                    </td>
                </tr>
            </table>

            <div class="alert {{ $diferencia >= 0 ? 'alert-success' : 'alert-warning' }} mt-3">
                @if ($diferencia >= 0)
                    Actualmente las cuentas por cobrar son mayores que las cuentas por pagar.
                @else
                    Actualmente las cuentas por pagar son mayores que las cuentas por cobrar.
                @endif
            </div>
        </div>
    </div>

    {{-- Cuentas por cobrar --}}
    <div class="card">
        <div class="card-header bg-success">
            <h3 class="card-title">Cuentas por cobrar a clientes</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Venta</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Pagado</th>
                            <th>Saldo</th>
                            <th class="no-print">Acción</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($ventasPorCobrar as $venta)
                            <tr>
                                <td>
                                    {{ $venta->fecha }}

                                    @if ($venta->hora)
                                        <br>
                                        <small>{{ $venta->hora }}</small>
                                    @endif
                                </td>

                                <td>
                                    <strong>{{ $venta->numero }}</strong><br>
                                    <small>{{ $venta->metodo_pago }}</small>
                                </td>

                                <td>
                                    @if ($venta->cliente)
                                        {{ trim(
                                            ($venta->cliente->primer_nombre ?? '') . ' ' .
                                            ($venta->cliente->segundo_nombre ?? '') . ' ' .
                                            ($venta->cliente->primer_apellido ?? '') . ' ' .
                                            ($venta->cliente->segundo_apellido ?? '')
                                        ) }}

                                        @if ($venta->cliente->telefono)
                                            <br>
                                            <small>Tel: {{ $venta->cliente->telefono }}</small>
                                        @endif
                                    @else
                                        Consumidor final
                                    @endif
                                </td>

                                <td class="text-right">
                                    L {{ number_format($venta->total, 2) }}
                                </td>

                                <td class="text-right">
                                    L {{ number_format($venta->monto_pagado, 2) }}
                                </td>

                                <td class="text-right">
                                    <strong class="text-danger">
                                        L {{ number_format($venta->saldo_pendiente, 2) }}
                                    </strong>
                                </td>

                                <td class="no-print">
                                    <a href="{{ route('ventas.recibo', $venta->id) }}"
                                       target="_blank"
                                       class="btn btn-success btn-xs">
                                        Recibo
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    No hay cuentas por cobrar pendientes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-right">Totales</th>
                            <th class="text-right">L {{ number_format($totalVentasOriginal, 2) }}</th>
                            <th class="text-right">L {{ number_format($totalVentasPagado, 2) }}</th>
                            <th class="text-right">L {{ number_format($totalPorCobrar, 2) }}</th>
                            <th class="no-print"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="no-print">
                <a href="{{ route('ventas.cuentas-por-cobrar') }}"
                   class="btn btn-success">
                    Ir a cuentas por cobrar
                </a>
            </div>
        </div>
    </div>

    {{-- Cuentas por pagar --}}
    <div class="card">
        <div class="card-header bg-danger">
            <h3 class="card-title">Cuentas por pagar a proveedores</h3>
        </div>

        <div class="card-body">
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
                            <th class="no-print">Acción</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($comprasPorPagar as $compra)
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
                                </td>

                                <td>
                                    @if ($compra->proveedor)
                                        {{ $compra->proveedor->nombre_comercial }}

                                        @if ($compra->proveedor->telefono)
                                            <br>
                                            <small>Tel: {{ $compra->proveedor->telefono }}</small>
                                        @endif

                                        @if ($compra->proveedor->rtn)
                                            <br>
                                            <small>RTN: {{ $compra->proveedor->rtn }}</small>
                                        @endif
                                    @else
                                        Sin proveedor
                                    @endif
                                </td>

                                <td class="text-right">
                                    L {{ number_format($compra->total, 2) }}
                                </td>

                                <td class="text-right">
                                    L {{ number_format($compra->monto_pagado, 2) }}
                                </td>

                                <td class="text-right">
                                    <strong class="text-danger">
                                        L {{ number_format($compra->saldo_pendiente, 2) }}
                                    </strong>
                                </td>

                                <td class="no-print">
                                    <a href="{{ route('compras.show', $compra->id) }}"
                                       class="btn btn-primary btn-xs">
                                        Ver
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    No hay cuentas por pagar pendientes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-right">Totales</th>
                            <th class="text-right">L {{ number_format($totalComprasOriginal, 2) }}</th>
                            <th class="text-right">L {{ number_format($totalComprasPagado, 2) }}</th>
                            <th class="text-right">L {{ number_format($totalPorPagar, 2) }}</th>
                            <th class="no-print"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="no-print">
                <a href="{{ route('compras.cuentas-por-pagar') }}"
                   class="btn btn-danger">
                    Ir a cuentas por pagar
                </a>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        @media print {
            .main-header,
            .main-sidebar,
            .main-footer,
            .control-sidebar,
            .content-header .btn,
            .no-print {
                display: none !important;
            }

            .content-wrapper {
                margin-left: 0 !important;
                padding: 0 !important;
            }

            .card,
            .small-box {
                box-shadow: none !important;
                border: 1px solid #000 !important;
            }

            body {
                font-size: 12px;
            }

            table {
                font-size: 11px;
            }

            .card-header {
                color: #000 !important;
                background: #f2f2f2 !important;
            }
        }
    </style>
@stop