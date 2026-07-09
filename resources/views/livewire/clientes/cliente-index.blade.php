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
            <h3 class="card-title">Listado de clientes</h3>

            <div class="card-tools">
                <button class="btn btn-primary btn-sm" wire:click="create">
                    <i class="fas fa-plus"></i> Nuevo cliente
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <input type="text"
                        class="form-control"
                        placeholder="Buscar por nombre, apellido, teléfono, DNI, RTN o correo..."
                        wire:model.debounce.500ms="search">
                </div>

                <div class="col-md-3">
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
                            <th>Cliente</th>
                            <th>Teléfono</th>
                            <th>DNI</th>
                            <th>Tipo</th>
                            <th>Ubicación</th>
                            <th>Estado</th>
                            <th width="170">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($clientes as $cliente)
                            <tr>
                                <td>
                                    <strong>{{ $cliente->nombre_completo }}</strong><br>
                                    <small>{{ $cliente->correo }}</small>
                                </td>

                                <td>{{ $cliente->telefono_completo }}</td>

                                <td>{{ $cliente->dni_formateado }}</td>

                                <td>
                                    <span class="badge badge-info">
                                        {{ $cliente->tipo_cliente }}
                                    </span>
                                </td>

                                <td>
                                    {{ $cliente->municipio->nombre ?? '' }},
                                    {{ $cliente->departamento->nombre ?? '' }}
                                    <br>
                                    <small>{{ $cliente->direccion_referencia }}</small>
                                </td>

                                <td>
                                    @if ($cliente->activo)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-secondary">Inactivo</span>
                                    @endif
                                </td>

                                <td>
                                    <button class="btn btn-warning btn-xs"
                                            wire:click="edit({{ $cliente->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button class="btn btn-{{ $cliente->activo ? 'secondary' : 'success' }} btn-xs"
                                            wire:click="cambiarEstado({{ $cliente->id }})">
                                        @if ($cliente->activo)
                                            Desactivar
                                        @else
                                            Activar
                                        @endif
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    No hay clientes registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $clientes->links() }}
        </div>
    </div>

    {{-- Modal cliente --}}
    <div wire:ignore.self class="modal fade" id="clienteModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <form wire:submit.prevent="{{ $cliente_id ? 'update' : 'store' }}" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $modalTitle }}</h5>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <h5>Datos personales</h5>
                    <hr>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Primer nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model.defer="primer_nombre">
                            @error('primer_nombre') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-3">
                            <label>Segundo nombre</label>
                            <input type="text" class="form-control" wire:model.defer="segundo_nombre">
                            @error('segundo_nombre') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-3">
                            <label>Primer apellido <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" wire:model.defer="primer_apellido">
                            @error('primer_apellido') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-3">
                            <label>Segundo apellido</label>
                            <input type="text" class="form-control" wire:model.defer="segundo_apellido">
                            @error('segundo_apellido') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>DNI <span class="text-danger">*</span></label>
                            <input type="text"
                                class="form-control"
                                placeholder="0000-0000-00000"
                                maxlength="15"
                                inputmode="numeric"
                                oninput="this.value = formatearDni(this.value)"
                                wire:model.defer="dni">
                            @error('dni') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-3">
                            <label>RTN</label>
                            <input type="text"
                                   class="form-control"
                                   placeholder="0000-0000-000000"
                                   wire:model.defer="rtn">
                            @error('rtn') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-3">
                            <label>Tipo cliente <span class="text-danger">*</span></label>
                            <select class="form-control" wire:model.defer="tipo_cliente">
                                <option value="Natural">Natural</option>
                                <option value="Empresa">Empresa</option>
                                <option value="Institucion">Institución</option>
                                <option value="Mayorista">Mayorista</option>
                                <option value="Corporativo">Corporativo</option>
                            </select>
                            @error('tipo_cliente') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-3">
                            <label>Estado</label>
                            <select class="form-control" wire:model.defer="activo">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                            @error('activo') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <h5 class="mt-3">Contacto</h5>
                    <hr>

                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label>Prefijo</label>
                            <input type="text" class="form-control" wire:model.defer="codigo_pais">
                            @error('codigo_pais') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-4">
                            <label>Teléfono <span class="text-danger">*</span></label>
                           <input type="text"
                                class="form-control"
                                placeholder="0000-0000"
                                maxlength="9"
                                inputmode="numeric"
                                oninput="this.value = formatearTelefono(this.value)"
                                wire:model.defer="telefono">
                            @error('telefono') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>Correo</label>
                            <input type="email" class="form-control" wire:model.defer="correo">
                            @error('correo') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <h5 class="mt-3">Dirección</h5>
                    <hr>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Departamento <span class="text-danger">*</span></label>
                            <select class="form-control" wire:model="departamento_id">
                                <option value="">Seleccione un departamento</option>
                                @foreach ($departamentos as $departamento)
                                    <option value="{{ $departamento->id }}">
                                        {{ $departamento->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('departamento_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>Municipio <span class="text-danger">*</span></label>
                            <select class="form-control" wire:model.defer="municipio_id">
                                <option value="">Seleccione un municipio</option>
                                @foreach ($municipios as $municipio)
                                    <option value="{{ $municipio->id }}">
                                        {{ $municipio->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            @error('municipio_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Referencia de dirección <span class="text-danger">*</span></label>
                        <textarea class="form-control"
                                  rows="2"
                                  placeholder="Ejemplo: Barrio San José, frente a la pulpería..."
                                  wire:model.defer="direccion_referencia"></textarea>
                        @error('direccion_referencia') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="form-group">
                        <label>Notas</label>
                        <textarea class="form-control"
                                  rows="2"
                                  wire:model.defer="notas"></textarea>
                        @error('notas') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-primary">
                        {{ $cliente_id ? 'Actualizar cliente' : 'Guardar cliente' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>