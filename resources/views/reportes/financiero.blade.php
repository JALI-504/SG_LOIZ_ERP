@extends('adminlte::page')

@section('title', 'Reporte financiero')

@section('content_header')
    <h1>Reporte financiero</h1>
@stop

@section('content')

    {{-- Botones --}}
    <div class="mb-3 no-print">
        <a href="{{ route('reportes.financiero.excel', [
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
        ]) }}" class="btn btn-success">
            <i class="fas fa-file-excel"></i> Exportar Excel
        </a>

        <button type="button"
                class="btn btn-secondary"
                onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir reporte
        </button>
    </div>

    {{-- Filtros --}}
    <div class="card no-print">
        <div class="card-header">
            <h3 class="card-title">Filtros del reporte</h3>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('reportes.financiero') }}">
                <div class="row">
                    <div class="col-md-3">
                        <label>Desde</label>
                        <input type="date"
                               name="fecha_desde"
                               value="{{ $fechaDesde }}"
                               class="form-control">
                    </div>

                    <div class="col-md-3">
                        <label>Hasta</label>
                        <input type="date"
                               name="fecha_hasta"
                               value="{{ $fechaHasta }}"
                               class="form-control">
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Filtrar
                        </button>
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <a href="{{ route('reportes.financiero') }}"
                           class="btn btn-secondary">
                            <i class="fas fa-broom"></i> Limpiar
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Resumen principal --}}
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>L {{ number_format($totalVentas, 2) }}</h4>
                    <p>Ingresos por ventas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h4>L {{ number_format($costoVentas, 2) }}</h4>
                    <p>Costo de ventas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>L {{ number_format($totalGastos, 2) }}</h4>
                    <p>Gastos registrados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box {{ $utilidadNetaEstimada >= 0 ? 'bg-info' : 'bg-danger' }}">
                <div class="inner">
                    <h4>L {{ number_format($utilidadNetaEstimada, 2) }}</h4>
                    <p>Utilidad neta estimada</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Resumen fiscal --}}
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>{{ number_format($totalFacturasFiscales, 0) }}</h4>
                    <p>Facturas fiscales</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h4>{{ number_format($totalRecibosInternos, 0) }}</h4>
                    <p>Recibos internos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>L {{ number_format($totalIsv15, 2) }}</h4>
                    <p>ISV generado</p>
                </div>
                <div class="icon">
                    <i class="fas fa-percent"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>L {{ number_format($totalRetencion, 2) }}</h4>
                    <p>Retenciones</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Desglose financiero --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-pie"></i> Estado financiero estimado
                    </h3>
                </div>

                <div class="card-body p-0">
                    <table class="table table-striped table-sm mb-0">
                        <tbody>
                            <tr>
                                <th>Ventas válidas</th>
                                <td class="text-right">{{ number_format($cantidadVentas, 0) }}</td>
                            </tr>

                            <tr>
                                <th>Total vendido</th>
                                <td class="text-right">
                                    <strong>L {{ number_format($totalVentas, 2) }}</strong>
                                </td>
                            </tr>

                            <tr>
                                <th>Descuentos en ventas</th>
                                <td class="text-right">
                                    L {{ number_format($totalDescuentosVentas, 2) }}
                                </td>
                            </tr>

                            <tr>
                                <th>Costo de ventas</th>
                                <td class="text-right">
                                    L {{ number_format($costoVentas, 2) }}
                                </td>
                            </tr>

                            <tr class="table-info">
                                <th>Utilidad bruta estimada</th>
                                <td class="text-right">
                                    <strong>L {{ number_format($utilidadBruta, 2) }}</strong>
                                </td>
                            </tr>

                            <tr>
                                <th>Gastos registrados</th>
                                <td class="text-right">
                                    L {{ number_format($totalGastos, 2) }}
                                </td>
                            </tr>

                            <tr class="{{ $utilidadNetaEstimada >= 0 ? 'table-success' : 'table-danger' }}">
                                <th>Utilidad neta estimada</th>
                                <td class="text-right">
                                    <strong>L {{ number_format($utilidadNetaEstimada, 2) }}</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    <small class="text-muted">
                        La utilidad es estimada con base en los costos registrados al momento de la venta.
                    </small>
                </div>
            </div>
        </div>

        {{-- Desglose fiscal --}}
        <div class="col-md-6">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-invoice-dollar"></i> Desglose fiscal
                    </h3>
                </div>

                <div class="card-body p-0">
                    <table class="table table-striped table-sm mb-0">
                        <tbody>
                            <tr>
                                <th>Subtotal gravado</th>
                                <td class="text-right">
                                    L {{ number_format($totalSubtotalGravado, 2) }}
                                </td>
                            </tr>

                            <tr>
                                <th>Subtotal exento</th>
                                <td class="text-right">
                                    L {{ number_format($totalSubtotalExento, 2) }}
                                </td>
                            </tr>

                            <tr>
                                <th>Subtotal no sujeto</th>
                                <td class="text-right">
                                    L {{ number_format($totalSubtotalNoSujeto, 2) }}
                                </td>
                            </tr>

                            <tr class="table-info">
                                <th>ISV 15%</th>
                                <td class="text-right">
                                    <strong>L {{ number_format($totalIsv15, 2) }}</strong>
                                </td>
                            </tr>

                            <tr class="table-warning">
                                <th>Retención</th>
                                <td class="text-right">
                                    <strong>L {{ number_format($totalRetencion, 2) }}</strong>
                                </td>
                            </tr>

                            <tr class="table-success">
                                <th>Neto recibido</th>
                                <td class="text-right">
                                    <strong>L {{ number_format($totalNetoRecibido, 2) }}</strong>
                                </td>
                            </tr>

                            <tr class="bg-dark text-white">
                                <th>Total vendido</th>
                                <td class="text-right">
                                    <strong>L {{ number_format($totalVentas, 2) }}</strong>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer">
                    <small class="text-muted">
                        Este desglose excluye ventas anuladas.
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- Cuentas y compras --}}
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-purple">
                <div class="inner">
                    <h4>L {{ number_format($totalCompras, 2) }}</h4>
                    <p>Compras del período</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-primary">
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
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4>L {{ number_format($cuentasPorPagar, 2) }}</h4>
                    <p>Cuentas por pagar</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h4>{{ number_format($cantidadCompras, 0) }}</h4>
                    <p>Compras registradas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-truck"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Tablas --}}
    <div class="row">
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
                                    <td class="text-right">
                                        L {{ number_format($metodo->total, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">
                                        No hay ventas registradas.
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
                            @forelse ($gastosPorCategoria as $categoria)
                                <tr>
                                    <td>{{ $categoria->categoria }}</td>
                                    <td>{{ number_format($categoria->cantidad, 0) }}</td>
                                    <td class="text-right">
                                        L {{ number_format($categoria->total, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center">
                                        No hay gastos registrados.
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
            .content-header,
            .no-print {
                display: none !important;
            }

            .content-wrapper {
                margin-left: 0 !important;
                padding: 0 !important;
            }

            .content {
                padding: 0 !important;
            }

            .card,
            .small-box {
                page-break-inside: avoid;
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