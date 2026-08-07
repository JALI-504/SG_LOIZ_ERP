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
                <i class="fas fa-university"></i>
                Cuentas bancarias
            </h3>

            <div class="card-tools">
                @can('crear cuentas bancarias')
                    <button wire:click="abrirFormulario"
                            class="btn btn-success btn-sm">
                        <i class="fas fa-plus"></i>
                        Nueva cuenta
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
                           placeholder="Buscar por código, banco, cuenta, número o moneda...">
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
                estas cuentas servirán como base para registrar movimientos bancarios y conciliaciones.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Código</th>
                            <th>Banco</th>
                            <th>Cuenta</th>
                            <th>Número</th>
                            <th>Tipo</th>
                            <th>Moneda</th>
                            <th>Saldo inicial</th>
                            <th>Saldo actual</th>
                            <th>Estado</th>
                            <th width="130">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($cuentas as $cuenta)
                            <tr>
                                <td>
                                    <strong>{{ $cuenta->codigo }}</strong>
                                </td>

                                <td>
                                    {{ $cuenta->banco }}
                                </td>

                                <td>
                                    {{ $cuenta->nombre_cuenta }}

                                    @if ($cuenta->observacion)
                                        <br>
                                        <small class="text-muted">
                                            {{ $cuenta->observacion }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    {{ $cuenta->numero_cuenta ?: 'No registrada' }}
                                </td>

                                <td>
                                    {{ $cuenta->tipo_cuenta ?: 'No definido' }}
                                </td>

                                <td>
                                    <span class="badge badge-primary">
                                        {{ $cuenta->moneda }}
                                    </span>
                                </td>

                                <td>
                                    L {{ number_format($cuenta->saldo_inicial, 2) }}
                                </td>

                                <td>
                                    <strong>
                                        L {{ number_format($cuenta->saldo_actual, 2) }}
                                    </strong>
                                </td>

                                <td>
                                    @if ($cuenta->activo)
                                        <span class="badge badge-success">
                                            Activa
                                        </span>
                                    @else
                                        <span class="badge badge-danger">
                                            Inactiva
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @can('editar cuentas bancarias')
                                        <button wire:click="editar({{ $cuenta->id }})"
                                                class="btn btn-warning btn-sm"
                                                title="Editar cuenta">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    @endcan

                                    @can('eliminar cuentas bancarias')
                                        <button wire:click="cambiarEstado({{ $cuenta->id }})"
                                                class="btn {{ $cuenta->activo ? 'btn-danger' : 'btn-success' }} btn-sm"
                                                title="{{ $cuenta->activo ? 'Desactivar' : 'Reactivar' }}">
                                            @if ($cuenta->activo)
                                                <i class="fas fa-ban"></i>
                                            @else
                                                <i class="fas fa-check"></i>
                                            @endif
                                        </button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted">
                                    No hay cuentas bancarias registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $cuentas->links() }}
        </div>
    </div>

    @if ($mostrarFormulario)
        <div class="modal fade show"
             style="display: block;"
             tabindex="-1"
             role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form wire:submit.prevent="guardar">
                        <div class="modal-header {{ $modoEdicion ? 'bg-warning' : 'bg-success' }}">
                            <h5 class="modal-title">
                                @if ($modoEdicion)
                                    Editar cuenta bancaria
                                @else
                                    Nueva cuenta bancaria
                                @endif
                            </h5>

                            <button type="button"
                                    class="close"
                                    wire:click="cerrarFormulario">
                                <span>&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Banco</label>
                                        <input type="text"
                                               wire:model.defer="banco"
                                               class="form-control @error('banco') is-invalid @enderror"
                                               placeholder="Ejemplo: BAC, Atlántida, Ficohsa">

                                        @error('banco')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Nombre de la cuenta</label>
                                        <input type="text"
                                               wire:model.defer="nombre_cuenta"
                                               class="form-control @error('nombre_cuenta') is-invalid @enderror"
                                               placeholder="Ejemplo: Cuenta principal LOIZ">

                                        @error('nombre_cuenta')
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
                                        <label>Número de cuenta</label>
                                        <input type="text"
                                               wire:model.defer="numero_cuenta"
                                               class="form-control @error('numero_cuenta') is-invalid @enderror"
                                               placeholder="Opcional">

                                        @error('numero_cuenta')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Tipo de cuenta</label>
                                        <select wire:model.defer="tipo_cuenta"
                                                class="form-control @error('tipo_cuenta') is-invalid @enderror">
                                            <option value="Cuenta de ahorro">Cuenta de ahorro</option>
                                            <option value="Cuenta corriente">Cuenta corriente</option>
                                            <option value="Tarjeta de crédito">Tarjeta de crédito</option>
                                            <option value="Billetera electrónica">Billetera electrónica</option>
                                            <option value="Otra">Otra</option>
                                        </select>

                                        @error('tipo_cuenta')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Moneda</label>
                                        <select wire:model.defer="moneda"
                                                class="form-control @error('moneda') is-invalid @enderror">
                                            <option value="HNL">HNL - Lempiras</option>
                                            <option value="USD">USD - Dólares</option>
                                        </select>

                                        @error('moneda')
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
                                        <label>Saldo inicial</label>
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               wire:model.defer="saldo_inicial"
                                               class="form-control @error('saldo_inicial') is-invalid @enderror">

                                        @error('saldo_inicial')
                                            <span class="invalid-feedback">
                                                {{ $message }}
                                            </span>
                                        @enderror

                                        @if ($modoEdicion)
                                            <small class="text-muted">
                                                Al cambiar el saldo inicial, el saldo actual se ajustará por la diferencia.
                                            </small>
                                        @endif
                                    </div>
                                </div>

                                <div class="col-md-8">
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
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button"
                                    class="btn btn-secondary"
                                    wire:click="cerrarFormulario">
                                Cancelar
                            </button>

                            <button type="submit"
                                    class="btn {{ $modoEdicion ? 'btn-warning' : 'btn-success' }}">
                                <i class="fas fa-save"></i>
                                Guardar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal-backdrop fade show"></div>
    @endif
</div>