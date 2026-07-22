<div>
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

    <div class="mb-3">
        <a href="{{ route('ventas.index') }}" class="btn btn-success btn-sm">
            <i class="fas fa-cash-register"></i> Nueva venta
        </a>

        <a href="{{ route('ventas.historial') }}" class="btn btn-primary btn-sm">
            <i class="fas fa-receipt"></i> Historial ventas
        </a>
    </div>

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
                    <h4>L {{ number_format($totalOriginal, 2) }}</h4>
                    <p>Total original</p>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h4>L {{ number_format($totalPagado, 2) }}</h4>
                    <p>Total abonado</p>
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
            <div class="row">
                <div class="col-md-3">
                    <label>Buscar</label>
                    <input type="text"
                           class="form-control"
                           placeholder="Número, cliente, DNI, RTN o teléfono..."
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
                    <label>Comprobante</label>
                    <select class="form-control" wire:model="filtroComprobante">
                        <option value="todos">Todos</option>
                        <option value="interno">Recibos internos</option>
                        <option value="fiscal">Facturas fiscales</option>
                    </select>
                </div>

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

    {{-- Tabla --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Ventas pendientes de pago</h3>
        </div>

        <div class="card-body">
            <div class="alert alert-info">
                Aquí se muestran las ventas con saldo pendiente. Al registrar un abono, el sistema actualiza el monto pagado y el saldo.
                Si el saldo llega a cero, la venta se marca automáticamente como pagada.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Número</th>
                            <th>Cliente</th>
                            <th>Total</th>
                            <th>Pagado</th>
                            <th>Saldo</th>
                            <th>Estado</th>
                            <th width="170">Acciones</th>
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

                                    @if ($venta->es_fiscal)
                                        <span class="badge badge-success">
                                            <i class="fas fa-file-invoice"></i> Factura fiscal
                                        </span>

                                        @if ($venta->cai)
                                            <br>
                                            <small class="text-muted">
                                                CAI: {{ $venta->cai }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="badge badge-secondary">
                                            <i class="fas fa-receipt"></i> Recibo interno
                                        </span>
                                    @endif
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
                                    <strong>L {{ number_format($venta->total, 2) }}</strong>
                                </td>

                                <td>
                                    L {{ number_format($venta->monto_pagado, 2) }}
                                </td>

                                <td>
                                    <strong class="text-danger">
                                        L {{ number_format($venta->saldo_pendiente, 2) }}
                                    </strong>
                                </td>

                                <td>
                                    @if ($venta->estado === 'Pendiente')
                                        <span class="badge badge-warning">Pendiente</span>
                                    @elseif ($venta->estado === 'Pagada')
                                        <span class="badge badge-success">Pagada</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $venta->estado }}</span>
                                    @endif
                                </td>

                                <td>
                                    <button class="btn btn-success btn-xs"
                                            wire:click="abrirAbono({{ $venta->id }})">
                                        Abonar
                                    </button>

                                    <a href="{{ route('ventas.recibo', $venta->id) }}"
                                    target="_blank"
                                    class="btn btn-primary btn-xs">
                                        @if ($venta->es_fiscal)
                                            <i class="fas fa-file-invoice"></i> Factura
                                        @else
                                            <i class="fas fa-receipt"></i> Recibo
                                        @endif
                                    </a>
                                </td>
                            </tr>

                            @if ($venta->pagos->count() > 0)
                                <tr>
                                    <td colspan="8">
                                        <strong>Abonos registrados:</strong>

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
                                                    @foreach ($venta->pagos as $pago)
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
                                                                <a href="{{ route('ventas.pagos.recibo', $pago->id) }}"
                                                                target="_blank"
                                                                class="btn btn-success btn-xs">
                                                                    Recibo abono
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
                                    No hay cuentas pendientes de cobro.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $ventas->links() }}
        </div>
    </div>

    {{-- Modal abono --}}
    <div wire:ignore.self class="modal fade" id="abonoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <form wire:submit.prevent="registrarAbono" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar abono</h5>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    @if ($ventaSeleccionada)
                        <div class="alert alert-info">
                            <strong>
                                {{ $ventaSeleccionada->es_fiscal ? 'Factura fiscal:' : 'Recibo interno:' }}
                            </strong>
                            {{ $ventaSeleccionada->numero }} <br>
                            <strong>Total:</strong> L {{ number_format($ventaSeleccionada->total, 2) }} <br>
                            <strong>Pagado:</strong> L {{ number_format($ventaSeleccionada->monto_pagado, 2) }} <br>
                            @if (($ventaSeleccionada->retencion ?? 0) > 0)
                                <strong>Retención aplicada:</strong>
                                L {{ number_format($ventaSeleccionada->retencion, 2) }} <br>
                            @endif
                            <strong>Saldo pendiente:</strong>
                            <span class="text-danger">
                                L {{ number_format($ventaSeleccionada->saldo_pendiente, 2) }}
                            </span>
                        </div>
                    @endif

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Monto del abono <span class="text-danger">*</span></label>
                            <input type="number"
                                   step="0.01"
                                   min="0.01"
                                   class="form-control"
                                   wire:model.defer="monto_abono">

                            @error('monto_abono')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>Método de pago <span class="text-danger">*</span></label>
                            <select class="form-control" wire:model.defer="metodo_pago">
                                @foreach ($metodosPago as $metodo)
                                    <option value="{{ $metodo }}">{{ $metodo }}</option>
                                @endforeach
                            </select>

                            @error('metodo_pago')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Referencia</label>
                        <input type="text"
                               class="form-control"
                               placeholder="Ej: Transferencia #123, recibo manual, nota..."
                               wire:model.defer="referencia">

                        @error('referencia')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Observación</label>
                        <textarea class="form-control"
                                  rows="2"
                                  wire:model.defer="observacion"></textarea>

                        @error('observacion')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit"
                            class="btn btn-success"
                            wire:loading.attr="disabled"
                            wire:target="registrarAbono">
                        <span wire:loading.remove wire:target="registrarAbono">
                            Registrar abono
                        </span>

                        <span wire:loading wire:target="registrarAbono">
                            Guardando...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>