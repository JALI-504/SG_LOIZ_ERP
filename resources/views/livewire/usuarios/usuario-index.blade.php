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
            <h3 class="card-title">Listado de usuarios</h3>

            <div class="card-tools">
                @can('crear usuarios')
                    @can('asignar roles usuarios')
                        <button type="button"
                                class="btn btn-primary btn-sm"
                               wire:click.prevent="create"
                            <i class="fas fa-plus"></i> Nuevo usuario
                        </button>
                    @endcan
                @endcan
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text"
                           class="form-control"
                           placeholder="Buscar por nombre o correo..."
                           wire:model.debounce.500ms="search">
                </div>

                <div class="col-md-3">
                    <select class="form-control" wire:model="filtroRol">
                        <option value="todos">Todos los roles</option>

                        @foreach ($roles as $rolItem)
                            <option value="{{ $rolItem }}">{{ $rolItem }}</option>
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
                            <th>Nombre</th>
                            <th>Correo</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Creado</th>

                            @if (
                                auth()->user()->can('editar usuarios') ||
                                auth()->user()->can('desactivar usuarios') ||
                                auth()->user()->can('cambiar password usuarios')
                            )
                                <th width="230">Acciones</th>
                            @endif
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($usuarios as $usuario)
                            <tr>
                                <td>
                                    <strong>{{ $usuario->name }}</strong>

                                    @if ($usuario->id === auth()->id())
                                        <span class="badge badge-info">Usuario actual</span>
                                    @endif
                                </td>

                                <td>{{ $usuario->email }}</td>

                                <td>
                                    @forelse ($usuario->roles as $rolUsuario)
                                        <span class="badge badge-primary">
                                            {{ $rolUsuario->name }}
                                        </span>
                                    @empty
                                        <span class="badge badge-secondary">
                                            Sin rol
                                        </span>
                                    @endforelse
                                </td>

                                <td>
                                    @if ($usuario->activo)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-danger">Inactivo</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $usuario->created_at ? $usuario->created_at->format('d/m/Y') : '' }}
                                </td>

                                @if (
                                    auth()->user()->can('editar usuarios') ||
                                    auth()->user()->can('desactivar usuarios') ||
                                    auth()->user()->can('cambiar password usuarios')
                                )
                                    <td>
                                        @can('editar usuarios')
                                            @can('asignar roles usuarios')
                                                <button type="button"
                                                        class="btn btn-warning btn-xs"
                                                        wire:click="edit({{ $usuario->id }})">
                                                    <i class="fas fa-edit"></i> Editar
                                                </button>
                                            @endcan
                                        @endcan

                                        @can('cambiar password usuarios')
                                            <button type="button"
                                                    class="btn btn-info btn-xs"
                                                    wire:click="abrirCambioPassword({{ $usuario->id }})">
                                                <i class="fas fa-key"></i>
                                            </button>
                                        @endcan

                                        @can('desactivar usuarios')
                                            @if ($usuario->id !== auth()->id())
                                                <button type="button"
                                                        class="btn btn-{{ $usuario->activo ? 'secondary' : 'success' }} btn-xs"
                                                        wire:click="cambiarEstado({{ $usuario->id }})"
                                                        onclick="confirm('¿Seguro que desea cambiar el estado de este usuario?') || event.stopImmediatePropagation()">
                                                    @if ($usuario->activo)
                                                        Desactivar
                                                    @else
                                                        Activar
                                                    @endif
                                                </button>
                                            @endif
                                        @endcan
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{
                                    auth()->user()->can('editar usuarios') ||
                                    auth()->user()->can('desactivar usuarios') ||
                                    auth()->user()->can('cambiar password usuarios')
                                        ? 6
                                        : 5
                                }}" class="text-center">
                                    No hay usuarios registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $usuarios->links() }}
        </div>
    </div>

    @can('crear usuarios')
        @can('asignar roles usuarios')
            @if ($mostrarModalUsuario)
                {{-- Modal usuario --}}
                <div class="modal fade show"
                     id="usuarioModal"
                     tabindex="-1"
                     role="dialog"
                     style="display: block;"
                     aria-modal="true">
                    <div class="modal-dialog modal-lg" role="document">
                        <form wire:submit.prevent="{{ $usuario_id ? 'update' : 'store' }}" class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">{{ $modalTitle }}</h5>

                                <button type="button" class="close" wire:click="cerrarModalUsuario">
                                    <span>&times;</span>
                                </button>
                            </div>

                            <div class="modal-body">
                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Nombre <span class="text-danger">*</span></label>
                                        <input type="text"
                                               class="form-control"
                                               wire:model.defer="name">

                                        @error('name')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
                                        <label>Correo <span class="text-danger">*</span></label>
                                        <input type="email"
                                               class="form-control"
                                               wire:model.defer="email">

                                        @error('email')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>
                                </div>

                                @if (!$usuario_id)
                                    <div class="form-row">
                                        <div class="form-group col-md-6">
                                            <label>Contraseña <span class="text-danger">*</span></label>
                                            <input type="password"
                                                   class="form-control"
                                                   wire:model.defer="password">

                                            @error('password')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group col-md-6">
                                            <label>Confirmar contraseña <span class="text-danger">*</span></label>
                                            <input type="password"
                                                   class="form-control"
                                                   wire:model.defer="password_confirmation">
                                        </div>
                                    </div>
                                @endif

                                <div class="form-row">
                                    <div class="form-group col-md-6">
                                        <label>Rol <span class="text-danger">*</span></label>
                                        <select class="form-control" wire:model.defer="rol">
                                            <option value="">Seleccione...</option>

                                            @foreach ($roles as $rolItem)
                                                <option value="{{ $rolItem }}">
                                                    {{ $rolItem }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('rol')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </div>

                                    <div class="form-group col-md-6">
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

                                @if ($usuario_id)
                                    <div class="alert alert-info">
                                        Para cambiar la contraseña use el botón de llave en el listado de usuarios.
                                    </div>
                                @endif
                            </div>

                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" wire:click="cerrarModalUsuario">
                                    Cancelar
                                </button>

                                <button type="submit"
                                        class="btn btn-primary"
                                        wire:loading.attr="disabled">
                                    {{ $usuario_id ? 'Actualizar usuario' : 'Guardar usuario' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="modal-backdrop fade show"></div>
            @endif
        @endcan
    @endcan

    @can('cambiar password usuarios')
        @if ($mostrarModalPassword)
            {{-- Modal contraseña --}}
            <div class="modal fade show"
                 id="passwordModal"
                 tabindex="-1"
                 role="dialog"
                 style="display: block;"
                 aria-modal="true">
                <div class="modal-dialog" role="document">
                    <form wire:submit.prevent="cambiarPassword" class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Cambiar contraseña</h5>

                            <button type="button" class="close" wire:click="cerrarModalPassword">
                                <span>&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <div class="form-group">
                                <label>Nueva contraseña <span class="text-danger">*</span></label>
                                <input type="password"
                                       class="form-control"
                                       wire:model.defer="nueva_password">

                                @error('nueva_password')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Confirmar nueva contraseña <span class="text-danger">*</span></label>
                                <input type="password"
                                       class="form-control"
                                       wire:model.defer="nueva_password_confirmation">
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="cerrarModalPassword">
                                Cancelar
                            </button>

                            <button type="submit"
                                    class="btn btn-primary"
                                    wire:loading.attr="disabled">
                                Actualizar contraseña
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="modal-backdrop fade show"></div>
        @endif
    @endcan
</div></div>