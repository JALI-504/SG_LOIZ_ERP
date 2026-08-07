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
                <i class="fas fa-exchange-alt"></i>
                Movimientos bancarios
            </h3>

            <div class="card-tools">
                @can('crear movimientos bancarios')
                    <button wire:click="abrirFormulario"
                            class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i>
                        Nuevo movimiento
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
                           placeholder="Buscar por código, cuenta, banco, referencia, tipo o descripción...">
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
                los movimientos actualizan automáticamente el saldo actual de la cuenta bancaria.
                Para mantener el saldo correcto, solo se permite anular el último movimiento activo de cada cuenta.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Código</th>
                            <th>Fecha</th>
                            <th>Cuenta</th>
                            <th>Tipo</th>
                            <th>Categoría</th>
                            <th>Referencia</th>
                            <th>Monto</th>
                            <th>Saldo anterior</th>
                            <th>Saldo nuevo</th>
                            <th>Estado</th>
                            <th width="100">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($movimientos as $movimiento)
                            <tr>
                                <td>
                                    <strong>{{ $movimiento->codigo }}</strong>

                                    @if ($movimiento->origen && $movimiento->origen !== 'Manual')
                                        <br>
                                        <span class="badge badge-info">
                                            {{ $movimiento->origen }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y') }}

                                    @if ($movimiento->hora)
                                        <br>
                                        <small class="text-muted">
                                            {{ $movimiento->hora }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    @if ($movimiento->cuentaBancaria)
                                        <strong>{{ $movimiento->cuentaBancaria->codigo }}</strong><br>
                                        {{ $movimiento->cuentaBancaria->banco }} -
                                        {{ $movimiento->cuentaBancaria->nombre_cuenta }}

                                        @if ($movimiento->cuentaBancaria->numero_cuenta)
                                            <br>
                                            <small class="text-muted">
                                                No. {{ $movimiento->cuentaBancaria->numero_cuenta }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="text-muted">Cuenta no encontrada</span>
                                    @endif
                                </td>

                                <td>
                                    @if ($movimiento->tipo === 'Entrada')
                                        <span class="badge badge-success">
                                            Entrada
                                        </span>
                                    @else
                                        <span class="badge badge-danger">
                                            Salida
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    {{ $movimiento->categoria ?: 'No definida' }}

                                    @if ($movimiento->descripcion)
                                        <br>
                                        <small class="text-muted">
                                            {{ $movimiento->descripcion }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    {{ $movimiento->referencia ?: 'Sin referencia' }}
                                </td>

                                <td>
                                    <strong>
                                        L {{ number_format($movimiento->monto, 2) }}
                                    </strong>
                                </td>

                                <td>
                                    L {{ number_format($movimiento->saldo_anterior, 2) }}
                                </td>

                                <td>
                                    <strong>
                                        L {{ number_format($movimiento->saldo_nuevo, 2) }}
                                    </strong>
                                </td>

                                <td>
                                    @if ($movimiento->estado === 'Activo')
                                        <span class="badge badge-success">
                                            Activo
                                        </span>
                                    @elseif ($movimiento->estado === 'Anulado')
                                        <span class="badge badge-danger">
                                            Anulado
                                        </span>

                                        @if ($movimiento->fecha_anulacion)
                                            <br>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($movimiento->fecha_anulacion)->format('d/m/Y H:i') }}
                                            </small>
                                        @endif
                                    @else
                                        <span class="badge badge-secondary">
                                            {{ $movimiento->estado }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if ($movimiento->estado === 'Activo')
                                        @can('anular movimientos bancarios')
                                            <button wire:click="abrirAnular({{ $movimiento->id }})"
                                                    class="btn btn-danger btn-sm"
                                                    title="Anular movimiento">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        @endcan
                                    @else
                                        <span class="text-muted">Sin acciones</span>
                                    @endif
                                </td>
                            </tr>

                            @if ($movimiento->estado === 'Anulado' && $movimiento->motivo_anulacion)
                                <tr>
                                    <td colspan="11">
                                        <small class="text-muted">
                                            <strong>Motivo de anulación:</strong>
                                            {{ $movimiento->motivo_anulacion }}

                                            @if ($movimiento->usuarioAnulacion)
                                                |
                                                <strong>Anulado por:</strong>
                                                {{ $movimiento->usuarioAnulacion->name }}
                                            @endif
                                        </small>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted">
                                    No hay movimientos bancarios registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $movimientos->links() }}
        </div>
    </div>

    @if ($mostrarFormulario)
        <div class="modal fade show"
             style="display: block;"
             tabindex="-1"
             role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form wire:submit.prevent="registrarMovimiento">
                        <div class="modal-header {{ $tipo === 'Entrada' ? 'bg-success' : 'bg-danger' }}">
                            <h5 class="modal-title">
                                Nuevo movimiento bancario
                            </h5>

                            <button type="button"
                                    class="close"
                                    wire:click="cerrarFormulario">
                                <span>&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-8">
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
                                                    Saldo: L {{ number_format($cuenta->saldo_actual, 2) }}
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

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Fecha</label>
                                        <input type="date"
                                               wire:model.defer="fecha"
                                               class="form-control @error('fecha') is-invalid @enderror">

                                        @error('fecha')
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
                                        <label>Tipo</label>
                                        <select wire:model="tipo"
                                                class="form-control @error('tipo') is-invalid @enderror">
                                            <option value="Entrada">Entrada</option>
                                            <option value="Salida">Salida</option>
                                        </select>

                                        @error('tipo')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Categoría</label>
                                        <select wire:model.defer="categoria"
                                                class="form-control @error('categoria') is-invalid @enderror">
                                            @if ($tipo === 'Entrada')
                                                <option value="Depósito">Depósito</option>
                                                <option value="Transferencia recibida">Transferencia recibida</option>
                                                <option value="Ajuste de entrada">Ajuste de entrada</option>
                                                <option value="Intereses bancarios">Intereses bancarios</option>
                                                <option value="Otro ingreso bancario">Otro ingreso bancario</option>
                                            @else
                                                <option value="Retiro">Retiro</option>
                                                <option value="Transferencia enviada">Transferencia enviada</option>
                                                <option value="Ajuste de salida">Ajuste de salida</option>
                                                <option value="Comisión bancaria">Comisión bancaria</option>
                                                <option value="Pago bancario">Pago bancario</option>
                                                <option value="Otro egreso bancario">Otro egreso bancario</option>
                                            @endif
                                        </select>

                                        @error('categoria')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Monto</label>
                                        <input type="number"
                                               step="0.01"
                                               min="0.01"
                                               wire:model.defer="monto"
                                               class="form-control @error('monto') is-invalid @enderror">

                                        @error('monto')
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
                                        <label>Referencia</label>
                                        <input type="text"
                                               wire:model.defer="referencia"
                                               class="form-control @error('referencia') is-invalid @enderror"
                                               placeholder="No. transferencia, cheque, depósito">

                                        @error('referencia')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label>Descripción</label>
                                        <input type="text"
                                               wire:model.defer="descripcion"
                                               class="form-control @error('descripcion') is-invalid @enderror"
                                               placeholder="Descripción breve del movimiento">

                                        @error('descripcion')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Observación</label>
                                <textarea wire:model.defer="observacion"
                                          class="form-control @error('observacion') is-invalid @enderror"
                                          rows="3"
                                          placeholder="Observación opcional"></textarea>

                                @error('observacion')
                                    <span class="invalid-feedback">
                                        {{ $message }}
                                    </span>
                                @enderror
                            </div>

                            <div class="alert alert-warning">
                                <strong>Importante:</strong>
                                al guardar este movimiento, el saldo actual de la cuenta bancaria será actualizado automáticamente.
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button"
                                    class="btn btn-secondary"
                                    wire:click="cerrarFormulario">
                                Cancelar
                            </button>

                            <button type="submit"
                                    wire:loading.attr="disabled"
                                    wire:target="registrarMovimiento"
                                    class="btn {{ $tipo === 'Entrada' ? 'btn-success' : 'btn-danger' }}">
                                <i class="fas fa-save"></i>
                                Registrar movimiento
                            </button>
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
                            Anular movimiento bancario
                        </h5>

                        <button type="button"
                                class="close"
                                wire:click="cerrarModalAnular">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-warning">
                            Solo se puede anular el último movimiento activo de la cuenta bancaria para mantener el saldo correcto.
                        </div>

                        <p>
                            ¿Está seguro de anular el movimiento?
                        </p>

                        <p>
                            <strong>{{ $movimientoAnularCodigo }}</strong>
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