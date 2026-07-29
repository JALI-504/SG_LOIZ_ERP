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

    @can('crear produccion')
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Registrar producción</h3>
            </div>

            <form wire:submit.prevent="registrarProduccion">
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-5">
                            <label>Producto a producir <span class="text-danger">*</span></label>
                            <select class="form-control" wire:model="producto_id">
                                <option value="">Seleccione...</option>

                                @foreach ($productos as $producto)
                                    <option value="{{ $producto->id }}">
                                        {{ $producto->codigo }} - {{ $producto->nombre }}
                                    </option>
                                @endforeach
                            </select>

                            @error('producto_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-2">
                            <label>Cantidad <span class="text-danger">*</span></label>
                            <input type="number"
                                   step="0.01"
                                   min="0.01"
                                   class="form-control"
                                   wire:model="cantidad">

                            @error('cantidad')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-2">
                            <label>Fecha <span class="text-danger">*</span></label>
                            <input type="date"
                                   class="form-control"
                                   wire:model.defer="fecha">

                            @error('fecha')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-3">
                            <label>Observación</label>
                            <input type="text"
                                   class="form-control"
                                   placeholder="Opcional"
                                   wire:model.defer="observacion">

                            @error('observacion')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    @if (count($recetaCalculada) > 0)
                        <div class="table-responsive mt-3">
                            <h5>Insumos requeridos</h5>

                            <table class="table table-bordered table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Insumo</th>
                                        <th>Por unidad</th>
                                        <th>Cantidad necesaria</th>
                                        <th>Stock disponible</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($recetaCalculada as $item)
                                        <tr>
                                            <td>{{ $item['insumo'] }}</td>
                                            <td>
                                                {{ number_format($item['cantidad_por_unidad'], 4) }}
                                                {{ $item['unidad'] }}
                                            </td>
                                            <td>
                                                {{ number_format($item['cantidad_necesaria'], 4) }}
                                                {{ $item['unidad'] }}
                                            </td>
                                            <td>
                                                {{ number_format($item['stock_disponible'], 4) }}
                                                {{ $item['unidad'] }}
                                            </td>
                                            <td>
                                                @if ($item['suficiente'])
                                                    <span class="badge badge-success">Disponible</span>
                                                @else
                                                    <span class="badge badge-danger">Insuficiente</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div class="card-footer">
                    <button type="submit"
                            class="btn btn-primary"
                            wire:loading.attr="disabled">
                        <i class="fas fa-industry"></i> Registrar producción
                    </button>

                    <span wire:loading class="text-info ml-2">
                        Procesando producción...
                    </span>
                </div>
            </form>
        </div>
    @endcan

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Historial de producción</h3>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-5">
                    <input type="text"
                           class="form-control"
                           placeholder="Buscar por código o producto..."
                           wire:model.debounce.500ms="search">
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
                            <th>Código</th>
                            <th>Fecha</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Costo unitario</th>
                            <th>Costo total</th>
                            <th>Estado</th>
                            <th>Usuario</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($producciones as $produccion)
                            <tr>
                                <td>
                                    <strong>{{ $produccion->codigo }}</strong>
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($produccion->fecha)->format('d/m/Y') }}
                                </td>

                                <td>
                                    {{ $produccion->producto->nombre ?? '' }}

                                    <div class="small text-muted">
                                        {{ $produccion->insumos->count() }} insumos usados
                                    </div>
                                </td>

                                <td>
                                    {{ number_format($produccion->cantidad, 2) }}
                                </td>

                                <td>
                                    L {{ number_format($produccion->costo_unitario, 4) }}
                                </td>

                                <td>
                                    <strong>L {{ number_format($produccion->costo_total, 2) }}</strong>
                                </td>

                                <td>
                                    @if ($produccion->estado === 'Registrada')
                                        <span class="badge badge-success">Registrada</span>
                                    @else
                                        <span class="badge badge-danger">{{ $produccion->estado }}</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $produccion->usuario->name ?? 'Sistema' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    No hay producciones registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $producciones->links() }}
        </div>
    </div>
</div>