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
            <h3 class="card-title">Listado de catálogos</h3>

            <div class="card-tools">
                <button class="btn btn-primary btn-sm" wire:click="create">
                    <i class="fas fa-plus"></i> Nuevo catálogo
                </button>
            </div>
        </div>

        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text"
                           class="form-control"
                           placeholder="Buscar por tipo, nombre o descripción..."
                           wire:model.debounce.500ms="search">
                </div>

                <div class="col-md-3">
                    <select class="form-control" wire:model="filtroTipo">
                        <option value="todos">Todos los tipos</option>
                        @foreach ($tiposCatalogo as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
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
                            <th>Tipo</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Orden</th>
                            <th>Estado</th>
                            <th width="170">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($catalogos as $catalogo)
                            <tr>
                                <td>
                                    <strong>
                                        {{ $tiposCatalogo[$catalogo->tipo] ?? $catalogo->tipo }}
                                    </strong>
                                    <br>
                                    <small class="text-muted">{{ $catalogo->tipo }}</small>
                                </td>

                                <td>{{ $catalogo->nombre }}</td>

                                <td>
                                    {{ $catalogo->descripcion ?? 'Sin descripción' }}
                                </td>

                                <td>{{ $catalogo->orden }}</td>

                                <td>
                                    @if ($catalogo->activo)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-secondary">Inactivo</span>
                                    @endif
                                </td>

                                <td>
                                    <button class="btn btn-warning btn-xs"
                                            wire:click="edit({{ $catalogo->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button class="btn btn-{{ $catalogo->activo ? 'secondary' : 'success' }} btn-xs"
                                            wire:click="cambiarEstado({{ $catalogo->id }})">
                                        @if ($catalogo->activo)
                                            Desactivar
                                        @else
                                            Activar
                                        @endif
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">
                                    No hay catálogos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $catalogos->links() }}
        </div>
    </div>

    {{-- Modal catálogo --}}
    <div wire:ignore.self class="modal fade" id="catalogoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <form wire:submit.prevent="{{ $catalogo_id ? 'update' : 'store' }}" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $modalTitle }}</h5>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Tipo de catálogo <span class="text-danger">*</span></label>
                            <select class="form-control" wire:model.defer="tipo">
                                @foreach ($tiposCatalogo as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>

                            @error('tipo')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>Nombre <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   placeholder="Ej: Vinil, Foamy, Cerámica..."
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

                    <div class="alert alert-info">
                        Ejemplo: si necesitas agregar una nueva categoría de insumo como
                        <strong>Vinil</strong>, selecciona el tipo
                        <strong>Categoría de insumo</strong> y escribe el nombre.
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-primary">
                        {{ $catalogo_id ? 'Actualizar catálogo' : 'Guardar catálogo' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>