@extends('adminlte::page')

@section('title', 'Reporte financiero')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Reporte financiero</h1>

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
            <form method="GET" action="{{ route('reportes.financiero') }}">
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

                        <a href="{{ route('reportes.financiero') }}" class="btn btn-secondary">
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
            <h3>Reporte financiero general</h3>
            <p class="mb-0">
                Desde <strong>{{ $fechaDesde }}</strong>
                hasta <strong>{{ $fechaHasta }}</strong>
            </p>
        </div>
    </div>

    {{-- Resumen principal --}}
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>L {{ number_format($totalVentas, 2) }}</h4>
                    <p>Ventas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-cash-register"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>L {{ number_format($costoVentas, 2) }}</h4>
                    <p>Costo estimado ventas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h4>L {{ number_format($utilidadBruta, 2) }}</h4>
                    <p>Utilidad bruta</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4>L {{ number_format($totalGastos, 2) }}</h4>
                    <p>Gastos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Utilidad neta y saldos --}}
    <div class="row">
        <div class="col-md-3">
            <div class="small-box {{ $utilidadNetaEstimada >= 0 ? 'bg-success' : 'bg-danger' }}">
                <div class="inner">
                    <h4>L {{ number_format($utilidadNetaEstimada, 2) }}</h4>
                    <p>Utilidad neta estimada</p>
                </div>
                <div class="icon">
                    <i class="fas fa-balance-scale"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>L {{ number_format($totalCompras, 2) }}</h4>
                    <p>Compras</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h4>L {{ number_format($cuentasPorCobrar, 2) }}</h4>
                    <p>Cuentas por cobrar</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-dark">
                <div class="inner">
                    <h4>L {{ number_format($cuentasPorPagar, 2) }}</h4>
                    <p>Cuentas por pagar</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla resumen --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Resumen financiero</h3>
        </div>

        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <td>Ventas registradas</td>
                    <td class="text-right">{{ number_format($cantidadVentas, 0) }}</td>
                    <td class="text-right">L {{ number_format($totalVentas, 2) }}</td>
                </tr>

                <tr>
                    <td>Descuentos en ventas</td>
                    <td></td>
                    <td class="text-right">L {{ number_format($totalDescuentosVentas, 2) }}</td>
                </tr>

                <tr>
                    <td>Costo estimado de ventas</td>
                    <td></td>
                    <td class="text-right">L {{ number_format($costoVentas, 2) }}</td>
                </tr>

                <tr>
                    <td><strong>Utilidad bruta estimada</strong></td>
                    <td></td>
                    <td class="text-right">
                        <strong>L {{ number_format($utilidadBruta, 2) }}</strong>
                    </td>
                </tr>

                <tr>
                    <td>Gastos registrados</td>
                    <td class="text-right">{{ number_format($cantidadGastos, 0) }}</td>
                    <td class="text-right">L {{ number_format($totalGastos, 2) }}</td>
                </tr>

                <tr>
                    <td><strong>Utilidad neta estimada</strong></td>
                    <td></td>
                    <td class="text-right">
                        <strong class="{{ $utilidadNetaEstimada >= 0 ? 'text-success' : 'text-danger' }}">
                            L {{ number_format($utilidadNetaEstimada, 2) }}
                        </strong>
                    </td>
                </tr>

                <tr>
                    <td>Compras registradas</td>
                    <td class="text-right">{{ number_format($cantidadCompras, 0) }}</td>
                    <td class="text-right">L {{ number_format($totalCompras, 2) }}</td>
                </tr>

                <tr>
                    <td>Cuentas por cobrar pendientes</td>
                    <td></td>
                    <td class="text-right">L {{ number_format($cuentasPorCobrar, 2) }}</td>
                </tr>

                <tr>
                    <td>Cuentas por pagar pendientes</td>
                    <td></td>
                    <td class="text-right">L {{ number_format($cuentasPorPagar, 2) }}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row">
        {{-- Ventas por método --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Ventas por método de pago</h3>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Método</th>
                                <th>Cantidad</th>
                                <th>Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($ventasPorMetodo as $metodo)
                                <tr>
                                    <td>{{ $metodo->metodo_pago }}</td>
                                    <td>{{ number_format($metodo->cantidad, 0) }}</td>
                                    <td>L {{ number_format($metodo->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">
                                        No hay ventas en el período seleccionado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Gastos por categoría --}}
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Gastos por categoría</h3>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Categoría</th>
                                <th>Cantidad</th>
                                <th>Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($gastosPorCategoria as $gasto)
                                <tr>
                                    <td>{{ $gasto->categoria }}</td>
                                    <td>{{ number_format($gasto->cantidad, 0) }}</td>
                                    <td>L {{ number_format($gasto->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">
                                        No hay gastos en el período seleccionado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Últimas ventas y gastos --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Últimas ventas del período</h3>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Número</th>
                                <th>Cliente</th>
                                <th>Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($ultimasVentas as $venta)
                                <tr>
                                    <td>{{ $venta->fecha }}</td>
                                    <td>{{ $venta->numero }}</td>
                                    <td>
                                        @if ($venta->cliente)
                                            {{ trim($venta->cliente->primer_nombre . ' ' . $venta->cliente->primer_apellido) }}
                                        @else
                                            Consumidor final
                                        @endif
                                    </td>
                                    <td>L {{ number_format($venta->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">
                                        No hay ventas recientes.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Últimos gastos del período</h3>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Categoría</th>
                                <th>Descripción</th>
                                <th>Monto</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($ultimosGastos as $gasto)
                                <tr>
                                    <td>{{ $gasto->fecha }}</td>
                                    <td>{{ $gasto->categoria }}</td>
                                    <td>{{ $gasto->descripcion }}</td>
                                    <td>L {{ number_format($gasto->monto, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">
                                        No hay gastos recientes.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
        }
    </style>
@stop