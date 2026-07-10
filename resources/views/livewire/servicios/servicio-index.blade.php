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
            <h3 class="card-title">Listado de servicios</h3>

            <div class="card-tools">
                <button class="btn btn-primary btn-sm" wire:click="create">
                    <i class="fas fa-plus"></i> Nuevo servicio
                </button>
            </div>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-5">
                    <input type="text"
                           class="form-control"
                           placeholder="Buscar por código, nombre, tipo, tamaño o color..."
                           wire:model.debounce.500ms="search">
                </div>

                <div class="col-md-3">
                    <select class="form-control" wire:model="filtroTipo">
                        <option value="todos">Todos los tipos</option>
                        @foreach ($tiposServicio as $tipo)
                            <option value="{{ $tipo }}">{{ $tipo }}</option>
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
                            <th>Código</th>
                            <th>Servicio</th>
                            <th>Tipo</th>
                            <th>Características</th>
                            <th>Costo</th>
                            <th>Precio</th>
                            <th>Utilidad</th>
                            <th>Estado</th>
                            <th width="170">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($servicios as $servicio)
                            <tr>
                                <td>
                                    <strong>{{ $servicio->codigo }}</strong>
                                </td>

                                <td>
                                    <strong>{{ $servicio->nombre }}</strong><br>
                                    <small>{{ $servicio->descripcion }}</small>
                                </td>

                                <td>
                                    <span class="badge badge-info">
                                        {{ $servicio->tipo_servicio }}
                                    </span>
                                </td>

                                <td>
                                    <small>
                                        Tamaño: {{ $servicio->tamano_papel }} <br>
                                        Color: {{ $servicio->color }} <br>
                                        Caras: {{ $servicio->caras }} <br>
                                        Unidad: {{ $servicio->unidad_cobro }}
                                    </small>
                                </td>

                                <td>
                                    L {{ number_format($servicio->costo_unitario, 2) }}
                                </td>

                                <td>
                                    <strong>
                                        L {{ number_format($servicio->precio_unitario, 2) }}
                                    </strong>
                                </td>

                                <td>
                                    @php
                                        $utilidad = $servicio->precio_unitario - $servicio->costo_unitario;
                                        $margen = $servicio->costo_unitario > 0
                                            ? (($servicio->precio_unitario - $servicio->costo_unitario) / $servicio->costo_unitario) * 100
                                            : 0;
                                    @endphp

                                    <strong>L {{ number_format($utilidad, 2) }}</strong><br>
                                    <small>{{ number_format($margen, 2) }}%</small>
                                </td>

                                <td>
                                    @if ($servicio->activo)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-secondary">Inactivo</span>
                                    @endif
                                </td>

                               <td>
                                    <button class="btn btn-warning btn-xs"
                                            wire:click="edit({{ $servicio->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <a href="{{ route('servicios.insumos', $servicio->id) }}"
                                    class="btn btn-info btn-xs">
                                        Insumos
                                    </a>

                                    <button class="btn btn-{{ $servicio->activo ? 'secondary' : 'success' }} btn-xs"
                                            wire:click="cambiarEstado({{ $servicio->id }})">
                                        @if ($servicio->activo)
                                            Desactivar
                                        @else
                                            Activar
                                        @endif
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">
                                    No hay servicios registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $servicios->links() }}
        </div>
    </div>

    {{-- Modal servicio --}}
    <div wire:ignore.self class="modal fade" id="servicioModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <form wire:submit.prevent="{{ $servicio_id ? 'update' : 'store' }}" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $modalTitle }}</h5>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <h5>Datos del servicio</h5>
                    <hr>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Código <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   placeholder="Ej: IMP-CAR-BN"
                                   wire:model.defer="codigo">
                            @error('codigo') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-8">
                            <label>Nombre del servicio <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   placeholder="Ej: Impresión carta blanco y negro"
                                   wire:model.defer="nombre">
                            @error('nombre') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Tipo de servicio <span class="text-danger">*</span></label>
                            <select class="form-control" wire:model.defer="tipo_servicio">
                                @foreach ($tiposServicio as $tipo)
                                    <option value="{{ $tipo }}">{{ $tipo }}</option>
                                @endforeach
                            </select>
                            @error('tipo_servicio') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-4">
                            <label>Tamaño de papel</label>
                            <select class="form-control" wire:model.defer="tamano_papel">
                                @foreach ($tamanosPapel as $tamano)
                                    <option value="{{ $tamano }}">{{ $tamano }}</option>
                                @endforeach
                            </select>
                            @error('tamano_papel') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-4">
                            <label>Color</label>
                            <select class="form-control" wire:model.defer="color">
                                @foreach ($colores as $colorOpcion)
                                    <option value="{{ $colorOpcion }}">{{ $colorOpcion }}</option>
                                @endforeach
                            </select>
                            @error('color') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Caras</label>
                            <select class="form-control" wire:model.defer="caras">
                                @foreach ($carasOpciones as $cara)
                                    <option value="{{ $cara }}">{{ $cara }}</option>
                                @endforeach
                            </select>
                            @error('caras') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-4">
                            <label>Unidad de cobro</label>
                            <select class="form-control" wire:model.defer="unidad_cobro">
                                @foreach ($unidadesCobro as $unidad)
                                    <option value="{{ $unidad }}">{{ $unidad }}</option>
                                @endforeach
                            </select>
                            @error('unidad_cobro') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-4">
                            <label>Estado</label>
                            <select class="form-control" wire:model.defer="activo">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                            @error('activo') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <h5 class="mt-3">Costos y precios</h5>
                    <hr>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Costo unitario <span class="text-danger">*</span></label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model.defer="costo_unitario">
                            @error('costo_unitario') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>Precio unitario <span class="text-danger">*</span></label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model.defer="precio_unitario">
                            @error('precio_unitario') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Descripción / observación</label>
                        <textarea class="form-control"
                                  rows="2"
                                  placeholder="Ej: Servicio para impresiones rápidas tamaño carta..."
                                  wire:model.defer="descripcion"></textarea>
                        @error('descripcion') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-primary">
                        {{ $servicio_id ? 'Actualizar servicio' : 'Guardar servicio' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>