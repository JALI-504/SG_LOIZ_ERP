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

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de roles</h3>

            <div class="card-tools">
                @can('crear roles')
                    @can('asignar permisos roles')
                        <button type="button"
                                class="btn btn-primary btn-sm"
                                wire:click.prevent="create">
                            <i class="fas fa-plus"></i> Nuevo rol
                        </button>
                    @endcan
                @endcan
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-5">
                    <input type="text"
                           class="form-control"
                           placeholder="Buscar rol..."
                           wire:model.debounce.500ms="search">
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Rol</th>
                            <th>Permisos asignados</th>

                            @if (
                                auth()->user()->can('editar roles') ||
                                auth()->user()->can('eliminar roles')
                            )
                                <th width="180">Acciones</th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($roles as $rol)
                            <tr>
                                <td>
                                    <strong>{{ $rol->name }}</strong>

                                    @if ($rol->name === 'Administrador')
                                        <span class="badge badge-danger">Protegido</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge badge-info">
                                        {{ $rol->permissions->count() }} permisos
                                    </span>

                                    <div class="mt-1">
                                        @foreach ($rol->permissions->take(8) as $permiso)
                                            <span class="badge badge-light border">
                                                {{ $permiso->name }}
                                            </span>
                                        @endforeach

                                        @if ($rol->permissions->count() > 8)
                                            <span class="badge badge-secondary">
                                                +{{ $rol->permissions->count() - 8 }} más
                                            </span>
                                        @endif
                                    </div>
                                </td>

                                @if (
                                    auth()->user()->can('editar roles') ||
                                    auth()->user()->can('eliminar roles')
                                )
                                    <td>
                                        @can('editar roles')
                                            @can('asignar permisos roles')
                                                @if ($rol->name !== 'Administrador')
                                                    <button type="button"
                                                            class="btn btn-warning btn-xs"
                                                            wire:click="edit({{ $rol->id }})">
                                                        <i class="fas fa-edit"></i> Editar
                                                    </button>
                                                @endif
                                            @endcan
                                        @endcan

                                        @can('eliminar roles')
                                            @if (!in_array($rol->name, ['Administrador', 'Cajero', 'Inventario', 'Reportes']))
                                                <button type="button"
                                                        class="btn btn-danger btn-xs"
                                                        wire:click="eliminar({{ $rol->id }})"
                                                        onclick="confirm('¿Seguro que desea eliminar este rol?') || event.stopImmediatePropagation()">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        @endcan
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{
                                    auth()->user()->can('editar roles') ||
                                    auth()->user()->can('eliminar roles')
                                        ? 3
                                        : 2
                                }}" class="text-center">
                                    No hay roles registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @can('crear roles')
        @can('asignar permisos roles')
            @if ($mostrarModalRol)
                <div class="modal fade show"
                     id="rolModal"
                     tabindex="-1"
                     role="dialog"
                     style="display: block;"
                     aria-modal="true">

                    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
                        <form wire:submit.prevent="{{ $rol_id ? 'update' : 'store' }}" class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ $modalTitle }}</h5>

                                <button type="button" class="close" wire:click="cerrarModalRol">
                                    <span>&times;</span>
                                </button>
                            </div>

                            <div class="modal-body" style="max-height: calc(100vh - 220px); overflow-y: auto;">
                                <div class="form-group">
                                    <label>Nombre del rol <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control"
                                           wire:model.defer="name"
                                           {{ $name === 'Administrador' ? 'readonly' : '' }}>

                                    @error('name')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <hr>

                                <h5>Permisos del rol</h5>

                                <div class="alert alert-info">
                                    Seleccione los permisos que tendrá este rol dentro del sistema.
                                </div>

                                <div class="row">
                                    @foreach ($permisosAgrupados as $grupo => $permisosGrupo)
                                        @if ($permisosGrupo->count() > 0)
                                            <div class="col-md-4 mb-3">
                                                <div class="card h-100">
                                                    <div class="card-header py-2">
                                                        <strong>{{ $grupo }}</strong>
                                                    </div>

                                                    <div class="card-body">
                                                        @foreach ($permisosGrupo as $permiso)
                                                            <div class="custom-control custom-checkbox mb-1">
                                                                <input type="checkbox"
                                                                       class="custom-control-input"
                                                                       id="permiso_{{ $permiso->id }}"
                                                                       value="{{ $permiso->name }}"
                                                                       wire:model.defer="permisosSeleccionados">

                                                                <label class="custom-control-label"
                                                                       for="permiso_{{ $permiso->id }}">
                                                                    {{ $permiso->name }}
                                                                </label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="button"
                                        class="btn btn-secondary"
                                        wire:click="cerrarModalRol">
                                    Cancelar
                                </button>

                                <button type="submit"
                                        class="btn btn-primary"
                                        wire:loading.attr="disabled">
                                    {{ $rol_id ? 'Actualizar rol' : 'Guardar rol' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="modal-backdrop fade show"></div>
            @endif
        @endcan
    @endcan
</div>