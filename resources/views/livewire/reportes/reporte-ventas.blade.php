<div>
    {{-- Filtros --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Filtros del reporte
            </h3>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <label>Desde</label>
                    <input type="date"
                           class="form-control"
                           wire:model="fechaDesde">
                </div>

                <div class="col-md-3">
                    <label>Hasta</label>
                    <input type="date"
                           class="form-control"
                           wire:model="fechaHasta">
                </div>

                <div class="col-md-3">
                    <label>Método de pago</label>
                    <select class="form-control" wire:model="filtroMetodoPago">
                        <option value="todos">Todos</option>

                        @foreach ($metodosPago as $metodo)
                            <option value="{{ $metodo }}">{{ $metodo }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Tipo de venta</label>
                    <select class="form-control" wire:model="filtroTipoItem">
                        <option value="todos">Productos y servicios</option>
                        <option value="Producto">Solo productos</option>
                        <option value="Servicio">Solo servicios</option>
                    </select>
                </div>
            </div>

            <div class="mt-3">
                <button class="btn btn-secondary btn-sm"
                        wire:click="limpiarFiltros">
                    Limpiar filtros
                </button>
            </div>
        </div>
    </div>

    {{-- Resumen --}}
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ number_format($totalVentas, 0) }}</h4>
                    <p>Ventas válidas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>L {{ number_format($totalVendido, 2) }}</h4>
                    <p>Total vendido</p>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>L {{ number_format($utilidadEstimada, 2) }}</h4>
                    <p>Utilidad estimada</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h4>L {{ number_format($ticketPromedio, 2) }}</h4>
                    <p>Ticket promedio</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Segundo resumen --}}
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h4>L {{ number_format($costoEstimado, 2) }}</h4>
                    <p>Costo estimado</p>
                </div>
                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>L {{ number_format($totalDescuentos, 2) }}</h4>
                    <p>Descuentos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4>{{ number_format($totalAnuladas, 0) }}</h4>
                    <p>Ventas anuladas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-ban"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4>L {{ number_format($montoAnulado, 2) }}</h4>
                    <p>Monto anulado</p>
                </div>
                <div class="icon">
                    <i class="fas fa-times-circle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Ventas por método --}}
        <div class="col-md-5">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Ventas por método de pago
                    </h3>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
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
                                        <td>
                                            <span class="badge badge-info">
                                                {{ $metodo->metodo_pago }}
                                            </span>
                                        </td>

                                        <td>
                                            {{ number_format($metodo->cantidad, 0) }}
                                        </td>

                                        <td>
                                            <strong>L {{ number_format($metodo->total, 2) }}</strong>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center">
                                            No hay ventas por método de pago.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Items más vendidos --}}
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Productos / servicios más vendidos
                    </h3>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Tipo</th>
                                    <th>Código</th>
                                    <th>Descripción</th>
                                    <th>Cant.</th>
                                    <th>Total</th>
                                    <th>Utilidad</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($itemsMasVendidos as $item)
                                    <tr>
                                        <td>
                                            @if ($item->tipo_item === 'Producto')
                                                <span class="badge badge-primary">Producto</span>
                                            @else
                                                <span class="badge badge-info">Servicio</span>
                                            @endif
                                        </td>

                                        <td>
                                            {{ $item->codigo }}
                                        </td>

                                        <td>
                                            {{ $item->descripcion }}
                                        </td>

                                        <td>
                                            {{ number_format($item->cantidad_total, 2) }}
                                        </td>

                                        <td>
                                            <strong>L {{ number_format($item->total_vendido, 2) }}</strong>
                                        </td>

                                        <td>
                                            L {{ number_format($item->utilidad, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            No hay productos o servicios vendidos.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <small class="text-muted">
                        La utilidad es estimada con base en el costo registrado al momento de la venta.
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- Últimas ventas --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Últimas ventas del período
            </h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Número</th>
                            <th>Cliente</th>
                            <th>Método</th>
                            <th>Estado</th>
                            <th>Total</th>
                            <th width="100">Acción</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($ultimasVentas as $venta)
                            <tr>
                                <td>
                                    {{ $venta->fecha }}
                                    @if ($venta->hora)
                                        <br>
                                        <small>{{ $venta->hora }}</small>
                                    @endif
                                </td>

                                <td>
                                    <strong>{{ $venta->numero }}</strong>
                                </td>

                                <td>
                                    @if ($venta->cliente)
                                        {{ trim($venta->cliente->primer_nombre . ' ' . $venta->cliente->segundo_nombre . ' ' . $venta->cliente->primer_apellido . ' ' . $venta->cliente->segundo_apellido) }}
                                    @else
                                        <span class="text-muted">Consumidor final</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $venta->metodo_pago }}
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
                                    <strong>L {{ number_format($venta->total, 2) }}</strong>
                                </td>

                                <td>
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
                                    No hay ventas registradas en este período.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <a href="{{ route('ventas.historial') }}" class="btn btn-primary btn-sm">
                Ver historial completo
            </a>
        </div>
    </div>
</div>