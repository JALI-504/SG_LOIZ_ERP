<div>
    <div class="alert alert-info">
        <strong>Resumen del día:</strong>
        {{ \Carbon\Carbon::parse($hoy)->format('d/m/Y') }}
    </div>

    {{-- Accesos rápidos --}}
    <div class="mb-3">
        <a href="{{ route('ventas.index') }}" class="btn btn-success">
            <i class="fas fa-cash-register"></i> Nueva venta
        </a>

        <a href="{{ route('ventas.historial') }}" class="btn btn-primary">
            <i class="fas fa-receipt"></i> Historial ventas
        </a>

        <a href="{{ route('reportes.ventas') }}" class="btn btn-info">
            <i class="fas fa-chart-line"></i> Reporte ventas
        </a>

        <a href="{{ route('productos.index') }}" class="btn btn-secondary">
            <i class="fas fa-cube"></i> Productos
        </a>

        <a href="{{ route('insumos.index') }}" class="btn btn-secondary">
            <i class="fas fa-boxes"></i> Insumos
        </a>
    </div>

    {{-- Resumen principal --}}
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ number_format($totalVentasHoy, 0) }}</h4>
                    <p>Ventas de hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>L {{ number_format($totalVendidoHoy, 2) }}</h4>
                    <p>Total vendido hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>L {{ number_format($utilidadEstimadaHoy, 2) }}</h4>
                    <p>Utilidad estimada hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h4>L {{ number_format($ticketPromedioHoy, 2) }}</h4>
                    <p>Ticket promedio</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Resumen secundario --}}
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h4>L {{ number_format($costoEstimadoHoy, 2) }}</h4>
                    <p>Costo estimado hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>L {{ number_format($totalDescuentosHoy, 2) }}</h4>
                    <p>Descuentos hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4>{{ number_format($ventasPendientesHoy, 0) }}</h4>
                    <p>Ventas pendientes hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4>{{ number_format($ventasAnuladasHoy, 0) }}</h4>
                    <p>Ventas anuladas hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-ban"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Últimas ventas --}}
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Últimas ventas
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
                                    <th>Estado</th>
                                    <th>Total</th>
                                    <th width="90">Acción</th>
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
                                            @if ($venta->estado === 'Pagada')
                                                <span class="badge badge-success">Pagada</span>
                                            @elseif ($venta->estado === 'Pendiente')
                                                <span class="badge badge-warning">Pendiente</span>
                                            @elseif ($venta->estado === 'Anulada')
                                                <span class="badge badge-danger">Anulada</span>
                                            @else
                                                <span class="badge badge-secondary">
                                                    {{ $venta->estado }}
                                                </span>
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
                                        <td colspan="6" class="text-center">
                                            No hay ventas registradas.
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

        {{-- Alertas de stock --}}
        <div class="col-md-5">
            <div class="card">
                <div class="card-header bg-danger">
                    <h3 class="card-title">
                        Alertas de inventario
                    </h3>
                </div>

                <div class="card-body">
                    <h5>Productos con stock bajo</h5>

                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Producto</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($productosStockBajo as $producto)
                                    <tr>
                                        <td>
                                            <strong>{{ $producto->codigo }}</strong><br>
                                            {{ $producto->nombre }}
                                        </td>

                                        <td>
                                            <span class="badge badge-danger">
                                                {{ number_format($producto->stock_actual, 2) }}
                                            </span>
                                            <br>
                                            <small>
                                                Mínimo:
                                                {{ number_format($producto->stock_minimo, 2) }}
                                            </small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">
                                            No hay productos con stock bajo.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <h5>Insumos con stock bajo</h5>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Insumo</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($insumosStockBajo as $insumo)
                                    <tr>
                                        <td>
                                            <strong>{{ $insumo->codigo }}</strong><br>
                                            {{ $insumo->nombre }}
                                        </td>

                                        <td>
                                            <span class="badge badge-danger">
                                                {{ number_format($insumo->stock_actual, 2) }}
                                                {{ $insumo->unidad_consumo }}
                                            </span>
                                            <br>
                                            <small>
                                                Mínimo:
                                                {{ number_format($insumo->stock_minimo, 2) }}
                                                {{ $insumo->unidad_consumo }}
                                            </small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">
                                            No hay insumos con stock bajo.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <a href="{{ route('insumos.index') }}" class="btn btn-secondary btn-sm">
                        Ver insumos
                    </a>

                    <a href="{{ route('productos.index') }}" class="btn btn-secondary btn-sm">
                        Ver productos
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Más vendidos del día --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Productos más vendidos hoy
                    </h3>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Cant.</th>
                                <th>Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($productosMasVendidosHoy as $item)
                                <tr>
                                    <td>{{ $item->codigo }}</td>
                                    <td>{{ $item->descripcion }}</td>
                                    <td>{{ number_format($item->cantidad_total, 2) }}</td>
                                    <td>
                                        <strong>L {{ number_format($item->total_vendido, 2) }}</strong>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">
                                        No hay productos vendidos hoy.
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
                    <h3 class="card-title">
                        Servicios más vendidos hoy
                    </h3>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Código</th>
                                <th>Servicio</th>
                                <th>Cant.</th>
                                <th>Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($serviciosMasVendidosHoy as $item)
                                <tr>
                                    <td>{{ $item->codigo }}</td>
                                    <td>{{ $item->descripcion }}</td>
                                    <td>{{ number_format($item->cantidad_total, 2) }}</td>
                                    <td>
                                        <strong>L {{ number_format($item->total_vendido, 2) }}</strong>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">
                                        No hay servicios vendidos hoy.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>