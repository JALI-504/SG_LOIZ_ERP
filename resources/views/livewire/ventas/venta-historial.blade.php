<div>
    <div class="mb-3">
        <a href="{{ route('ventas.index') }}" class="btn btn-success btn-sm">
            <i class="fas fa-cash-register"></i> Ir al POS
        </a>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ number_format($totalVentas, 0) }}</h4>
                    <p>Ventas encontradas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>L {{ number_format($totalMonto, 2) }}</h4>
                    <p>Total vendido</p>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>L {{ number_format($totalDescuento, 2) }}</h4>
                    <p>Total descuentos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Filtros de búsqueda
            </h3>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label>Buscar</label>
                    <input type="text"
                           class="form-control"
                           placeholder="Número, cliente, DNI, RTN, teléfono..."
                           wire:model.debounce.500ms="search">
                </div>

                <div class="col-md-2">
                    <label>Desde</label>
                    <input type="date"
                           class="form-control"
                           wire:model="fechaDesde">
                </div>

                <div class="col-md-2">
                    <label>Hasta</label>
                    <input type="date"
                           class="form-control"
                           wire:model="fechaHasta">
                </div>

                <div class="col-md-2">
                    <label>Estado</label>
                    <select class="form-control" wire:model="filtroEstado">
                        <option value="todos">Todos</option>

                        @foreach ($estadosVenta as $estado)
                            <option value="{{ $estado }}">{{ $estado }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label>Método pago</label>
                    <select class="form-control" wire:model="filtroMetodoPago">
                        <option value="todos">Todos</option>

                        @foreach ($metodosPago as $metodo)
                            <option value="{{ $metodo }}">{{ $metodo }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-2">
                    <label>Mostrar</label>
                    <select class="form-control" wire:model="perPage">
                        <option value="10">10 registros</option>
                        <option value="25">25 registros</option>
                        <option value="50">50 registros</option>
                        <option value="100">100 registros</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Listado --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Historial de ventas
            </h3>
        </div>

        <div class="card-body">
            <div class="alert alert-warning">
                <strong>Comprobantes internos no fiscales.</strong><br>
                Las ventas registradas aquí usan numeración interna tipo REC-000001.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Número</th>
                            <th>Cliente</th>
                            <th>Método pago</th>
                            <th>Estado</th>
                            <th>Subtotal</th>
                            <th>Descuento</th>
                            <th>Total</th>
                            <th>Pagado</th>
                            <th>Saldo</th>
                            <th width="180">Acción</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($ventas as $venta)
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
                                    <small>{{ $venta->tipo_comprobante }}</small>
                                </td>

                                <td>
                                    @if ($venta->cliente)
                                        {{ trim($venta->cliente->primer_nombre . ' ' . $venta->cliente->segundo_nombre . ' ' . $venta->cliente->primer_apellido . ' ' . $venta->cliente->segundo_apellido) }}

                                        @if ($venta->cliente->dni)
                                            <br>
                                            <small>DNI: {{ $venta->cliente->dni }}</small>
                                        @endif

                                        @if ($venta->cliente->telefono)
                                            <br>
                                            <small>Tel: {{ $venta->cliente->telefono }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">Consumidor final</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge badge-info">
                                        {{ $venta->metodo_pago }}
                                    </span>
                                </td>

                                <td>
                                    @if ($venta->estado === 'Pagada')
                                        <span class="badge badge-success">Pagada</span>
                                    @elseif ($venta->estado === 'Pendiente')
                                        <span class="badge badge-warning">Pendiente</span>
                                    @elseif ($venta->estado === 'Anulada')
                                        <span class="badge badge-danger">Anulada</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $venta->estado }}</span>
                                    @endif
                                </td>

                                <td>
                                    L {{ number_format($venta->subtotal, 2) }}
                                </td>

                                <td>
                                    L {{ number_format($venta->descuento, 2) }}
                                </td>

                                <td>
                                    <strong>L {{ number_format($venta->total, 2) }}</strong>
                                </td>

                                <td>
                                    L {{ number_format($venta->monto_pagado, 2) }}
                                </td>

                                <td>
                                    @if ($venta->saldo_pendiente > 0)
                                        <strong class="text-danger">
                                            L {{ number_format($venta->saldo_pendiente, 2) }}
                                        </strong>
                                    @else
                                        <strong class="text-success">
                                            L 0.00
                                        </strong>
                                    @endif
                                </td>

                                <td>
                                    <button class="btn btn-primary btn-xs"
                                            wire:click="verDetalle({{ $venta->id }})">
                                        @if ($ventaSeleccionadaId == $venta->id)
                                            Ocultar
                                        @else
                                            Ver detalle
                                        @endif
                                    </button>

                                    <a href="{{ route('ventas.recibo', $venta->id) }}"
                                    target="_blank"
                                    class="btn btn-success btn-xs">
                                        Recibo
                                    </a>

                                    @if ($venta->estado !== 'Anulada')
                                        <button class="btn btn-danger btn-xs"
                                                onclick="confirm('¿Seguro que desea anular esta venta? Se restaurará el inventario.') || event.stopImmediatePropagation()"
                                                wire:click="anularVenta({{ $venta->id }})">
                                            Anular
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center">
                                    No hay ventas registradas con los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $ventas->links() }}
        </div>
    </div>

    {{-- Detalle de venta --}}
    @if ($ventaSeleccionada)
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">
                    Detalle de venta {{ $ventaSeleccionada->numero }}
                </h3>

                <div class="card-tools">
                    <button class="btn btn-light btn-sm" wire:click="cerrarDetalle">
                        Cerrar
                    </button>
                </div>
            </div>

            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <strong>Número:</strong><br>
                        {{ $ventaSeleccionada->numero }}
                    </div>

                    <div class="col-md-3">
                        <strong>Fecha:</strong><br>
                        {{ $ventaSeleccionada->fecha }}
                        {{ $ventaSeleccionada->hora }}
                    </div>

                    <div class="col-md-3">
                        <strong>Método pago:</strong><br>
                        {{ $ventaSeleccionada->metodo_pago }}
                    </div>

                    <div class="col-md-3">
                        <strong>Estado:</strong><br>
                        {{ $ventaSeleccionada->estado }}
                    </div>
                </div>

                <div class="mb-3">
                    <strong>Cliente:</strong><br>

                    @if ($ventaSeleccionada->cliente)
                        {{ trim($ventaSeleccionada->cliente->primer_nombre . ' ' . $ventaSeleccionada->cliente->segundo_nombre . ' ' . $ventaSeleccionada->cliente->primer_apellido . ' ' . $ventaSeleccionada->cliente->segundo_apellido) }}
                    @else
                        Consumidor final
                    @endif
                </div>

                @if ($ventaSeleccionada->observacion)
                    <div class="mb-3">
                        <strong>Observación:</strong><br>
                        {{ $ventaSeleccionada->observacion }}
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Tipo</th>
                                <th>Código</th>
                                <th>Descripción</th>
                                <th>Cantidad</th>
                                <th>Precio</th>
                                <th>Costo</th>
                                <th>Descuento</th>
                                <th>Total</th>
                                <th>Utilidad</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($ventaSeleccionada->detalles as $detalle)
                                <tr>
                                    <td>
                                        @if ($detalle->tipo_item === 'Producto')
                                            <span class="badge badge-primary">Producto</span>
                                        @else
                                            <span class="badge badge-info">Servicio</span>
                                        @endif
                                    </td>

                                    <td>
                                        {{ $detalle->codigo }}
                                    </td>

                                    <td>
                                        {{ $detalle->descripcion }}
                                    </td>

                                    <td>
                                        {{ number_format($detalle->cantidad, 2) }}
                                    </td>

                                    <td>
                                        L {{ number_format($detalle->precio_unitario, 2) }}
                                    </td>

                                    <td>
                                        L {{ number_format($detalle->costo_unitario, 4) }}
                                    </td>

                                    <td>
                                        L {{ number_format($detalle->descuento, 2) }}
                                    </td>

                                    <td>
                                        <strong>L {{ number_format($detalle->total, 2) }}</strong>
                                    </td>

                                    <td>
                                        L {{ number_format($detalle->utilidad, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center">
                                        Esta venta no tiene detalle registrado.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <hr>
                @if ($ventaSeleccionada->pagos->count() > 0)
    <div class="mt-4">
        <h5>Abonos registrados</h5>

        <div class="table-responsive">
            <table class="table table-bordered table-hover table-sm">
                <thead class="thead-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Referencia</th>
                        <th>Observación</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($ventaSeleccionada->pagos as $pago)
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
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                <hr>

                <div class="row">
                    <div class="col-md-4 offset-md-8">
                        <div class="d-flex justify-content-between">
                            <span>Subtotal:</span>
                            <strong>L {{ number_format($ventaSeleccionada->subtotal, 2) }}</strong>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span>Descuento:</span>
                            <strong>L {{ number_format($ventaSeleccionada->descuento, 2) }}</strong>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span>Impuesto:</span>
                            <strong>L {{ number_format($ventaSeleccionada->impuesto, 2) }}</strong>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span>Pagado:</span>
                            <strong>L {{ number_format($ventaSeleccionada->monto_pagado, 2) }}</strong>
                        </div>

                        <div class="d-flex justify-content-between">
                            <span>Saldo pendiente:</span>
                            <strong class="{{ $ventaSeleccionada->saldo_pendiente > 0 ? 'text-danger' : 'text-success' }}">
                                L {{ number_format($ventaSeleccionada->saldo_pendiente, 2) }}
                            </strong>
                        </div>

                        <div class="d-flex justify-content-between mt-2">
                            <h4>Total:</h4>
                            <h4>
                                <strong>L {{ number_format($ventaSeleccionada->total, 2) }}</strong>
                            </h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>