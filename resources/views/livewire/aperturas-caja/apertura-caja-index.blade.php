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

    @if ($aperturaAbierta)
        <div class="alert alert-info">
            <strong>Caja abierta actualmente:</strong>
            {{ $aperturaAbierta->codigo }}
            |
            Fecha: {{ \Carbon\Carbon::parse($aperturaAbierta->fecha)->format('d/m/Y') }}
            |
            Monto inicial: L {{ number_format($aperturaAbierta->monto_inicial, 2) }}
            |
            Responsable: {{ $aperturaAbierta->usuario->name ?? 'Sistema' }}
        </div>
    @else
        <div class="alert alert-warning">
            No hay caja abierta actualmente.
        </div>
    @endif

    <div class="row">
        @can('crear aperturas caja')
            <div class="col-md-4">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-cash-register"></i>
                            Nueva apertura de caja
                        </h3>
                    </div>

                    <div class="card-body">
                        <div class="form-group">
                            <label>Fecha</label>
                            <input type="date"
                                   wire:model="fecha"
                                   class="form-control @error('fecha') is-invalid @enderror">

                            @error('fecha')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Monto inicial</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   wire:model="monto_inicial"
                                   class="form-control @error('monto_inicial') is-invalid @enderror">

                            @error('monto_inicial')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label>Observación</label>
                            <textarea wire:model="observacion"
                                      rows="3"
                                      class="form-control @error('observacion') is-invalid @enderror"
                                      placeholder="Observación opcional..."></textarea>

                            @error('observacion')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="card-footer text-right">
                        <button wire:click="registrarApertura"
                                wire:loading.attr="disabled"
                                class="btn btn-success">
                            <i class="fas fa-save"></i>
                            Abrir caja
                        </button>
                    </div>
                </div>
            </div>
        @endcan

        <div class="@can('crear aperturas caja') col-md-8 @else col-md-12 @endcan">
            <div class="card">
                <div class="card-header bg-dark">
                    <h3 class="card-title">
                        <i class="fas fa-list"></i>
                        Historial de aperturas
                    </h3>
                </div>

                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <input type="text"
                                   wire:model.debounce.500ms="search"
                                   class="form-control"
                                   placeholder="Buscar por código, fecha o estado...">
                        </div>

                        <div class="col-md-4">
                            <select wire:model="perPage" class="form-control">
                                <option value="10">10 registros</option>
                                <option value="25">25 registros</option>
                                <option value="50">50 registros</option>
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Código</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Monto inicial</th>
                                    <th>Usuario</th>
                                    <th>Estado</th>
                                    <th width="120">Acciones</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($aperturas as $apertura)
                                    <tr>
                                        <td>
                                            <strong>{{ $apertura->codigo }}</strong>
                                            @if ($apertura->observacion)
                                                <br>
                                                <small class="text-muted">
                                                    {{ $apertura->observacion }}
                                                </small>
                                            @endif
                                        </td>

                                        <td>
                                            {{ \Carbon\Carbon::parse($apertura->fecha)->format('d/m/Y') }}
                                        </td>

                                        <td>
                                            {{ $apertura->hora_apertura }}
                                        </td>

                                        <td>
                                            L {{ number_format($apertura->monto_inicial, 2) }}
                                        </td>

                                        <td>
                                            {{ $apertura->usuario->name ?? 'Sistema' }}
                                        </td>

                                        <td>
                                            @if ($apertura->estado === 'Abierta')
                                                <span class="badge badge-success">Abierta</span>
                                            @elseif ($apertura->estado === 'Cerrada')
                                                <span class="badge badge-primary">Cerrada</span>
                                            @elseif ($apertura->estado === 'Anulada')
                                                <span class="badge badge-danger">Anulada</span>
                                            @else
                                                <span class="badge badge-secondary">{{ $apertura->estado }}</span>
                                            @endif

                                            @if ($apertura->cierreCaja)
                                                <br>
                                                <small class="text-muted">
                                                    Cierre: {{ $apertura->cierreCaja->codigo }}
                                                </small>
                                            @endif
                                        </td>

                                        <td>
                                            @can('anular aperturas caja')
                                                @if ($apertura->estado === 'Abierta' && !$apertura->cierreCaja)
                                                    <button wire:click="abrirAnular({{ $apertura->id }})"
                                                            class="btn btn-danger btn-sm"
                                                            title="Anular apertura">
                                                        <i class="fas fa-ban"></i>
                                                    </button>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            No hay aperturas registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $aperturas->links() }}
                </div>
            </div>
        </div>
    </div>

    @if ($mostrarModalAnulacion)
        <div class="modal fade show"
             style="display: block;"
             tabindex="-1"
             role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger">
                        <h5 class="modal-title">
                            Anular apertura de caja
                        </h5>

                        <button type="button"
                                class="close"
                                wire:click="cerrarModalAnulacion">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-warning">
                            Esta acción marcará la apertura como anulada.
                        </div>

                        <div class="form-group">
                            <label>Motivo de anulación</label>
                            <textarea wire:model="motivoAnulacion"
                                      rows="3"
                                      class="form-control @error('motivoAnulacion') is-invalid @enderror"
                                      placeholder="Ingrese el motivo de anulación..."></textarea>

                            @error('motivoAnulacion')
                                <span class="invalid-feedback">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-secondary"
                                wire:click="cerrarModalAnulacion">
                            Cancelar
                        </button>

                        <button type="button"
                                class="btn btn-danger"
                                wire:click="confirmarAnular">
                            <i class="fas fa-ban"></i>
                            Confirmar anulación
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-backdrop fade show"></div>
    @endif
</div>