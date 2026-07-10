<div>
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Servicio: <strong>{{ $servicio->nombre }}</strong>
            </h3>

            <div class="card-tools">
                <a href="{{ route('servicios.index') }}" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Volver a servicios
                </a>
            </div>
        </div>

        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h4>L {{ number_format($costoTotal, 2) }}</h4>
                            <p>Costo total insumos</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-boxes"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="small-box bg-primary">
                        <div class="inner">
                            <h4>L {{ number_format($precioVenta, 2) }}</h4>
                            <p>Precio de venta</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-tags"></i>
                        </div>
                    </div>
                </div>

                <div class="col-md-3">
                    <div class="small-box bg-success">
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
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h4>{{ number_format($margen, 2) }}%</h4>
                            <p>Margen sobre venta</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-info">
                Aquí puedes definir qué insumos consume este servicio por cada unidad vendida.
                Ejemplo: una impresión carta consume <strong>1 hoja</strong> de papel bond carta.
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="card card-primary">
                        <div class="card-header">
                            <h3 class="card-title">{{ $modalTitle }}</h3>
                        </div>

                        <div class="card-body">
                            <div class="form-group">
                                <label>Buscar insumo</label>
                                <input type="text"
                                       class="form-control"
                                       placeholder="Buscar por código, nombre o categoría..."
                                       wire:model.debounce.500ms="search">
                            </div>

                            <div class="form-group">
                                <label>Filtrar categoría</label>
                                <select class="form-control" wire:model="filtroCategoria">
                                    <option value="todas">Todas las categorías</option>
                                    @foreach ($categorias as $categoria)
                                        <option value="{{ $categoria }}">{{ $categoria }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="form-group">
                                <label>Insumo <span class="text-danger">*</span></label>
                                <select class="form-control" wire:model.defer="insumo_id">
                                    <option value="">Seleccione un insumo</option>
                                    @foreach ($insumos as $insumo)
                                        <option value="{{ $insumo->id }}">
                                            {{ $insumo->codigo }} - {{ $insumo->nombre }}
                                            / L {{ number_format($insumo->costo_unitario_real, 4) }}
                                            por {{ $insumo->unidad_consumo }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('insumo_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Cantidad por unidad <span class="text-danger">*</span></label>
                                <input type="number"
                                       step="0.01"
                                       min="0"
                                       class="form-control"
                                       wire:model.defer="cantidad_por_unidad">

                                @error('cantidad_por_unidad')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror

                                <small class="text-muted">
                                    Ejemplo: 1 hoja, 0.50 ml, 20 cm2.
                                </small>
                            </div>
                        </div>

                        <div class="card-footer">
                            @if ($receta_id)
                                <button class="btn btn-primary btn-sm" wire:click="update">
                                    <i class="fas fa-save"></i> Actualizar
                                </button>

                                <button class="btn btn-secondary btn-sm" wire:click="cancelar">
                                    Cancelar
                                </button>
                            @else
                                <button class="btn btn-primary btn-sm" wire:click="store">
                                    <i class="fas fa-plus"></i> Agregar insumo
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">Insumos asignados al servicio</h3>
                        </div>

                        <div class="card-body table-responsive">
                            <table class="table table-bordered table-hover table-sm">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Código</th>
                                        <th>Insumo</th>
                                        <th>Categoría</th>
                                        <th>Cantidad</th>
                                        <th>Costo unitario</th>
                                        <th>Subtotal</th>
                                        <th width="120">Acciones</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse ($recetas as $receta)
                                        <tr>
                                            <td>
                                                <strong>{{ $receta->insumo->codigo }}</strong>
                                            </td>

                                            <td>
                                                {{ $receta->insumo->nombre }} <br>
                                                <small class="text-muted">
                                                    Consumo en {{ $receta->insumo->unidad_consumo }}
                                                </small>
                                            </td>

                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $receta->insumo->categoria }}
                                                </span>
                                            </td>

                                            <td>
                                                {{ number_format($receta->cantidad_por_unidad, 2) }}
                                                {{ $receta->insumo->unidad_consumo }}
                                            </td>

                                            <td>
                                                L {{ number_format($receta->insumo->costo_unitario_real, 4) }}
                                            </td>

                                            <td>
                                                @php
                                                    $subtotal = $receta->cantidad_por_unidad * $receta->insumo->costo_unitario_real;
                                                @endphp

                                                <strong>
                                                    L {{ number_format($subtotal, 2) }}
                                                </strong>
                                            </td>

                                            <td>
                                                <button class="btn btn-warning btn-xs"
                                                        wire:click="edit({{ $receta->id }})">
                                                    <i class="fas fa-edit"></i>
                                                </button>

                                                <button class="btn btn-danger btn-xs"
                                                        onclick="confirm('¿Desea eliminar este insumo del servicio?') || event.stopImmediatePropagation()"
                                                        wire:click="delete({{ $receta->id }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">
                                                Este servicio todavía no tiene insumos asignados.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>

                                <tfoot>
                                    <tr>
                                        <th colspan="5" class="text-right">Costo total:</th>
                                        <th colspan="2">
                                            L {{ number_format($costoTotal, 2) }}
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>