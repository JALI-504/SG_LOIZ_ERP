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
            <h3 class="card-title">Listado de insumos</h3>

            <div class="card-tools">
                <button class="btn btn-primary btn-sm" wire:click="create">
                    <i class="fas fa-plus"></i> Nuevo insumo
                </button>
            </div>
        </div>

        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-5">
                    <input type="text"
                           class="form-control"
                           placeholder="Buscar por código, nombre, categoría o unidad..."
                           wire:model.debounce.500ms="search">
                </div>

                <div class="col-md-3">
                    <select class="form-control" wire:model="filtroCategoria">
                        <option value="todas">Todas las categorías</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria }}">{{ $categoria }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select class="form-control" wire:model="filtroEstado">
                        <option value="activos">Solo activos</option>
                        <option value="inactivos">Solo inactivos</option>
                        <option value="todos">Todos</option>
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
                            <th>Código</th>
                            <th>Insumo</th>
                            <th>Categoría</th>
                            <th>Compra / Consumo</th>
                            <th>Costo</th>
                            <th>Stock</th>
                            <th>Estado</th>
                            <th width="230">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($insumos as $insumo)
                            <tr>
                                <td>
                                    <strong>{{ $insumo->codigo }}</strong>
                                </td>

                                <td>
                                    <strong>{{ $insumo->nombre }}</strong><br>

                                    @if ($insumo->descripcion)
                                        <small>{{ $insumo->descripcion }}</small><br>
                                    @endif

                                    @if ($insumo->ancho_cm || $insumo->largo_cm || $insumo->espesor_mm)
                                        <small>
                                            Medidas:
                                            {{ $insumo->ancho_cm ? number_format($insumo->ancho_cm, 2) . ' cm' : '' }}
                                            {{ $insumo->largo_cm ? ' x ' . number_format($insumo->largo_cm, 2) . ' cm' : '' }}
                                            {{ $insumo->espesor_mm ? ' / ' . number_format($insumo->espesor_mm, 2) . ' mm' : '' }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge badge-info">
                                        {{ $insumo->categoria }}
                                    </span>
                                </td>

                                <td>
                                    <small>
                                        Compra: {{ $insumo->unidad_compra }} <br>
                                        {{ number_format($insumo->cantidad_por_compra, 2) }}
                                        Consumo: {{ $insumo->unidad_consumo }}
                                    </small>
                                </td>

                                <td>
                                    <small>
                                        Compra: <strong>L {{ number_format($insumo->costo_compra, 2) }}</strong><br>
                                        Base: L {{ number_format($insumo->costo_unitario_base, 2) }}<br>
                                        Merma: {{ number_format($insumo->porcentaje_merma, 2) }}%<br>
                                        Real: <strong>L {{ number_format($insumo->costo_unitario_real, 2) }}</strong>
                                    </small>
                                </td>

                                <td>
                                    @if ($insumo->stock_bajo)
                                        <span class="badge badge-danger">Stock bajo</span><br>
                                    @else
                                        <span class="badge badge-success">Disponible</span><br>
                                    @endif

                                    <strong>
                                        {{ number_format($insumo->stock_actual, 2) }}
                                        {{ $insumo->unidad_consumo }}
                                    </strong><br>

                                    <small>
                                        Mínimo:
                                        {{ number_format($insumo->stock_minimo, 2) }}
                                        {{ $insumo->unidad_consumo }}
                                    </small>
                                </td>

                                <td>
                                    @if ($insumo->activo)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-secondary">Inactivo</span>
                                    @endif
                                </td>

                                <td>
                                    <button class="btn btn-warning btn-xs"
                                            wire:click="edit({{ $insumo->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button class="btn btn-info btn-xs"
                                            wire:click="abrirMovimiento({{ $insumo->id }})">
                                        Movimiento
                                    </button>

                                    <button class="btn btn-{{ $insumo->activo ? 'secondary' : 'success' }} btn-xs"
                                            wire:click="cambiarEstado({{ $insumo->id }})">
                                        @if ($insumo->activo)
                                            Desactivar
                                        @else
                                            Activar
                                        @endif
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    No hay insumos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $insumos->links() }}
        </div>
    </div>

    {{-- Modal insumo --}}
    <div wire:ignore.self class="modal fade" id="insumoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <form wire:submit.prevent="{{ $insumo_id ? 'update' : 'store' }}" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $modalTitle }}</h5>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <h5>Datos del insumo</h5>
                    <hr>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Código <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   placeholder="Ej: PAP-CAR-BOND"
                                   wire:model.defer="codigo">
                            @error('codigo') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   placeholder="Ej: Papel bond carta"
                                   wire:model.defer="nombre">
                            @error('nombre') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-3">
                            <label>Categoría <span class="text-danger">*</span></label>
                            <select class="form-control" wire:model.defer="categoria">
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria }}">{{ $categoria }}</option>
                                @endforeach
                            </select>
                            @error('categoria') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <h5 class="mt-3">Compra y consumo</h5>
                    <hr>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Unidad de compra <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   placeholder="Resma, Pliego, Paquete..."
                                   wire:model.defer="unidad_compra">
                            @error('unidad_compra') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-3">
                            <label>Cantidad por compra <span class="text-danger">*</span></label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model="cantidad_por_compra">
                            @error('cantidad_por_compra') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-3">
                            <label>Unidad de consumo <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   placeholder="Hoja, cm2, unidad, ml..."
                                   wire:model.defer="unidad_consumo">
                            @error('unidad_consumo') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-3">
                            <label>Estado</label>
                            <select class="form-control" wire:model.defer="activo">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                            @error('activo') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <h5 class="mt-3">Medidas opcionales</h5>
                    <hr>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Ancho en cm</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model.defer="ancho_cm">
                            @error('ancho_cm') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-4">
                            <label>Largo en cm</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model.defer="largo_cm">
                            @error('largo_cm') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-4">
                            <label>Espesor en mm</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model.defer="espesor_mm">
                            @error('espesor_mm') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <h5 class="mt-3">Costos e inventario</h5>
                    <hr>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Costo compra <span class="text-danger">*</span></label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model="costo_compra">
                            @error('costo_compra') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-3">
                            <label>% Merma</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   max="99.99"
                                   class="form-control"
                                   wire:model="porcentaje_merma">
                            @error('porcentaje_merma') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-3">
                            <label>Costo unitario base</label>
                            <input type="number"
                                   step="0.0001"
                                   class="form-control"
                                   wire:model="costo_unitario_base"
                                   readonly>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Costo unitario real</label>
                            <input type="number"
                                   step="0.0001"
                                   class="form-control"
                                   wire:model="costo_unitario_real"
                                   readonly>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Stock actual <span class="text-danger">*</span></label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model.defer="stock_actual">
                            @error('stock_actual') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>Stock mínimo <span class="text-danger">*</span></label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model.defer="stock_minimo">
                            @error('stock_minimo') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Descripción / observación</label>
                        <textarea class="form-control"
                                  rows="2"
                                  wire:model.defer="descripcion"></textarea>
                        @error('descripcion') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-primary">
                        {{ $insumo_id ? 'Actualizar insumo' : 'Guardar insumo' }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal movimiento --}}
    <div wire:ignore.self class="modal fade" id="movimientoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <form wire:submit.prevent="storeMovimiento" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Registrar movimiento de inventario</h5>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="alert alert-info">
                        Use este formulario para registrar entradas de compra, salidas por daño, pruebas o ajustes de inventario.
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Tipo de movimiento <span class="text-danger">*</span></label>
                            <select class="form-control" wire:model.defer="movimiento_tipo">
                                @foreach ($tiposMovimiento as $tipo)
                                    <option value="{{ $tipo }}">{{ $tipo }}</option>
                                @endforeach
                            </select>
                            @error('movimiento_tipo') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>Cantidad <span class="text-danger">*</span></label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model="movimiento_cantidad">
                            @error('movimiento_cantidad') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Costo unitario</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model="movimiento_costo_unitario">
                            @error('movimiento_costo_unitario') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>Total</label>
                            <input type="number"
                                   step="0.01"
                                   class="form-control"
                                   wire:model="movimiento_total"
                                   readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Referencia</label>
                        <input type="text"
                               class="form-control"
                               placeholder="Ej: Compra factura #001, Venta #0001, Prueba láser..."
                               wire:model.defer="movimiento_referencia">
                        @error('movimiento_referencia') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label>Observación</label>
                        <textarea class="form-control"
                                  rows="2"
                                  placeholder="Ej: 3 hojas dañadas por atasco de impresora..."
                                  wire:model.defer="movimiento_observacion"></textarea>
                        @error('movimiento_observacion') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-primary">
                        Guardar movimiento
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>