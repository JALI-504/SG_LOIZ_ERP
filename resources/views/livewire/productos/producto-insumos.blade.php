<div>
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('message') }}
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
        <div class="card-header bg-info">
            <h3 class="card-title">
                Receta de insumos del producto
            </h3>
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

            @if (!$producto->usa_receta)
                <div class="alert alert-warning mt-3">
                    Este producto actualmente está marcado como <strong>no usa receta</strong>.
                    Puedes volver al módulo de productos, editarlo y cambiar
                    <strong>Usa receta</strong> a <strong>Sí</strong>.
                </div>
            @endif
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h4>L {{ number_format($costoTotal, 2) }}</h4>
                    <p>Costo calculado</p>
                </div>
                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>L {{ number_format($precioVenta, 2) }}</h4>
                    <p>Precio de venta</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tag"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box {{ $utilidad >= 0 ? 'bg-warning' : 'bg-danger' }}">
                <div class="inner">
                    <h4>L {{ number_format($utilidad, 2) }}</h4>
                    <p>Utilidad estimada</p>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box {{ $margen >= 30 ? 'bg-success' : 'bg-secondary' }}">
                <div class="inner">
                    <h4>{{ number_format($margen, 2) }}%</h4>
                    <p>Margen sobre venta</p>
                </div>
                <div class="icon">
                    <i class="fas fa-percent"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">{{ $modalTitle }}</h3>
        </div>

        <div class="card-body">
            <form wire:submit.prevent="{{ $receta_id ? 'update' : 'store' }}">
                <div class="row">
                    <div class="col-md-4">
                        <label>Buscar insumo</label>
                        <input type="text"
                               class="form-control"
                               placeholder="Buscar por código, nombre o categoría..."
                               wire:model.debounce.500ms="search">
                    </div>

                    <div class="col-md-3">
                        <label>Filtrar categoría</label>
                        <select class="form-control" wire:model="filtroCategoria">
                            <option value="todas">Todas las categorías</option>
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria }}">{{ $categoria }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label>Insumo <span class="text-danger">*</span></label>
                        <select class="form-control" wire:model.defer="insumo_id">
                            <option value="">Seleccione un insumo</option>
                            @foreach ($insumos as $insumo)
                                <option value="{{ $insumo->id }}">
                                    {{ $insumo->codigo }} - {{ $insumo->nombre }}
                                    | L {{ number_format($insumo->costo_unitario_real, 4) }}
                                    / {{ $insumo->unidad_consumo }}
                                </option>
                            @endforeach
                        </select>

                        @error('insumo_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="col-md-2">
                        <label>Cantidad <span class="text-danger">*</span></label>
                        <input type="number"
                               step="0.01"
                               min="0.01"
                               class="form-control"
                               wire:model.defer="cantidad_por_unidad">

                        @error('cantidad_por_unidad')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary btn-sm">
                        @if ($receta_id)
                            Actualizar insumo
                        @else
                            Agregar insumo
                        @endif
                    </button>

                    @if ($receta_id)
                        <button type="button"
                                class="btn btn-secondary btn-sm"
                                wire:click="cancelar">
                            Cancelar edición
                        </button>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Insumos asignados al producto</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Código</th>
                            <th>Insumo</th>
                            <th>Categoría</th>
                            <th>Unidad consumo</th>
                            <th>Costo unitario real</th>
                            <th>Cantidad por producto</th>
                            <th>Subtotal</th>
                            <th width="130">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($recetas as $receta)
                            <tr>
                                <td>
                                    <strong>{{ $receta->insumo->codigo }}</strong>
                                </td>

                                <td>
                                    {{ $receta->insumo->nombre }}
                                </td>

                                <td>
                                    <span class="badge badge-info">
                                        {{ $receta->insumo->categoria }}
                                    </span>
                                </td>

                                <td>
                                    {{ $receta->insumo->unidad_consumo }}
                                </td>

                                <td>
                                    L {{ number_format($receta->insumo->costo_unitario_real, 4) }}
                                </td>

                                <td>
                                    {{ number_format($receta->cantidad_por_unidad, 2) }}
                                </td>

                                <td>
                                    <strong>
                                        L {{ number_format($receta->cantidad_por_unidad * $receta->insumo->costo_unitario_real, 2) }}
                                    </strong>
                                </td>

                                <td>
                                    <button class="btn btn-warning btn-xs"
                                            wire:click="edit({{ $receta->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button class="btn btn-danger btn-xs"
                                            onclick="confirm('¿Eliminar este insumo de la receta?') || event.stopImmediatePropagation()"
                                            wire:click="delete({{ $receta->id }})">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    Este producto todavía no tiene insumos asignados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-right">Costo total:</th>
                            <th colspan="2">
                                L {{ number_format($costoTotal, 2) }}
                            </th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="alert alert-info mt-3">
                Este costo se calcula usando el <strong>costo unitario real</strong> de cada insumo.
                Al agregar, editar o eliminar insumos, el campo
                <strong>costo unitario</strong> del producto se actualiza automáticamente.
            </div>
        </div>
    </div>
</div>