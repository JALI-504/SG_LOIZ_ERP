<div>
    <div class="mb-3">
        <a href="{{ route('activos-fijos.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-laptop-house"></i>
            Ver activos fijos
        </a>

        <a href="{{ route('activos-fijos.depreciaciones') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-chart-line"></i>
            Depreciaciones
        </a>

        <button type="button"
                class="btn btn-outline-secondary btn-sm"
                wire:click="limpiarFiltros">
            <i class="fas fa-broom"></i>
            Limpiar filtros
        </button>
    </div>

    {{-- Filtros --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Filtros del reporte</h3>
        </div>

        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <label>Buscar</label>
                    <input type="text"
                           class="form-control"
                           placeholder="Código, activo, serie, responsable..."
                           wire:model.debounce.500ms="search">
                </div>

                <div class="col-md-2">
                    <label>Categoría</label>
                    <select class="form-control" wire:model="categoria_id">
                        <option value="todos">Todas</option>

                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}">
                                {{ $categoria->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label>Estado</label>
                    <select class="form-control" wire:model="estado">
                        <option value="todos">Todos</option>
                        <option value="Activo">Activo</option>
                        <option value="En mantenimiento">En mantenimiento</option>
                        <option value="Dañado">Dañado</option>
                        <option value="Vendido">Vendido</option>
                        <option value="Dado de baja">Dado de baja</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label>Compra desde</label>
                    <input type="date"
                           class="form-control"
                           wire:model="fecha_desde">
                </div>

                <div class="col-md-2">
                    <label>Compra hasta</label>
                    <input type="date"
                           class="form-control"
                           wire:model="fecha_hasta">
                </div>
            </div>
        </div>
    </div>

    {{-- Resumen general --}}
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ number_format($totalActivos, 0) }}</h4>
                    <p>Total activos filtrados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-list"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>{{ number_format($totalActivosVigentes, 0) }}</h4>
                    <p>Activos vigentes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h4>{{ number_format($totalBajas, 0) }}</h4>
                    <p>Dados de baja</p>
                </div>
                <div class="icon">
                    <i class="fas fa-ban"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h4>{{ number_format($totalVendidos, 0) }}</h4>
                    <p>Vendidos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Resumen financiero --}}
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>L {{ number_format($valorCompraTotal, 2) }}</h4>
                    <p>Valor compra vigente</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>L {{ number_format($depreciacionAcumuladaTotal, 2) }}</h4>
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
                    <h4>L {{ number_format($valorLibrosTotal, 2) }}</h4>
                    <p>Valor en libros vigente</p>
                </div>
                <div class="icon">
                    <i class="fas fa-book"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4>L {{ number_format($valorRecuperadoTotal, 2) }}</h4>
                    <p>Valor recuperado</p>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Resumen por categoría --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Resumen por categoría vigente</h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Categoría</th>
                            <th>Cantidad</th>
                            <th>Valor compra</th>
                            <th>Depreciación acumulada</th>
                            <th>Valor en libros</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($resumenCategorias as $resumen)
                            <tr>
                                <td>
                                    {{ $resumen->categoriaActivo->nombre ?? 'Sin categoría' }}
                                </td>
                                <td>{{ number_format($resumen->cantidad, 0) }}</td>
                                <td>L {{ number_format($resumen->valor_compra, 2) }}</td>
                                <td>L {{ number_format($resumen->depreciacion_acumulada, 2) }}</td>
                                <td>
                                    <strong>
                                        L {{ number_format($resumen->valor_en_libros, 2) }}
                                    </strong>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">
                                    No hay información para resumir.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Detalle --}}
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Detalle de activos fijos</h3>

            <div class="card-tools">
                <select class="form-control form-control-sm" wire:model="perPage">
                    <option value="10">Mostrar 10</option>
                    <option value="25">Mostrar 25</option>
                    <option value="50">Mostrar 50</option>
                    <option value="100">Mostrar 100</option>
                </select>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Código</th>
                            <th>Activo</th>
                            <th>Categoría</th>
                            <th>Responsable</th>
                            <th>Ubicación</th>
                            <th>Valor compra</th>
                            <th>Dep. acum.</th>
                            <th>Valor libros</th>
                            <th>Estado</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($activos as $activo)
                            <tr class="{{ in_array($activo->estado, ['Dado de baja', 'Vendido']) ? 'table-secondary' : '' }}">
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
                                </td>

                                <td>
                                    {{ $activo->categoriaActivo->nombre ?? 'Sin categoría' }}
                                </td>

                                <td>
                                    {{ $activo->responsable ?? 'Sin responsable' }}
                                </td>

                                <td>
                                    {{ $activo->ubicacion ?? 'Sin ubicación' }}
                                </td>

                                <td>
                                    L {{ number_format($activo->valor_compra, 2) }}
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

                                    @if ($activo->tipo_baja)
                                        <br>
                                        <small class="text-muted">
                                            {{ $activo->tipo_baja }}
                                        </small>
                                    @endif

                                    @if ($activo->valor_recuperado > 0)
                                        <br>
                                        <small class="text-success">
                                            Rec: L {{ number_format($activo->valor_recuperado, 2) }}
                                        </small>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">
                                    No hay activos fijos con los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $activos->links() }}
        </div>
    </div>
</div>