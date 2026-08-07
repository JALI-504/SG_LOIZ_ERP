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
        <div class="card-header bg-dark">
            <h3 class="card-title">
                <i class="fas fa-balance-scale"></i>
                Conciliaciones bancarias
            </h3>

            <div class="card-tools">
                @can('crear conciliaciones bancarias')
                    <button wire:click="abrirFormulario"
                            class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i>
                        Nueva conciliación
                    </button>
                @endcan
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-8">
                    <input type="text"
                           wire:model.debounce.500ms="search"
                           class="form-control"
                           placeholder="Buscar por código, cuenta, banco o estado...">
                </div>

                <div class="col-md-4">
                    <select wire:model="perPage" class="form-control">
                        <option value="10">10 registros</option>
                        <option value="25">25 registros</option>
                        <option value="50">50 registros</option>
                    </select>
                </div>
            </div>

            <div class="alert alert-info">
                <strong>Nota:</strong>
                la conciliación compara el saldo calculado por el sistema contra el saldo final reportado por el banco.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Código</th>
                            <th>Cuenta</th>
                            <th>Rango</th>
                            <th>Saldo sistema</th>
                            <th>Saldo banco</th>
                            <th>Diferencia</th>
                            <th>Movimientos</th>
                            <th>Estado</th>
                            <th width="100">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($conciliaciones as $conciliacion)
                            <tr>
                                <td>
                                    <strong>{{ $conciliacion->codigo }}</strong>
                                </td>

                                <td>
                                    @if ($conciliacion->cuentaBancaria)
                                        <strong>{{ $conciliacion->cuentaBancaria->codigo }}</strong><br>
                                        {{ $conciliacion->cuentaBancaria->banco }} -
                                        {{ $conciliacion->cuentaBancaria->nombre_cuenta }}

                                        @if ($conciliacion->cuentaBancaria->numero_cuenta)
                                            <br>
                                            <small class="text-muted">
                                                No. {{ $conciliacion->cuentaBancaria->numero_cuenta }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="text-muted">Cuenta no encontrada</span>
                                    @endif
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($conciliacion->fecha_inicio)->format('d/m/Y') }}
                                    -
                                    {{ \Carbon\Carbon::parse($conciliacion->fecha_fin)->format('d/m/Y') }}
                                </td>

                                <td>
                                    <strong>
                                        L {{ number_format($conciliacion->saldo_final_sistema, 2) }}
                                    </strong>

                                    <br>
                                    <small class="text-muted">
                                        Inicial:
                                        L {{ number_format($conciliacion->saldo_inicial_sistema, 2) }}
                                    </small>
                                </td>

                                <td>
                                    <strong>
                                        L {{ number_format($conciliacion->saldo_final_banco, 2) }}
                                    </strong>
                                </td>

                                <td>
                                    @if (abs($conciliacion->diferencia) <= 0.01)
                                        <span class="badge badge-success">
                                            L {{ number_format($conciliacion->diferencia, 2) }}
                                        </span>
                                    @else
                                        <span class="badge badge-danger">
                                            L {{ number_format($conciliacion->diferencia, 2) }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ number_format($conciliacion->cantidad_movimientos, 0) }}
                                </td>

                                <td>
                                    @if ($conciliacion->estado === 'Conciliada')
                                        <span class="badge badge-success">
                                            Conciliada
                                        </span>
                                    @elseif ($conciliacion->estado === 'Con diferencia')
                                        <span class="badge badge-warning">
                                            Con diferencia
                                        </span>
                                    @elseif ($conciliacion->estado === 'Anulada')
                                        <span class="badge badge-danger">
                                            Anulada
                                        </span>

                                        @if ($conciliacion->fecha_anulacion)
                                            <br>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($conciliacion->fecha_anulacion)->format('d/m/Y H:i') }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="badge badge-secondary">
                                            {{ $conciliacion->estado }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if ($conciliacion->estado !== 'Anulada')
                                        @can('anular conciliaciones bancarias')
                                            <button wire:click="abrirAnular({{ $conciliacion->id }})"
                                                    class="btn btn-danger btn-sm"
                                                    title="Anular conciliación">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endcan
                                    @else
                                        <span class="text-muted">Sin acciones</span>
                                    @endif
                                </td>
                            </tr>

                            @if ($conciliacion->observacion || $conciliacion->motivo_anulacion)
                                <tr>
                                    <td colspan="9">
                                        @if ($conciliacion->observacion)
                                            <small class="text-muted">
                                                <strong>Observación:</strong>
                                                {{ $conciliacion->observacion }}
                                            </small>
                                        @endif

                                        @if ($conciliacion->motivo_anulacion)
                                            <br>
                                            <small class="text-muted">
                                                <strong>Motivo de anulación:</strong>
                                                {{ $conciliacion->motivo_anulacion }}

                                                @if ($conciliacion->usuarioAnulacion)
                                                    |
                                                    <strong>Anulado por:</strong>
                                                    {{ $conciliacion->usuarioAnulacion->name }}
                                                @endif
                                            </small>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted">
                                    No hay conciliaciones bancarias registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $conciliaciones->links() }}
        </div>
    </div>

    @if ($mostrarFormulario)
        <div class="modal fade show"
             style="display: block;"
             tabindex="-1"
             role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <form wire:submit.prevent="calcular">
                        <div class="modal-header bg-success">
                            <h5 class="modal-title">
                                Nueva conciliación bancaria
                            </h5>

                            <button type="button"
                                    class="close"
                                    wire:click="cerrarFormulario">
                                <span>&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <div class="alert alert-info">
                                Ingrese la cuenta bancaria, el rango de fechas y el saldo final según el banco.
                                Luego presione <strong>Calcular</strong>.
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Cuenta bancaria</label>
                                        <select wire:model.defer="cuenta_bancaria_id"
                                                class="form-control @error('cuenta_bancaria_id') is-invalid @enderror">
                                            <option value="">Seleccione una cuenta</option>

                                            @foreach ($cuentas as $cuenta)
                                                <option value="{{ $cuenta->id }}">
                                                    {{ $cuenta->codigo }}
                                                    -
                                                    {{ $cuenta->banco }}
                                                    -
                                                    {{ $cuenta->nombre_cuenta }}
                                                    |
                                                    Saldo actual: L {{ number_format($cuenta->saldo_actual, 2) }}
                                                </option>
                                            @endforeach
                                        </select>

                                        @error('cuenta_bancaria_id')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Fecha inicio</label>
                                        <input type="date"
                                               wire:model.defer="fecha_inicio"
                                               class="form-control @error('fecha_inicio') is-invalid @enderror">

                                        @error('fecha_inicio')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Fecha fin</label>
                                        <input type="date"
                                               wire:model.defer="fecha_fin"
                                               class="form-control @error('fecha_fin') is-invalid @enderror">

                                        @error('fecha_fin')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Saldo final según banco</label>
                                        <input type="number"
                                               step="0.01"
                                               wire:model.defer="saldo_final_banco"
                                               class="form-control @error('saldo_final_banco') is-invalid @enderror">

                                        @error('saldo_final_banco')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Observación</label>
                                        <textarea wire:model.defer="observacion"
                                                  rows="3"
                                                  class="form-control @error('observacion') is-invalid @enderror"
                                                  placeholder="Observación opcional"></textarea>

                                        @error('observacion')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            @if ($mostrarResultado)
                                <hr>

                                <h5>
                                    Resultado de conciliación
                                </h5>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="small-box bg-secondary">
                                            <div class="inner">
                                                <h4>
                                                    L {{ number_format($saldo_inicial_sistema, 2) }}
                                                </h4>
                                                <p>Saldo inicial sistema</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-wallet"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="small-box bg-success">
                                            <div class="inner">
                                                <h4>
                                                    L {{ number_format($total_entradas_sistema, 2) }}
                                                </h4>
                                                <p>Entradas sistema</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-arrow-down"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="small-box bg-danger">
                                            <div class="inner">
                                                <h4>
                                                    L {{ number_format($total_salidas_sistema, 2) }}
                                                </h4>
                                                <p>Salidas sistema</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-arrow-up"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="small-box bg-primary">
                                            <div class="inner">
                                                <h4>
                                                    L {{ number_format($saldo_final_sistema, 2) }}
                                                </h4>
                                                <p>Saldo final sistema</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-calculator"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="small-box bg-info">
                                            <div class="inner">
                                                <h4>
                                                    L {{ number_format($saldo_final_banco, 2) }}
                                                </h4>
                                                <p>Saldo final banco</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-university"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="small-box {{ abs($diferencia) <= 0.01 ? 'bg-success' : 'bg-warning' }}">
                                            <div class="inner">
                                                <h4>
                                                    L {{ number_format($diferencia, 2) }}
                                                </h4>
                                                <p>Diferencia</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-balance-scale"></i>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-3">
                                        <div class="small-box bg-secondary">
                                            <div class="inner">
                                                <h4>
                                                    {{ number_format($cantidad_movimientos, 0) }}
                                                </h4>
                                                <p>Movimientos</p>
                                            </div>
                                            <div class="icon">
                                                <i class="fas fa-list"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if (abs($diferencia) <= 0.01)
                                    <div class="alert alert-success">
                                        <strong>Resultado:</strong>
                                        la cuenta está conciliada. El saldo del sistema coincide con el saldo del banco.
                                    </div>
                                @else
                                    <div class="alert alert-warning">
                                        <strong>Resultado:</strong>
                                        existe una diferencia de
                                        <strong>L {{ number_format($diferencia, 2) }}</strong>
                                        entre el sistema y el banco.
                                    </div>
                                @endif
                            @endif
                        </div>

                        <div class="modal-footer">
                            <button type="button"
                                    class="btn btn-secondary"
                                    wire:click="cerrarFormulario">
                                Cancelar
                            </button>

                            <button type="submit"
                                    class="btn btn-info">
                                <i class="fas fa-calculator"></i>
                                Calcular
                            </button>

                            @if ($mostrarResultado)
                                <button type="button"
                                        wire:click="registrarConciliacion"
                                        wire:loading.attr="disabled"
                                        wire:target="registrarConciliacion"
                                        class="btn btn-success">
                                    <i class="fas fa-save"></i>
                                    Registrar conciliación
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal-backdrop fade show"></div>
    @endif

    @if ($mostrarModalAnular)
        <div class="modal fade show"
             style="display: block;"
             tabindex="-1"
             role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-danger">
                        <h5 class="modal-title">
                            Anular conciliación bancaria
                        </h5>

                        <button type="button"
                                class="close"
                                wire:click="cerrarModalAnular">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-warning">
                            Esta acción no modifica los movimientos bancarios ni el saldo de la cuenta.
                            Solo marca la conciliación como anulada.
                        </div>

                        <p>
                            ¿Está seguro de anular la conciliación?
                        </p>

                        <p>
                            <strong>{{ $conciliacionAnularCodigo }}</strong>
                        </p>

                        <div class="form-group">
                            <label>Motivo de anulación</label>
                            <textarea wire:model.defer="motivo_anulacion"
                                      rows="3"
                                      class="form-control @error('motivo_anulacion') is-invalid @enderror"
                                      placeholder="Explique el motivo de la anulación"></textarea>

                            @error('motivo_anulacion')
                                <span class="invalid-feedback">
                                    {{ $message }}
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-secondary"
                                wire:click="cerrarModalAnular">
                            Cancelar
                        </button>

                        <button type="button"
                                wire:click="confirmarAnular"
                                wire:loading.attr="disabled"
                                wire:target="confirmarAnular"
                                class="btn btn-danger">
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