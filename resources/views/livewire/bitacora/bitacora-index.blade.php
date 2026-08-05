<div>
    <div class="card">
        <div class="card-header bg-dark">
            <h3 class="card-title">
                <i class="fas fa-clipboard-check"></i>
                Bitácora del sistema
            </h3>
        </div>

        <div class="card-body">
            <div class="alert alert-info">
                Aquí se mostrarán las acciones importantes realizadas por los usuarios dentro del sistema.
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Buscar</label>
                    <input type="text"
                           class="form-control"
                           placeholder="Buscar por módulo, acción, descripción, modelo o IP..."
                           wire:model.debounce.500ms="search">
                </div>

                <div class="col-md-2">
                    <label>Desde</label>
                    <input type="date"
                           class="form-control"
                           wire:model="fechaDesde">
                </div>

                <div class="col-md-2">
                    <label>Hasta</label>
                    <input type="date"
                           class="form-control"
                           wire:model="fechaHasta">
                </div>

                <div class="col-md-2">
                    <label>Registros</label>
                    <select class="form-control" wire:model="perPage">
                        <option value="15">15 registros</option>
                        <option value="25">25 registros</option>
                        <option value="50">50 registros</option>
                        <option value="100">100 registros</option>
                    </select>
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button type="button"
                            class="btn btn-secondary btn-block"
                            wire:click="limpiarFiltros">
                        <i class="fas fa-broom"></i> Limpiar
                    </button>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-4">
                    <label>Usuario</label>
                    <select class="form-control" wire:model="usuario_id">
                        <option value="todos">Todos los usuarios</option>

                        @foreach ($usuarios as $usuario)
                            <option value="{{ $usuario->id }}">
                                {{ $usuario->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Módulo</label>
                    <select class="form-control" wire:model="modulo">
                        <option value="todos">Todos los módulos</option>

                        @foreach ($modulos as $itemModulo)
                            <option value="{{ $itemModulo }}">
                                {{ $itemModulo }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4">
                    <label>Acción</label>
                    <select class="form-control" wire:model="accion">
                        <option value="todos">Todas las acciones</option>

                        @foreach ($acciones as $itemAccion)
                            <option value="{{ $itemAccion }}">
                                {{ $itemAccion }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th width="120">Fecha / Hora</th>
                            <th>Usuario</th>
                            <th>Módulo</th>
                            <th>Acción</th>
                            <th>Descripción</th>
                            <th>Modelo</th>
                            <th>IP</th>
                            <th width="90">Acción</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($bitacoras as $bitacora)
                            <tr>
                                <td>
                                    {{ \Carbon\Carbon::parse($bitacora->fecha)->format('d/m/Y') }}
                                    <br>
                                    <small class="text-muted">
                                        {{ $bitacora->hora }}
                                    </small>
                                </td>

                                <td>
                                    @if ($bitacora->usuario)
                                        <strong>{{ $bitacora->usuario->name }}</strong>
                                    @else
                                        <span class="text-muted">Sistema</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge badge-primary">
                                        {{ $bitacora->modulo }}
                                    </span>
                                </td>

                                <td>
                                    @if ($bitacora->accion === 'Crear' || $bitacora->accion === 'Registrar')
                                        <span class="badge badge-success">{{ $bitacora->accion }}</span>
                                    @elseif ($bitacora->accion === 'Anular' || $bitacora->accion === 'Eliminar')
                                        <span class="badge badge-danger">{{ $bitacora->accion }}</span>
                                    @elseif ($bitacora->accion === 'Actualizar' || $bitacora->accion === 'Editar')
                                        <span class="badge badge-warning">{{ $bitacora->accion }}</span>
                                    @elseif ($bitacora->accion === 'Convertir')
                                        <span class="badge badge-info">{{ $bitacora->accion }}</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $bitacora->accion }}</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $bitacora->descripcion ?: 'Sin descripción' }}
                                </td>

                                <td>
                                    @if ($bitacora->modelo)
                                        <strong>{{ class_basename($bitacora->modelo) }}</strong>

                                        @if ($bitacora->modelo_id)
                                            <br>
                                            <small class="text-muted">
                                                ID: {{ $bitacora->modelo_id }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="text-muted">No aplica</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $bitacora->ip ?: 'No registrada' }}
                                </td>

                                <td>
                                    <button type="button"
                                            class="btn btn-info btn-xs"
                                            wire:click="verDetalle({{ $bitacora->id }})">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    No hay registros en la bitácora con los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $bitacoras->links() }}
        </div>
    </div>

    @if ($mostrarModalDetalle && $bitacoraDetalle)
        @php
            $datosAnteriores = null;
            $datosNuevos = null;

            if ($bitacoraDetalle->datos_anteriores) {
                $datosAnteriores = json_encode(
                    json_decode($bitacoraDetalle->datos_anteriores, true),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                );
            }

            if ($bitacoraDetalle->datos_nuevos) {
                $datosNuevos = json_encode(
                    json_decode($bitacoraDetalle->datos_nuevos, true),
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                );
            }
        @endphp

        <div class="modal fade show"
             style="display: block;"
             role="dialog"
             aria-modal="true">

            <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <div>
                            <h5 class="modal-title mb-0">
                                <i class="fas fa-clipboard-check"></i>
                                Detalle de bitácora
                            </h5>

                            <small class="text-muted">
                                Registro #{{ $bitacoraDetalle->id }}
                            </small>
                        </div>

                        <button type="button" class="close" wire:click="cerrarModalDetalle">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body" style="max-height: calc(100vh - 220px); overflow-y: auto;">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-primary">
                                        <i class="far fa-calendar-alt"></i>
                                    </span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">Fecha</span>
                                        <span class="info-box-number">
                                            {{ \Carbon\Carbon::parse($bitacoraDetalle->fecha)->format('d/m/Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info">
                                        <i class="far fa-clock"></i>
                                    </span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">Hora</span>
                                        <span class="info-box-number">
                                            {{ $bitacoraDetalle->hora }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-secondary">
                                        <i class="fas fa-user"></i>
                                    </span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">Usuario</span>
                                        <span class="info-box-number">
                                            {{ $bitacoraDetalle->usuario->name ?? 'Sistema' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-dark">
                                        <i class="fas fa-network-wired"></i>
                                    </span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">IP</span>
                                        <span class="info-box-number">
                                            {{ $bitacoraDetalle->ip ?: 'No registrada' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title">
                                    Información principal
                                </h3>
                            </div>

                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Módulo:</strong>
                                        <p>{{ $bitacoraDetalle->modulo }}</p>
                                    </div>

                                    <div class="col-md-4">
                                        <strong>Acción:</strong>
                                        <p>{{ $bitacoraDetalle->accion }}</p>
                                    </div>

                                    <div class="col-md-4">
                                        <strong>Modelo:</strong>
                                        <p>
                                            @if ($bitacoraDetalle->modelo)
                                                {{ $bitacoraDetalle->modelo }}

                                                @if ($bitacoraDetalle->modelo_id)
                                                    <br>
                                                    <small class="text-muted">
                                                        ID: {{ $bitacoraDetalle->modelo_id }}
                                                    </small>
                                                @endif
                                            @else
                                                No aplica
                                            @endif
                                        </p>
                                    </div>
                                </div>

                                <strong>Descripción:</strong>
                                <p>
                                    {{ $bitacoraDetalle->descripcion ?: 'Sin descripción' }}
                                </p>

                                <strong>URL:</strong>
                                <p style="word-break: break-all;">
                                    {{ $bitacoraDetalle->url ?: 'No registrada' }}
                                </p>

                                <strong>User agent:</strong>
                                <p style="word-break: break-all;">
                                    {{ $bitacoraDetalle->user_agent ?: 'No registrado' }}
                                </p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-outline card-warning">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            Datos anteriores
                                        </h3>
                                    </div>

                                    <div class="card-body">
                                        @if ($datosAnteriores)
                                            <pre style="white-space: pre-wrap; font-size: 12px;">{{ $datosAnteriores }}</pre>
                                        @else
                                            <span class="text-muted">
                                                No hay datos anteriores registrados.
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card card-outline card-success">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            Datos nuevos
                                        </h3>
                                    </div>

                                    <div class="card-body">
                                        @if ($datosNuevos)
                                            <pre style="white-space: pre-wrap; font-size: 12px;">{{ $datosNuevos }}</pre>
                                        @else
                                            <span class="text-muted">
                                                No hay datos nuevos registrados.
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-secondary"
                                wire:click="cerrarModalDetalle">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-backdrop fade show"></div>
    @endif
</div>