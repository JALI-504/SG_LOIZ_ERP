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
        @can('crear categorias activos')
            <button type="button"
                    class="btn btn-success btn-sm"
                    wire:click="crear">
                <i class="fas fa-plus"></i>
                Nueva categoría
            </button>
        @endcan

        <a href="{{ route('activos-fijos.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-laptop-house"></i>
            Ver activos fijos
        </a>
    </div>

    {{-- Resumen --}}
    <div class="row">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ number_format($totalCategorias, 0) }}</h4>
                    <p>Total categorías</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>{{ number_format($totalActivas, 0) }}</h4>
                    <p>Categorías activas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h4>{{ number_format($totalInactivas, 0) }}</h4>
                    <p>Categorías inactivas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-ban"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros</h3>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <label>Buscar</label>
                    <input type="text"
                           class="form-control"
                           placeholder="Código, nombre, descripción..."
                           wire:model.debounce.500ms="search">
                </div>

                <div class="col-md-3">
                    <label>Estado</label>
                    <select class="form-control" wire:model="filtroEstado">
                        <option value="todos">Todos</option>
                        <option value="activas">Activas</option>
                        <option value="inactivas">Inactivas</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Mostrar</label>
                    <select class="form-control" wire:model="perPage">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de categorías de activos</h3>
        </div>

        <div class="card-body">
            <div class="alert alert-info">
                Las categorías definen la vida útil y el método de depreciación predeterminado para los activos fijos.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Código</th>
                            <th>Prefijo</th>
                            <th>Nombre</th>
                            <th>Depreciación</th>
                            <th>Vida útil</th>
                            <th>Requisitos</th>
                            <th>Estado</th>
                            <th width="170">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($categorias as $categoria)
                            <tr class="{{ !$categoria->activo ? 'table-secondary' : '' }}">
                                <td>
                                    <strong>{{ $categoria->codigo }}</strong>
                                </td>

                                <td>
                                    <span class="badge badge-primary">
                                        AF-{{ $categoria->prefijo_codigo ?? 'GEN' }}
                                    </span>
                                </td>

                                <td>
                                    <strong>{{ $categoria->nombre }}</strong>

                                    @if ($categoria->descripcion)
                                        <br>
                                        <small class="text-muted">
                                            {{ $categoria->descripcion }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    @if ($categoria->depreciable)
                                        <span class="badge badge-success">Depreciable</span>
                                        <br>
                                        <small>{{ $categoria->metodo_depreciacion }}</small>
                                    @else
                                        <span class="badge badge-secondary">No depreciable</span>
                                    @endif
                                </td>

                                <td>
                                    {{ number_format($categoria->vida_util_meses, 0) }} meses
                                </td>

                                <td>
                                    <small>
                                        @if ($categoria->requiere_numero_serie)
                                            <span class="badge badge-info">Serie</span>
                                        @endif

                                        @if ($categoria->requiere_marca_modelo)
                                            <span class="badge badge-primary">Marca/modelo</span>
                                        @endif

                                        @if ($categoria->requiere_responsable)
                                            <span class="badge badge-warning">Responsable</span>
                                        @endif

                                        @if (!$categoria->requiere_numero_serie && !$categoria->requiere_marca_modelo && !$categoria->requiere_responsable)
                                            <span class="text-muted">Sin requisitos especiales</span>
                                        @endif
                                    </small>

                                    <br>

                                    <small class="text-muted">
                                        Dep. anual: {{ number_format($categoria->porcentaje_depreciacion_anual, 2) }}%
                                    </small>
                                </td>

                                <td>
                                    @if ($categoria->activo)
                                        <span class="badge badge-success">Activa</span>
                                    @else
                                        <span class="badge badge-secondary">Inactiva</span>
                                    @endif
                                </td>

                                <td>
                                    @can('editar categorias activos')
                                        <button type="button"
                                                class="btn btn-primary btn-xs"
                                                wire:click="editar({{ $categoria->id }})">
                                            Editar
                                        </button>
                                    @endcan

                                    @can('eliminar categorias activos')
                                        <button type="button"
                                                class="btn {{ $categoria->activo ? 'btn-danger' : 'btn-warning' }} btn-xs"
                                                onclick="confirm('¿Desea cambiar el estado de esta categoría?') || event.stopImmediatePropagation()"
                                                wire:click="cambiarEstado({{ $categoria->id }})">
                                            {{ $categoria->activo ? 'Desactivar' : 'Activar' }}
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    No hay categorías registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $categorias->links() }}
        </div>
    </div>

    {{-- Modal --}}
    @if ($mostrarModal)
        <div class="modal fade show"
             style="display: block;"
             tabindex="-1"
             role="dialog">
            <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <form wire:submit.prevent="guardar">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                {{ $categoria_id ? 'Editar categoría de activo' : 'Nueva categoría de activo' }}
                            </h5>

                            <button type="button"
                                    class="close"
                                    wire:click="cerrarModal">
                                <span>&times;</span>
                            </button>
                        </div>

                        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                            <div class="form-group">
                                <label>Nombre <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control @error('nombre') is-invalid @enderror"
                                       wire:model.defer="nombre"
                                       placeholder="Ej: Computadoras, mobiliario, maquinaria...">

                                @error('nombre')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Prefijo de código <span class="text-danger">*</span></label>
                                <input type="text"
                                    maxlength="10"
                                    class="form-control @error('prefijo_codigo') is-invalid @enderror"
                                    wire:model.defer="prefijo_codigo"
                                    placeholder="Ej: COM, VEH, TER">

                                @error('prefijo_codigo')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror

                                <small class="text-muted">
                                    El código del activo se generará así: AF-PREFIJO-000001.
                                </small>
                            </div>

                            <div class="form-group">
                                <label>Descripción</label>
                                <textarea class="form-control @error('descripcion') is-invalid @enderror"
                                          rows="3"
                                          wire:model.defer="descripcion"></textarea>

                                @error('descripcion')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Depreciable</label>
                                    <select class="form-control"
                                            wire:model.defer="depreciable">
                                        <option value="1">Sí</option>
                                        <option value="0">No</option>
                                    </select>
                                </div>

                                <div class="form-group col-md-4">
                                    <label>Vida útil en meses <span class="text-danger">*</span></label>
                                    <input type="number"
                                           min="1"
                                           max="600"
                                           class="form-control @error('vida_util_meses') is-invalid @enderror"
                                           wire:model.defer="vida_util_meses">

                                    @error('vida_util_meses')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group col-md-4">
                                    <label>% depreciación anual</label>
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           max="100"
                                           class="form-control @error('porcentaje_depreciacion_anual') is-invalid @enderror"
                                           wire:model.defer="porcentaje_depreciacion_anual">

                                    @error('porcentaje_depreciacion_anual')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label>Método de depreciación</label>
                                    <select class="form-control @error('metodo_depreciacion') is-invalid @enderror"
                                            wire:model.defer="metodo_depreciacion">
                                        <option value="Linea recta">Línea recta</option>
                                    </select>

                                    @error('metodo_depreciacion')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group col-md-6">
                                    <label>Estado</label>
                                    <select class="form-control"
                                            wire:model.defer="activo">
                                        <option value="1">Activa</option>
                                        <option value="0">Inactiva</option>
                                    </select>
                                </div>

                                <div class="card card-outline card-info">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            Requisitos al registrar activos de esta categoría
                                        </h3>
                                    </div>

                                    <div class="card-body">
                                        <div class="form-row">
                                            <div class="form-group col-md-4">
                                                <label>¿Requiere número de serie?</label>
                                                <select class="form-control"
                                                        wire:model.defer="requiere_numero_serie">
                                                    <option value="1">Sí</option>
                                                    <option value="0">No</option>
                                                </select>
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label>¿Requiere marca y modelo?</label>
                                                <select class="form-control"
                                                        wire:model.defer="requiere_marca_modelo">
                                                    <option value="1">Sí</option>
                                                    <option value="0">No</option>
                                                </select>
                                            </div>

                                            <div class="form-group col-md-4">
                                                <label>¿Requiere responsable?</label>
                                                <select class="form-control"
                                                        wire:model.defer="requiere_responsable">
                                                    <option value="1">Sí</option>
                                                    <option value="0">No</option>
                                                </select>
                                            </div>
                                        </div>

                                        <small class="text-muted">
                                            Estos requisitos se usarán cuando registremos activos fijos. Por ejemplo, una computadora puede exigir número de serie, marca, modelo y responsable.
                                        </small>
                                    </div>
                                </div>
                            </div>

                            <div class="alert alert-secondary mb-0">
                                Para esta primera versión se usará el método de depreciación de línea recta.
                            </div>
                        </div>

                        <div class="modal-footer bg-white">
                            <button type="button"
                                    class="btn btn-secondary"
                                    wire:click="cerrarModal">
                                Cancelar
                            </button>

                            <button type="submit"
                                    class="btn btn-success">
                                {{ $categoria_id ? 'Actualizar' : 'Guardar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal-backdrop fade show"></div>
    @endif
</div>