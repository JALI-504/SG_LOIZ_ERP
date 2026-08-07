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

    <div class="mb-3">
        @can('crear activos fijos')
            <button type="button"
                    class="btn btn-success btn-sm"
                    wire:click="crear">
                <i class="fas fa-plus"></i>
                Nuevo activo fijo
            </button>
        @endcan

        <a href="{{ route('activos-fijos.categorias') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-tags"></i>
            Categorías
        </a>
    </div>

    {{-- Resumen --}}
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ number_format($totalActivos, 0) }}</h4>
                    <p>Total activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-laptop-house"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>L {{ number_format($totalValorCompra, 2) }}</h4>
                    <p>Valor de compra activo</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>L {{ number_format($totalDepreciacionAcumulada, 2) }}</h4>
                    <p>Depreciación acumulada</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h4>L {{ number_format($totalValorLibros, 2) }}</h4>
                    <p>Valor en libros</p>
                </div>
                <div class="icon">
                    <i class="fas fa-book"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros</h3>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label>Buscar</label>
                    <input type="text"
                           class="form-control"
                           placeholder="Código, nombre, serie, responsable..."
                           wire:model.debounce.500ms="search">
                </div>

                <div class="col-md-3">
                    <label>Categoría</label>
                    <select class="form-control" wire:model="filtroCategoria">
                        <option value="todos">Todas</option>

                        @foreach ($categoriasFiltro as $categoriaFiltro)
                            <option value="{{ $categoriaFiltro->id }}">
                                {{ $categoriaFiltro->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label>Estado</label>
                    <select class="form-control" wire:model="filtroEstado">
                        <option value="todos">Todos</option>
                        <option value="Activo">Activo</option>
                        <option value="En mantenimiento">En mantenimiento</option>
                        <option value="Dañado">Dañado</option>
                        <option value="Vendido">Vendido</option>
                        <option value="Dado de baja">Dado de baja</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label>Mostrar</label>
                    <select class="form-control" wire:model="perPage">
                        <option value="</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label>Mostrar</label>
                    <select class="form-control" wire:model="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de activos fijos</h3>
        </div>

        <div class="card-body">
            <div class="alert alert-info">
                Aquí puedes registrar los bienes permanentes del negocio y controlar su depreciación.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Código</th>
                            <th>Activo</th>
                            <th>Categoría</th>
                            <th>Valor compra</th>
                            <th>Dep. mensual</th>
                            <th>Dep. acumulada</th>
                            <th>Valor libros</th>
                            <th>Estado</th>
                            <th width="180">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($activos as $activo)
                            <tr class="{{ $activo->estado === 'Dado de baja' ? 'table-secondary' : '' }}">
                                <td>
                                    <strong>{{ $activo->codigo }}</strong>

                                    @if ($activo->numero_serie)
                                        <br>
                                        <small>Serie: {{ $activo->numero_serie }}</small>
                                    @endif
                                </td>

                                <td>
                                    <strong>{{ $activo->nombre }}</strong>

                                    @if ($activo->marca || $activo->modelo)
                                        <br>
                                        <small>
                                            {{ $activo->marca }}
                                            {{ $activo->modelo }}
                                        </small>
                                    @endif

                                    @if ($activo->ubicacion)
                                        <br>
                                        <small class="text-muted">
                                            Ubicación: {{ $activo->ubicacion }}
                                        </small>
                                    @endif

                                    @if ($activo->responsable)
                                        <br>
                                        <small class="text-muted">
                                            Responsable: {{ $activo->responsable }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    @if ($activo->categoriaActivo)
                                        <span class="badge badge-info">
                                            {{ $activo->categoriaActivo->nombre }}
                                        </span>

                                        @if (!$activo->categoriaActivo->depreciable)
                                            <br>
                                            <span class="badge badge-secondary mt-1">No depreciable</span>
                                        @endif
                                    @else
                                        <span class="text-muted">Sin categoría</span>
                                    @endif
                                </td>

                                <td>
                                    L {{ number_format($activo->valor_compra, 2) }}
                                </td>

                                <td>
                                    L {{ number_format($activo->depreciacion_mensual, 2) }}
                                </td>

                                <td>
                                    L {{ number_format($activo->depreciacion_acumulada, 2) }}
                                </td>

                                <td>
                                    <strong>
                                        L {{ number_format($activo->valor_en_libros, 2) }}
                                    </strong>
                                </td>

                                <td>
                                    <span class="badge badge-{{ $activo->estado_clase }}">
                                        {{ $activo->estado }}
                                    </span>

                                    @if ($activo->fecha_baja)
                                        <br>
                                        <small class="text-muted">
                                            Baja: {{ \Carbon\Carbon::parse($activo->fecha_baja)->format('d/m/Y') }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    @if ($activo->estado !== 'Dado de baja')
                                        @can('editar activos fijos')
                                            <button type="button"
                                                    class="btn btn-primary btn-xs"
                                                    wire:click="editar({{ $activo->id }})">
                                                Editar
                                            </button>
                                        @endcan

                                        @can('anular activos fijos')
                                            <button type="button"
                                                    class="btn btn-danger btn-xs"
                                                    wire:click="abrirBaja({{ $activo->id }})">
                                                Baja
                                            </button>
                                        @endcan
                                    @else
                                        @can('anular activos fijos')
                                            <button type="button"
                                                    class="btn btn-warning btn-xs"
                                                    onclick="confirm('¿Desea reactivar este activo fijo?') || event.stopImmediatePropagation()"
                                                    wire:click="reactivar({{ $activo->id }})">
                                                Reactivar
                                            </button>
                                        @endcan
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">
                                    No hay activos fijos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $activos->links() }}
        </div>
    </div>

    {{-- Modal crear/editar --}}
    @if ($mostrarModal)
        <div class="modal fade show"
             style="display: block;"
             tabindex="-1"
             role="dialog">
            <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <form wire:submit.prevent="guardar">
                        <div class="modal-header">
                            <h5 class="modal-title">
                                {{ $activo_id ? 'Editar activo fijo' : 'Nuevo activo fijo' }}
                            </h5>

                            <button type="button"
                                    class="close"
                                    wire:click="cerrarModal">
                                <span>&times;</span>
                            </button>
                        </div>

                        <div class="modal-body" style="max-height: 75vh; overflow-y: auto;">
                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>Categoría <span class="text-danger">*</span></label>
                                    <select class="form-control @error('categoria_activo_id') is-invalid @enderror"
                                            wire:model="categoria_activo_id">
                                        <option value="">Seleccione</option>

                                        @foreach ($categorias as $categoria)
                                            <option value="{{ $categoria->id }}">
                                                {{ $categoria->nombre }}
                                                -
                                                AF-{{ $categoria->prefijo_codigo ?? 'GEN' }}
                                            </option>
                                        @endforeach
                                    </select>

                                    @error('categoria_activo_id')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group col-md-8">
                                    <label>Nombre del activo <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('nombre') is-invalid @enderror"
                                           wire:model.defer="nombre"
                                           placeholder="Ej: Laptop Dell Inspiron, impresora Epson...">

                                    @error('nombre')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            @if ($categoriaSeleccionada)
                                <div class="alert alert-secondary">
                                    <strong>Código automático:</strong>
                                    AF-{{ $categoriaSeleccionada->prefijo_codigo ?? 'GEN' }}-000001

                                    <br>

                                    <small>
                                        El número final se asignará automáticamente al guardar según el último activo registrado en esta categoría.
                                    </small>

                                    <hr>

                                    <strong>Requisitos de esta categoría:</strong>

                                    @if ($categoriaSeleccionada->requiere_numero_serie)
                                        <span class="badge badge-info">Número de serie obligatorio</span>
                                    @endif

                                    @if ($categoriaSeleccionada->requiere_marca_modelo)
                                        <span class="badge badge-primary">Marca/modelo obligatorio</span>
                                    @endif

                                    @if ($categoriaSeleccionada->requiere_responsable)
                                        <span class="badge badge-warning">Responsable obligatorio</span>
                                    @endif

                                    @if (!$categoriaSeleccionada->requiere_numero_serie && !$categoriaSeleccionada->requiere_marca_modelo && !$categoriaSeleccionada->requiere_responsable)
                                        <span class="badge badge-secondary">Sin requisitos especiales</span>
                                    @endif
                                </div>
                            @endif

                            <div class="form-group">
                                <label>Descripción</label>
                                <textarea class="form-control @error('descripcion') is-invalid @enderror"
                                          rows="2"
                                          wire:model.defer="descripcion"></textarea>

                                @error('descripcion')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label>Fecha compra</label>
                                    <input type="date"
                                           class="form-control @error('fecha_compra') is-invalid @enderror"
                                           wire:model.defer="fecha_compra">

                                    @error('fecha_compra')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group col-md-3">
                                    <label>Fecha inicio uso</label>
                                    <input type="date"
                                           class="form-control @error('fecha_inicio_uso') is-invalid @enderror"
                                           wire:model.defer="fecha_inicio_uso">

                                    @error('fecha_inicio_uso')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group col-md-3">
                                    <label>Valor compra <span class="text-danger">*</span></label>
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           class="form-control @error('valor_compra') is-invalid @enderror"
                                           wire:model.defer="valor_compra">

                                    @error('valor_compra')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group col-md-3">
                                    <label>Valor residual</label>
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           class="form-control @error('valor_residual') is-invalid @enderror"
                                           wire:model.defer="valor_residual">

                                    @error('valor_residual')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <label>Vida útil meses</label>
                                    <input type="number"
                                           min="1"
                                           max="600"
                                           class="form-control @error('vida_util_meses') is-invalid @enderror"
                                           wire:model.defer="vida_util_meses">

                                    @error('vida_util_meses')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group col-md-3">
                                    <label>Depreciación acumulada</label>
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           class="form-control @error('depreciacion_acumulada') is-invalid @enderror"
                                           wire:model.defer="depreciacion_acumulada">

                                    @error('depreciacion_acumulada')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group col-md-3">
                                    <label>Estado</label>
                                    <select class="form-control @error('estado') is-invalid @enderror"
                                            wire:model.defer="estado">
                                        <option value="Activo">Activo</option>
                                        <option value="En mantenimiento">En mantenimiento</option>
                                        <option value="Dañado">Dañado</option>
                                        <option value="Vendido">Vendido</option>
                                    </select>

                                    @error('estado')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group col-md-3">
                                    <label>Ubicación</label>
                                    <input type="text"
                                           class="form-control @error('ubicacion') is-invalid @enderror"
                                           wire:model.defer="ubicacion"
                                           placeholder="Ej: Oficina principal">

                                    @error('ubicacion')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="card card-outline card-info">
                                <div class="card-header">
                                    <h3 class="card-title">
                                        Identificación física del activo
                                    </h3>
                                </div>

                                <div class="card-body">
                                    <div class="form-row">
                                        <div class="form-group col-md-4">
                                            <label>
                                                Número de serie

                                                @if ($categoriaSeleccionada && $categoriaSeleccionada->requiere_numero_serie)
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </label>

                                            <input type="text"
                                                   class="form-control @error('numero_serie') is-invalid @enderror"
                                                   wire:model.defer="numero_serie"
                                                   placeholder="Ej: SN123456789">

                                            @error('numero_serie')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label>
                                                Marca

                                                @if ($categoriaSeleccionada && $categoriaSeleccionada->requiere_marca_modelo)
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </label>

                                            <input type="text"
                                                   class="form-control @error('marca') is-invalid @enderror"
                                                   wire:model.defer="marca"
                                                   placeholder="Ej: Dell, Epson, HP...">

                                            @error('marca')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>

                                        <div class="form-group col-md-4">
                                            <label>
                                                Modelo

                                                @if ($categoriaSeleccionada && $categoriaSeleccionada->requiere_marca_modelo)
                                                    <span class="text-danger">*</span>
                                                @endif
                                            </label>

                                            <input type="text"
                                                   class="form-control @error('modelo') is-invalid @enderror"
                                                   wire:model.defer="modelo"
                                                   placeholder="Ej: Inspiron 15, L3250...">

                                            @error('modelo')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <label>
                                        Responsable

                                        @if ($categoriaSeleccionada && $categoriaSeleccionada->requiere_responsable)
                                            <span class="text-danger">*</span>
                                        @endif
                                    </label>

                                    <input type="text"
                                           class="form-control @error('responsable') is-invalid @enderror"
                                           wire:model.defer="responsable"
                                           placeholder="Persona responsable del activo">

                                    @error('responsable')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group col-md-4">
                                    <label>Proveedor</label>
                                    <input type="text"
                                           class="form-control @error('proveedor') is-invalid @enderror"
                                           wire:model.defer="proveedor"
                                           placeholder="Proveedor o persona que vendió">

                                    @error('proveedor')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="form-group col-md-4">
                                    <label>Documento compra</label>
                                    <input type="text"
                                           class="form-control @error('documento_compra') is-invalid @enderror"
                                           wire:model.defer="documento_compra"
                                           placeholder="Factura, recibo, comprobante...">

                                    @error('documento_compra')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Observación</label>
                                <textarea class="form-control @error('observacion') is-invalid @enderror"
                                          rows="2"
                                          wire:model.defer="observacion"></textarea>

                                @error('observacion')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="alert alert-secondary mb-0">
                                El sistema calculará automáticamente el valor depreciable, la depreciación mensual y el valor en libros al guardar.
                                Para terrenos o categorías no depreciables, el valor en libros se mantendrá igual al valor de compra.
                            </div>
                        </div>

                        <div class="modal-footer bg-white">
                            <button type="button"
                                    class="btn btn-secondary"
                                    wire:click="cerrarModal">
                                Cancelar
                            </button>

                            <button type="submit"
                                    class="btn btn-success">
                                {{ $activo_id ? 'Actualizar' : 'Guardar' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal-backdrop fade show"></div>
    @endif

    {{-- Modal baja --}}
    @if ($mostrarModalBaja)
        <div class="modal fade show"
             style="display: block;"
             tabindex="-1"
             role="dialog">
            <div class="modal-dialog modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <form wire:submit.prevent="confirmarBaja">
                        <div class="modal-header">
                            <h5 class="modal-title">Dar de baja activo fijo</h5>

                            <button type="button"
                                    class="close"
                                    wire:click="cerrarModalBaja">
                                <span>&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <div class="alert alert-warning">
                                Esta acción marcará el activo como dado de baja. No se eliminará del sistema.
                            </div>

                            <div class="form-group">
                                <label>Motivo de baja</label>
                                <textarea class="form-control"
                                          rows="3"
                                          wire:model.defer="motivo_baja_form"
                                          placeholder="Ej: Daño irreparable, obsoleto, vendido, extraviado..."></textarea>
                            </div>
                        </div>

                        <div class="modal-footer bg-white">
                            <button type="button"
                                    class="btn btn-secondary"
                                    wire:click="cerrarModalBaja">
                                Cancelar
                            </button>

                            <button type="submit"
                                    class="btn btn-danger">
                                Confirmar baja
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal-backdrop fade show"></div>
    @endif
</div>