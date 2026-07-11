@extends('adminlte::page')

@section('title', 'Detalle de compra')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Detalle de compra</h1>

        <a href="{{ route('compras.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver
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

    <div class="row">
        <div class="col-md-8">
            {{-- Datos generales --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información general</h3>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-sm">
                        <tr>
                            <th width="220">Número interno</th>
                            <td>
                                <strong>{{ $compra->numero }}</strong>
                            </td>
                        </tr>

                        <tr>
                            <th>Fecha</th>
                            <td>
                                {{ $compra->fecha }}

                                @if ($compra->hora)
                                    {{ $compra->hora }}
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th>Proveedor</th>
                            <td>
                                @if ($compra->proveedor)
                                    <strong>{{ $compra->proveedor->nombre_comercial }}</strong>

                                    @if ($compra->proveedor->nombre_legal)
                                        <br>
                                        {{ $compra->proveedor->nombre_legal }}
                                    @endif

                                    @if ($compra->proveedor->rtn)
                                        <br>
                                        <small>RTN: {{ $compra->proveedor->rtn }}</small>
                                    @endif

                                    @if ($compra->proveedor->telefono)
                                        <br>
                                        <small>Tel: {{ $compra->proveedor->telefono }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">Sin proveedor registrado</span>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th>Comprobante</th>
                            <td>
                                {{ $compra->tipo_comprobante }}

                                @if ($compra->numero_comprobante)
                                    |
                                    <strong>{{ $compra->numero_comprobante }}</strong>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th>Tipo de pago</th>
                            <td>
                                @if ($compra->tipo_pago === 'Contado')
                                    <span class="badge badge-success">Contado</span>
                                @else
                                    <span class="badge badge-warning">Crédito</span>
                                @endif

                                <br>
                                <small>Método: {{ $compra->metodo_pago }}</small>
                            </td>
                        </tr>

                        <tr>
                            <th>Estado</th>
                            <td>
                                @if ($compra->estado === 'Registrada')
                                    <span class="badge badge-success">Registrada</span>
                                @else
                                    <span class="badge badge-secondary">Anulada</span>
                                @endif
                            </td>
                        </tr>

                        <tr>
                            <th>Observación</th>
                            <td>
                                {{ $compra->observacion ?? 'Sin observación' }}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Detalle --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Detalle de productos / insumos comprados</h3>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Tipo</th>
                                    <th>Código</th>
                                    <th>Descripción</th>
                                    <th>Cantidad</th>
                                    <th>Costo unitario</th>
                                    <th>Subtotal</th>
                                    <th>Descuento</th>
                                    <th>Total</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($compra->detalles as $detalle)
                                    <tr>
                                        <td>
                                            @if ($detalle->tipo_item === 'Insumo')
                                                <span class="badge badge-info">Insumo</span>
                                            @else
                                                <span class="badge badge-primary">Producto</span>
                                            @endif
                                        </td>

                                        <td>
                                            {{ $detalle->codigo }}
                                        </td>

                                        <td>
                                            <strong>{{ $detalle->descripcion }}</strong>
                                        </td>

                                        <td>
                                            {{ number_format($detalle->cantidad, 2) }}
                                        </td>

                                        <td>
                                            L {{ number_format($detalle->costo_unitario, 4) }}
                                        </td>

                                        <td>
                                            L {{ number_format($detalle->subtotal, 2) }}
                                        </td>

                                        <td>
                                            L {{ number_format($detalle->descuento, 2) }}
                                        </td>

                                        <td>
                                            <strong>L {{ number_format($detalle->total, 2) }}</strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            Esta compra no tiene detalles registrados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                        @if ($compra->pagos->count() > 0)
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title">Pagos registrados</h3>
                                </div>

                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover table-sm">
                                            <thead class="thead-dark">
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
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="alert alert-warning">
                        Esta compra todavía no actualiza automáticamente el inventario PEPS. Ese será el siguiente paso.
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            {{-- Totales --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Resumen financiero</h3>
                </div>

                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <td>Subtotal:</td>
                            <td class="text-right">
                                L {{ number_format($compra->subtotal, 2) }}
                            </td>
                        </tr>

                        <tr>
                            <td>Descuento:</td>
                            <td class="text-right">
                                L {{ number_format($compra->descuento, 2) }}
                            </td>
                        </tr>

                        <tr>
                            <td>Impuesto:</td>
                            <td class="text-right">
                                L {{ number_format($compra->impuesto, 2) }}
                            </td>
                        </tr>

                        <tr>
                            <td>
                                <strong>Total:</strong>
                            </td>
                            <td class="text-right">
                                <strong>L {{ number_format($compra->total, 2) }}</strong>
                            </td>
                        </tr>

                        <tr>
                            <td>Pagado:</td>
                            <td class="text-right">
                                L {{ number_format($compra->monto_pagado, 2) }}
                            </td>
                        </tr>

                        <tr>
                            <td>Saldo pendiente:</td>
                            <td class="text-right">
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
                        </tr>
                    </table>
                </div>
            </div>

            {{-- Acciones --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Acciones</h3>
                </div>

                <div class="card-body">
                    <a href="{{ route('compras.index') }}"
                       class="btn btn-secondary btn-block">
                        Volver al listado
                    </a>

                    @if ($compra->estado !== 'Anulada')
                        <form action="{{ route('compras.anular', $compra->id) }}"
                              method="POST"
                              class="mt-2">
                            @csrf
                            @method('PATCH')

                            <button type="submit"
                                    class="btn btn-danger btn-block"
                                    onclick="return confirm('¿Seguro que desea anular esta compra?')">
                                Anular compra
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop