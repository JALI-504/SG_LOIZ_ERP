<div>
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif
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
        <div class="card-header bg-dark">
            <h3 class="card-title">
                <i class="fas fa-database"></i>
                Respaldos de base de datos
            </h3>

            <div class="card-tools">
                @can('generar respaldos')
                    <button wire:click="generarRespaldo"
                            wire:loading.attr="disabled"
                            class="btn btn-success btn-sm">
                        <i class="fas fa-save"></i>
                        Generar respaldo
                    </button>
                @endcan
            </div>
        </div>

        <div class="card-body">
            <div wire:loading wire:target="generarRespaldo" class="alert alert-info">
                <i class="fas fa-spinner fa-spin"></i>
                Generando respaldo, por favor espere...
            </div>

            <div class="row mb-3">
                <div class="col-md-8">
                    <input type="text"
                           wire:model.debounce.500ms="search"
                           class="form-control"
                           placeholder="Buscar por archivo, tipo o estado...">
                </div>

                <div class="col-md-4">
                    <select wire:model="perPage" class="form-control">
                        <option value="10">10 registros</option>
                        <option value="25">25 registros</option>
                        <option value="50">50 registros</option>
                    </select>
                </div>
            </div>

            <div class="alert alert-warning">
                <strong>Importante:</strong>
                los respaldos contienen información sensible del sistema. Descárguelos y guárdelos en un lugar seguro.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Archivo</th>
                            <th>Tipo</th>
                            <th>Tamaño</th>
                            <th>Generado por</th>
                            <th>Fecha generación</th>
                            <th>Estado</th>
                            <th width="120">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($respaldos as $respaldo)
                            <tr>
                                <td>
                                    <strong>{{ $respaldo->nombre_archivo }}</strong>
                                    @if ($respaldo->observacion)
                                        <br>
                                        <small class="text-muted">
                                            {{ $respaldo->observacion }}
                                        </small>
                                    @endif
                                </td>

                                <td>{{ $respaldo->tipo }}</td>

                                <td>
                                    {{ number_format($respaldo->tamano_mb, 2) }} MB
                                </td>

                                <td>
                                    {{ $respaldo->usuario->name ?? 'Sistema' }}
                                </td>

                                <td>
                                    {{ $respaldo->fecha_generacion ? $respaldo->fecha_generacion->format('d/m/Y H:i') : 'No registrada' }}
                                </td>

                                <td>
                                    @if ($respaldo->estado === 'Generado')
                                        <span class="badge badge-success">
                                            Generado
                                        </span>
                                    @elseif ($respaldo->estado === 'Eliminado')
                                        <span class="badge badge-danger">
                                            Eliminado
                                        </span>
                                    @else
                                        <span class="badge badge-secondary">
                                            {{ $respaldo->estado }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if ($respaldo->estado === 'Generado')
                                        @can('descargar respaldos')
                                            <button wire:click="descargar({{ $respaldo->id }})"
                                                    class="btn btn-primary btn-sm"
                                                    title="Descargar respaldo">
                                                <i class="fas fa-download"></i>
                                            </button>
                                        @endcan

                                        @can('eliminar respaldos')
                                            <button wire:click="abrirEliminar({{ $respaldo->id }})"
                                                    class="btn btn-danger btn-sm"
                                                    title="Eliminar respaldo">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endcan
                                    @else
                                        <span class="text-muted">Sin acciones</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">
                                    No hay respaldos generados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $respaldos->links() }}
        </div>
    </div>

    @if ($mostrarModalEliminar)
        <div class="modal fade show"
            style="display: block;"
            tabindex="-1"
            role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger">
                        <h5 class="modal-title">
                            Eliminar respaldo
                        </h5>

                        <button type="button"
                                class="close"
                                wire:click="cerrarModalEliminar">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-warning">
                            Esta acción eliminará el archivo físico del respaldo en el servidor.
                        </div>

                        <p>
                            ¿Está seguro de eliminar el respaldo?
                        </p>

                        <p>
                            <strong>{{ $respaldoEliminarNombre }}</strong>
                        </p>

                        <small class="text-muted">
                            El sistema no permitirá eliminar el último respaldo disponible.
                        </small>
                    </div>

                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-secondary"
                                wire:click="cerrarModalEliminar">
                            Cancelar
                        </button>

                        <button type="button"
                                class="btn btn-danger"
                                wire:click="confirmarEliminar">
                            <i class="fas fa-trash"></i>
                            Confirmar eliminación
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-backdrop fade show"></div>
    @endif
</div>