@extends('adminlte::page')

@section('title', 'Reporte de inventario')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Reporte de inventario</h1>

        <button type="button"
                class="btn btn-secondary"
                onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir
        </button>
    </div>
@stop

@section('content')
    {{-- Resumen --}}
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ number_format($totalInsumos, 0) }}</h4>
                    <p>Insumos activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-layer-group"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h4>{{ number_format($totalProductos, 0) }}</h4>
                    <p>Productos con inventario</p>
                </div>
                <div class="icon">
                    <i class="fas fa-box"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>L {{ number_format($valorInventarioInsumos, 2) }}</h4>
                    <p>Valor inventario insumos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>L {{ number_format($valorInventarioTotal, 2) }}</h4>
                    <p>Valor inventario total</p>
                </div>
                <div class="icon">
                    <i class="fas fa-warehouse"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Stock bajo --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">Insumos con stock bajo</h3>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Código</th>
                                <th>Insumo</th>
                                <th>Stock</th>
                                <th>Mínimo</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($insumosStockBajo as $insumo)
                                <tr>
                                    <td>{{ $insumo->codigo }}</td>
                                    <td>{{ $insumo->nombre }}</td>
                                    <td>
                                        <strong class="text-danger">
                                            {{ number_format($insumo->stock_actual, 2) }}
                                        </strong>
                                        {{ $insumo->unidad_consumo }}
                                    </td>
                                    <td>
                                        {{ number_format($insumo->stock_minimo, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">
                                        No hay insumos con stock bajo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title">Productos con stock bajo</h3>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Stock</th>
                                <th>Mínimo</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($productosStockBajo as $producto)
                                <tr>
                                    <td>{{ $producto->codigo }}</td>
                                    <td>{{ $producto->nombre }}</td>
                                    <td>
                                        <strong class="text-danger">
                                            {{ number_format($producto->stock_actual, 2) }}
                                        </strong>
                                        {{ $producto->unidad_venta }}
                                    </td>
                                    <td>
                                        {{ number_format($producto->stock_minimo, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">
                                        No hay productos con stock bajo.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Lotes PEPS --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Lotes PEPS disponibles de insumos</h3>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Insumo</th>
                                <th>Disponible</th>
                                <th>Costo</th>
                                <th>Valor</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($lotesInsumos as $lote)
                                <tr>
                                    <td>{{ $lote->fecha_entrada }}</td>
                                    <td>
                                        @if ($lote->insumo)
                                            {{ $lote->insumo->nombre }}
                                        @else
                                            Sin insumo
                                        @endif
                                    </td>
                                    <td>{{ number_format($lote->cantidad_disponible, 2) }}</td>
                                    <td>L {{ number_format($lote->costo_unitario, 4) }}</td>
                                    <td>
                                        L {{ number_format($lote->cantidad_disponible * $lote->costo_unitario, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">
                                        No hay lotes disponibles de insumos.
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
                    <h3 class="card-title">Lotes PEPS disponibles de productos</h3>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Producto</th>
                                <th>Disponible</th>
                                <th>Costo</th>
                                <th>Valor</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($lotesProductos as $lote)
                                <tr>
                                    <td>{{ $lote->fecha_entrada }}</td>
                                    <td>
                                        @if ($lote->producto)
                                            {{ $lote->producto->nombre }}
                                        @else
                                            Sin producto
                                        @endif
                                    </td>
                                    <td>{{ number_format($lote->cantidad_disponible, 2) }}</td>
                                    <td>L {{ number_format($lote->costo_unitario, 4) }}</td>
                                    <td>
                                        L {{ number_format($lote->cantidad_disponible * $lote->costo_unitario, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">
                                        No hay lotes disponibles de productos.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Movimientos recientes --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Movimientos recientes de insumos</h3>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Insumo</th>
                                <th>Tipo</th>
                                <th>Cantidad</th>
                                <th>Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($movimientosInsumos as $movimiento)
                                <tr>
                                    <td>{{ $movimiento->created_at }}</td>
                                    <td>
                                        {{ $movimiento->insumo->nombre ?? 'Sin insumo' }}
                                    </td>
                                    <td>{{ $movimiento->tipo_movimiento }}</td>
                                    <td>{{ number_format($movimiento->cantidad, 2) }}</td>
                                    <td>L {{ number_format($movimiento->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">
                                        No hay movimientos recientes de insumos.
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
                    <h3 class="card-title">Movimientos recientes de productos</h3>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Fecha</th>
                                <th>Producto</th>
                                <th>Tipo</th>
                                <th>Cantidad</th>
                                <th>Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($movimientosProductos as $movimiento)
                                <tr>
                                    <td>{{ $movimiento->created_at }}</td>
                                    <td>
                                        {{ $movimiento->producto->nombre ?? 'Sin producto' }}
                                    </td>
                                    <td>{{ $movimiento->tipo_movimiento }}</td>
                                    <td>{{ number_format($movimiento->cantidad, 2) }}</td>
                                    <td>L {{ number_format($movimiento->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">
                                        No hay movimientos recientes de productos.
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
            .content-header .btn {
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