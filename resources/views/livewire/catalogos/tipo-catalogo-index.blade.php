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
            <h3 class="card-title">Listado de tipos de catálogo</h3>

            <div class="card-tools">
                <button class="btn btn-primary btn-sm" wire:click="create">
                    <i class="fas fa-plus"></i> Nuevo tipo
                </button>
            </div>
        </div>

        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-5">
                    <input type="text"
                           class="form-control"
                           placeholder="Buscar por código, nombre o descripción..."
                           wire:model.debounce.500ms="search">
                </div>

                <div class="col-md-3">
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
                            <th>Nombre visible</th>
                            <th>Descripción</th>
                            <th>Orden</th>
                            <th>Opciones</th>
                            <th>Estado</th>
                            <th width="170">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($tiposCatalogo as $tipoCatalogo)
                            <tr>
                                <td>
                                    <strong>{{ $tipoCatalogo->codigo }}</strong>
                                </td>

                                <td>
                                    {{ $tipoCatalogo->nombre }}
                                </td>

                                <td>
                                    {{ $tipoCatalogo->descripcion ?? 'Sin descripción' }}
                                </td>

                                <td>
                                    {{ $tipoCatalogo->orden }}
                                </td>

                                <td>
                                    <span class="badge badge-info">
                                        {{ $tipoCatalogo->catalogos_count }} opciones
                                    </span>
                                </td>

                                <td>
                                    @if ($tipoCatalogo->activo)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-secondary">Inactivo</span>
                                    @endif
                                </td>

                                <td>
                                    <button class="btn btn-warning btn-xs"
                                            wire:click="edit({{ $tipoCatalogo->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button class="btn btn-{{ $tipoCatalogo->activo ? 'secondary' : 'success' }} btn-xs"
                                            wire:click="cambiarEstado({{ $tipoCatalogo->id }})">
                                        @if ($tipoCatalogo->activo)
                                            Desactivar
                                        @else
                                            Activar
                                        @endif
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    No hay tipos de catálogo registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $tiposCatalogo->links() }}
        </div>
    </div>

    {{-- Modal tipo catálogo --}}
    <div wire:ignore.self class="modal fade" id="tipoCatalogoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <form wire:submit.prevent="{{ $tipo_catalogo_id ? 'update' : 'store' }}" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $modalTitle }}</h5>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="alert alert-info">
                        Los tipos de catálogo son los grupos principales. Por ejemplo:
                        <strong>Categoría de insumo</strong>, <strong>Unidad de compra</strong>,
                        <strong>Tipo de servicio</strong> o <strong>Método de pago</strong>.
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Código interno <span class="text-danger">*</span></label>

                            <input type="text"
                                   class="form-control"
                                   placeholder="Ej: marca_insumo"
                                   wire:model.defer="codigo"
                                   {{ $tipo_catalogo_id ? 'readonly' : '' }}>

                            @error('codigo')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror

                            @if ($tipo_catalogo_id)
                                <small class="text-muted">
                                    El código no se puede editar para no romper los catálogos asociados.
                                </small>
                            @else
                                <small class="text-muted">
                                    Use minúsculas, números y guion bajo. Ejemplo: metodo_pago.
                                </small>
                            @endif
                        </div>

                        <div class="form-group col-md-6">
                            <label>Nombre visible <span class="text-danger">*</span></label>

                            <input type="text"
                                   class="form-control"
                                   placeholder="Ej: Marca de insumo"
                                   wire:model.defer="nombre">

                            @error('nombre')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Orden</label>

                            <input type="number"
                                   min="0"
                                   class="form-control"
                                   wire:model.defer="orden">

                            @error('orden')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-4">
                            <label>Estado</label>

                            <select class="form-control" wire:model.defer="activo">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>

                            @error('activo')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Descripción</label>

                        <textarea class="form-control"
                                  rows="2"
                                  placeholder="Descripción opcional..."
                                  wire:model.defer="descripcion"></textarea>

                        @error('descripcion')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="alert alert-warning">
                        Después de crear un tipo de catálogo, podrás ir al módulo
                        <strong>Catálogos</strong> y agregar opciones dentro de ese tipo.
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-primary">
                        {{ $tipo_catalogo_id ? 'Actualizar tipo' : 'Guardar tipo' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>