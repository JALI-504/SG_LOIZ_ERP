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
        <a href="{{ route('activos-fijos.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-laptop-house"></i>
            Ver activos fijos
        </a>

        <a href="{{ route('activos-fijos.categorias') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-tags"></i>
            Categorías
        </a>
    </div>

    {{-- Generar depreciaciones --}}
    <div class="card">
        <div class="card-header bg-primary">
            <h3 class="card-title">
                <i class="fas fa-chart-line"></i>
                Generar depreciaciones mensuales
            </h3>
        </div>

        <div class="card-body">
            <div class="alert alert-info">
                Selecciona el período a depreciar. El sistema solo tomará activos depreciables, activos vigentes y que todavía tengan valor pendiente por depreciar.
            </div>

            <div class="row">
                <div class="col-md-3">
                    <label>Período <span class="text-danger">*</span></label>
                    <input type="month"
                           class="form-control @error('periodo') is-invalid @enderror"
                           wire:model="periodo">

                    @error('periodo')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label>Fecha depreciación <span class="text-danger">*</span></label>
                    <input type="date"
                           class="form-control @error('fecha_depreciacion') is-invalid @enderror"
                           wire:model.defer="fecha_depreciacion">

                    @error('fecha_depreciacion')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-3">
                    <label>Activos pendientes del período</label>
                    <div class="form-control bg-light">
                        {{ number_format($activosPendientesPeriodo, 0) }}
                    </div>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    @can('generar depreciaciones activos')
                        <button type="button"
                                class="btn btn-success btn-block"
                                onclick="confirm('¿Desea generar las depreciaciones de este período?') || event.stopImmediatePropagation()"
                                wire:click="generarDepreciaciones">
                            <i class="fas fa-cogs"></i>
                            Generar depreciaciones
                        </button>
                    @endcan
                </div>
            </div>

            <div class="form-group mt-3 mb-0">
                <label>Observación</label>
                <textarea class="form-control"
                          rows="2"
                          wire:model.defer="observacion"
                          placeholder="Observación opcional para las depreciaciones generadas."></textarea>
            </div>
        </div>
    </div>

    {{-- Resumen --}}
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ number_format($totalRegistros, 0) }}</h4>
                    <p>Registros encontrados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-list"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>L {{ number_format($totalDepreciado, 2) }}</h4>
                    <p>Total depreciado activo</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h4>{{ number_format($cantidadAnuladas, 0) }}</h4>
                    <p>Depreciaciones anuladas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-ban"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4>L {{ number_format($totalAnulado, 2) }}</h4>
                    <p>Monto anulado</p>
                </div>
                <div class="icon">
                    <i class="fas fa-undo"></i>
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
                           placeholder="Código, activo, serie, período..."
                           wire:model.debounce.500ms="search">
                </div>

                <div class="col-md-3">
                    <label>Período</label>
                    <input type="month"
                           class="form-control"
                           wire:model="filtroPeriodo">
                </div>

                <div class="col-md-2">
                    <label>Estado</label>
                    <select class="form-control" wire:model="filtroEstado">
                        <option value="todos">Todos</option>
                        <option value="Registrada">Registrada</option>
                        <option value="Anulada">Anulada</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label>Mostrar</label>
                    <select class="form-control" wire:model="perPage">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>

                <div class="col-md-1 d-flex align-items-end">
                    <button type="button"
                            class="btn btn-secondary btn-block"
                            wire:click="limpiarFiltros">
                        <i class="fas fa-broom"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabla --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Historial de depreciaciones</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Depreciación</th>
                            <th>Activo</th>
                            <th>Período</th>
                            <th>Monto</th>
                            <th>Dep. acumulada</th>
                            <th>Valor en libros</th>
                            <th>Estado</th>
                            <th width="120">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($depreciaciones as $depreciacion)
                            <tr class="{{ $depreciacion->estado === 'Anulada' ? 'table-secondary' : '' }}">
                                <td>
                                    <strong>{{ $depreciacion->codigo }}</strong><br>
                                    <small>
                                        {{ \Carbon\Carbon::parse($depreciacion->fecha_depreciacion)->format('d/m/Y') }}
                                    </small>
                                </td>

                                <td>
                                    @if ($depreciacion->activoFijo)
                                        <strong>{{ $depreciacion->activoFijo->codigo }}</strong><br>
                                        {{ $depreciacion->activoFijo->nombre }}

                                        @if ($depreciacion->activoFijo->categoriaActivo)
                                            <br>
                                            <span class="badge badge-info">
                                                {{ $depreciacion->activoFijo->categoriaActivo->nombre }}
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-muted">Activo no encontrado</span>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge badge-primary">
                                        {{ $depreciacion->periodo }}
                                    </span>
                                </td>

                                <td>
                                    @if ($depreciacion->estado === 'Anulada')
                                        <span class="text-muted">
                                            <del>L {{ number_format($depreciacion->monto, 2) }}</del>
                                        </span>
                                    @else
                                        <strong class="text-danger">
                                            L {{ number_format($depreciacion->monto, 2) }}
                                        </strong>
                                    @endif
                                </td>

                                <td>
                                    <small>Anterior:</small>
                                    L {{ number_format($depreciacion->depreciacion_acumulada_anterior, 2) }}
                                    <br>
                                    <small>Nueva:</small>
                                    <strong>
                                        L {{ number_format($depreciacion->depreciacion_acumulada_nueva, 2) }}
                                    </strong>
                                </td>

                                <td>
                                    <small>Anterior:</small>
                                    L {{ number_format($depreciacion->valor_en_libros_anterior, 2) }}
                                    <br>
                                    <small>Nuevo:</small>
                                    <strong>
                                        L {{ number_format($depreciacion->valor_en_libros_nuevo, 2) }}
                                    </strong>
                                </td>

                                <td>
                                    @if ($depreciacion->estado === 'Registrada')
                                        <span class="badge badge-success">Registrada</span>
                                    @else
                                        <span class="badge badge-secondary">Anulada</span>

                                        @if ($depreciacion->fecha_anulacion)
                                            <br>
                                            <small>
                                                {{ \Carbon\Carbon::parse($depreciacion->fecha_anulacion)->format('d/m/Y H:i') }}
                                            </small>
                                        @endif

                                        @if ($depreciacion->motivo_anulacion)
                                            <br>
                                            <small class="text-danger">
                                                {{ $depreciacion->motivo_anulacion }}
                                            </small>
                                        @endif
                                    @endif
                                </td>

                                <td>
                                    @can('anular depreciaciones activos')
                                        @if ($depreciacion->estado === 'Registrada')
                                            <button type="button"
                                                    class="btn btn-danger btn-xs"
                                                    wire:click="abrirAnular({{ $depreciacion->id }})">
                                                Anular
                                            </button>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    No hay depreciaciones registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $depreciaciones->links() }}
        </div>
    </div>

    {{-- Modal anular --}}
    @if ($mostrarModalAnular)
        <div class="modal fade show"
             style="display: block;"
             tabindex="-1"
             role="dialog">
            <div class="modal-dialog modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <form wire:submit.prevent="confirmarAnular">
                        <div class="modal-header">
                            <h5 class="modal-title">Anular depreciación</h5>

                            <button type="button"
                                    class="close"
                                    wire:click="cerrarModalAnular">
                                <span>&times;</span>
                            </button>
                        </div>

                        <div class="modal-body">
                            <div class="alert alert-warning">
                                Al anular esta depreciación, el sistema reversará la depreciación acumulada y el valor en libros del activo.
                            </div>

                            <div class="form-group">
                                <label>Motivo de anulación</label>
                                <textarea class="form-control"
                                          rows="3"
                                          wire:model.defer="motivo_anulacion"
                                          placeholder="Ej: Depreciación generada por error."></textarea>
                            </div>

                            <small class="text-muted">
                                Por seguridad, solo se puede anular la última depreciación registrada de cada activo.
                            </small>
                        </div>

                        <div class="modal-footer bg-white">
                            <button type="button"
                                    class="btn btn-secondary"
                                    wire:click="cerrarModalAnular">
                                Cancelar
                            </button>

                            <button type="submit"
                                    class="btn btn-danger">
                                Confirmar anulación
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal-backdrop fade show"></div>
    @endif
</div>