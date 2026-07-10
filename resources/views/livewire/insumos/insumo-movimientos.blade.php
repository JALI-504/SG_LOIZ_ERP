<div>
    <div class="mb-3">
        <a href="{{ route('insumos.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver a insumos
        </a>
    </div>

    <div class="card">
        <div class="card-header bg-primary">
            <h3 class="card-title">Kardex / Historial del insumo</h3>
        </div>

        <div class="card-body">
            <h5>{{ $insumo->nombre }}</h5>

            <p class="mb-1">
                <strong>Código:</strong> {{ $insumo->codigo }}
            </p>

            <p class="mb-1">
                <strong>Categoría:</strong> {{ $insumo->categoria }}
                |
                <strong>Unidad consumo:</strong> {{ $insumo->unidad_consumo }}
            </p>

            <p class="mb-1">
                <strong>Stock actual:</strong>
                {{ number_format($insumo->stock_actual, 2) }} {{ $insumo->unidad_consumo }}
                |
                <strong>Stock mínimo:</strong>
                {{ number_format($insumo->stock_minimo, 2) }} {{ $insumo->unidad_consumo }}
            </p>

            <p class="mb-0">
                <strong>Costo base actual:</strong>
                L {{ number_format($insumo->costo_unitario_base, 4) }}
                |
                <strong>Costo real actual:</strong>
                L {{ number_format($insumo->costo_unitario_real, 4) }}
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ number_format($insumo->stock_actual, 2) }}</h4>
                    <p>Stock actual</p>
                </div>
                <div class="icon">
                    <i class="fas fa-box"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box {{ $insumo->stock_actual <= $insumo->stock_minimo ? 'bg-danger' : 'bg-success' }}">
                <div class="inner">
                    <h4>{{ number_format($insumo->stock_minimo, 2) }}</h4>
                    <p>Stock mínimo</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>L {{ number_format($valorInventario, 2) }}</h4>
                    <p>Valor inventario</p>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h4>{{ number_format($totalDisponibleLotes, 2) }}</h4>
                    <p>Disponible en lotes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-layer-group"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Lotes --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lotes PEPS del insumo</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Lote</th>
                            <th>Fecha entrada</th>
                            <th>Cantidad inicial</th>
                            <th>Cantidad disponible</th>
                            <th>Costo unitario</th>
                            <th>Total inicial</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($lotes as $lote)
                            <tr>
                                <td>
                                    <strong>{{ $lote->codigo_lote }}</strong>

                                    @if ($lote->referencia)
                                        <br>
                                        <small class="text-muted">
                                            {{ $lote->referencia }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    {{ $lote->fecha_entrada }}
                                </td>

                                <td>
                                    {{ number_format($lote->cantidad_inicial, 2) }}
                                    {{ $insumo->unidad_consumo }}
                                </td>

                                <td>
                                    <strong>
                                        {{ number_format($lote->cantidad_disponible, 2) }}
                                        {{ $insumo->unidad_consumo }}
                                    </strong>
                                </td>

                                <td>
                                    L {{ number_format($lote->costo_unitario, 4) }}
                                </td>

                                <td>
                                    L {{ number_format($lote->total, 2) }}
                                </td>

                                <td>
                                    @if ($lote->activo)
                                        <span class="badge badge-success">Disponible</span>
                                    @else
                                        <span class="badge badge-secondary">Agotado</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    Este insumo todavía no tiene lotes registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="alert alert-info mt-3">
                PEPS significa que las salidas consumen primero los lotes más antiguos disponibles.
            </div>
        </div>
    </div>

    {{-- Historial --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Historial de movimientos</h3>
        </div>

        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-3">
                    <select class="form-control" wire:model="filtroTipo">
                        <option value="todos">Todos los movimientos</option>
                        <option value="entradas">Solo entradas</option>
                        <option value="salidas">Solo salidas</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <select class="form-control" wire:model="perPage">
                        <option value="10">10 registros</option>
                        <option value="25">25 registros</option>
                        <option value="50">50 registros</option>
                        <option value="100">100 registros</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Cantidad</th>
                            <th>Costo unitario</th>
                            <th>Total</th>
                            <th>Referencia</th>
                            <th>Detalle PEPS</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($movimientos as $movimiento)
                            <tr>
                                <td>
                                    {{ $movimiento->created_at->format('d/m/Y H:i') }}
                                </td>

                                <td>
                                    @if (in_array($movimiento->tipo_movimiento, $tiposEntrada))
                                        <span class="badge badge-success">
                                            {{ $movimiento->tipo_movimiento }}
                                        </span>
                                    @else
                                        <span class="badge badge-danger">
                                            {{ $movimiento->tipo_movimiento }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ number_format($movimiento->cantidad, 2) }}
                                    {{ $insumo->unidad_consumo }}
                                </td>

                                <td>
                                    L {{ number_format($movimiento->costo_unitario, 4) }}
                                </td>

                                <td>
                                    L {{ number_format($movimiento->total, 2) }}
                                </td>

                                <td>
                                    {{ $movimiento->referencia ?? 'Sin referencia' }}

                                    @if ($movimiento->observacion)
                                        <br>
                                        <small class="text-muted">
                                            {{ $movimiento->observacion }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    @forelse ($movimiento->detalleLotes as $detalle)
                                        <small>
                                            Lote:
                                            <strong>{{ $detalle->lote->codigo_lote ?? 'N/D' }}</strong>
                                            |
                                            Cant:
                                            {{ number_format($detalle->cantidad, 2) }}
                                            |
                                            Costo:
                                            L {{ number_format($detalle->costo_unitario, 4) }}
                                            |
                                            Total:
                                            L {{ number_format($detalle->total, 2) }}
                                        </small>
                                        <br>
                                    @empty
                                        <small class="text-muted">Sin detalle</small>
                                    @endforelse
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    No hay movimientos registrados para este insumo.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $movimientos->links() }}
        </div>
    </div>
</div>