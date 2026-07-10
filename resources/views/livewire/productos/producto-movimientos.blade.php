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
        <a href="{{ route('productos.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver a productos
        </a>
    </div>

    <div class="card">
        <div class="card-header bg-primary">
            <h3 class="card-title">Inventario del producto</h3>
        </div>

        <div class="card-body">
            <h5>{{ $producto->nombre }}</h5>

            <p class="mb-1">
                <strong>Código:</strong> {{ $producto->codigo }}
            </p>

            <p class="mb-1">
                <strong>Categoría:</strong> {{ $producto->categoria }}
                |
                <strong>Tipo:</strong> {{ $producto->tipo_producto }}
                |
                <strong>Unidad venta:</strong> {{ $producto->unidad_venta }}
            </p>

            <p class="mb-1">
                <strong>Stock actual:</strong>
                {{ number_format($producto->stock_actual, 2) }} {{ $producto->unidad_venta }}
                |
                <strong>Stock mínimo:</strong>
                {{ number_format($producto->stock_minimo, 2) }}
            </p>

            <p class="mb-0">
                <strong>Costo actual:</strong>
                L {{ number_format($producto->costo_unitario, 4) }}
                |
                <strong>Precio venta:</strong>
                L {{ number_format($producto->precio_venta, 2) }}
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ number_format($producto->stock_actual, 2) }}</h4>
                    <p>Stock actual</p>
                </div>
                <div class="icon">
                    <i class="fas fa-box"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box {{ $producto->stock_actual <= $producto->stock_minimo ? 'bg-danger' : 'bg-success' }}">
                <div class="inner">
                    <h4>{{ number_format($producto->stock_minimo, 2) }}</h4>
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
                    <h4>L {{ number_format($producto->costo_unitario, 4) }}</h4>
                    <p>Costo PEPS actual</p>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h4>L {{ number_format($producto->precio_venta, 2) }}</h4>
                    <p>Precio de venta</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tag"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Registrar movimiento --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Registrar movimiento</h3>
        </div>

        <div class="card-body">
            <form wire:submit.prevent="storeMovimiento">
                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Tipo de movimiento <span class="text-danger">*</span></label>

                        <select class="form-control" wire:model="movimiento_tipo">
                            @foreach ($tiposMovimiento as $tipo)
                                <option value="{{ $tipo }}">{{ $tipo }}</option>
                            @endforeach
                        </select>

                        @error('movimiento_tipo')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group col-md-2">
                        <label>Cantidad <span class="text-danger">*</span></label>

                        <input type="number"
                               step="0.01"
                               min="0.01"
                               class="form-control"
                               wire:model.defer="movimiento_cantidad">

                        @error('movimiento_cantidad')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label>Costo unitario</label>

                        <input type="number"
                               step="0.0001"
                               min="0"
                               class="form-control"
                               wire:model.defer="movimiento_costo_unitario"
                            {{ in_array($movimiento_tipo, ['Entrada produccion', 'Salida venta', 'Salida daño', 'Salida ajuste']) ? 'readonly' : '' }}
                        @error('movimiento_costo_unitario')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror

                        @if ($movimiento_tipo === 'Entrada produccion')
                            <small class="text-muted">
                                En producción el costo se calcula automáticamente usando la receta y PEPS de insumos.
                            </small>
                        @elseif (in_array($movimiento_tipo, ['Salida venta', 'Salida daño', 'Salida ajuste']))
                            <small class="text-muted">
                                En salidas el costo se calcula automáticamente por PEPS.
                            </small>
                        @else
                            <small class="text-muted">
                                En entradas este costo crea un nuevo lote.
                            </small>
                        @endif
                    </div>

                    <div class="form-group col-md-4">
                        <label>Referencia</label>

                        <input type="text"
                               class="form-control"
                               placeholder="Factura, recibo, orden, venta..."
                               wire:model.defer="movimiento_referencia">

                        @error('movimiento_referencia')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Observación</label>

                    <textarea class="form-control"
                              rows="2"
                              placeholder="Observación opcional..."
                              wire:model.defer="movimiento_observacion"></textarea>

                    @error('movimiento_observacion')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar movimiento
                </button>
            </form>
        </div>
    </div>

    {{-- Lotes disponibles --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lotes del producto</h3>
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
                            <th>Total</th>
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
                                        <small class="text-muted">{{ $lote->referencia }}</small>
                                    @endif
                                </td>

                                <td>
                                    {{ $lote->fecha_entrada }}
                                </td>

                                <td>
                                    {{ number_format($lote->cantidad_inicial, 2) }}
                                </td>

                                <td>
                                    <strong>{{ number_format($lote->cantidad_disponible, 2) }}</strong>
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
                                    Este producto todavía no tiene lotes registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="alert alert-info mt-3">
                Las salidas se descuentan por PEPS: primero se consume el lote más antiguo disponible.
            </div>
        </div>
    </div>

    {{-- Historial de movimientos --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Historial de movimientos</h3>
        </div>

        <div class="card-body">
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
                                    @if (in_array($movimiento->tipo_movimiento, ['Entrada compra', 'Entrada produccion', 'Entrada ajuste', 'Devolucion']))
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
                                    No hay movimientos registrados para este producto.
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