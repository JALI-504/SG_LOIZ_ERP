<div>
    <style>
        .reporte-header {
            background: linear-gradient(135deg, #1f2937, #334155);
            color: white;
            border-radius: 8px;
            padding: 18px 22px;
            margin-bottom: 18px;
            box-shadow: 0 3px 10px rgba(0,0,0,.12);
        }

        .reporte-header h4 {
            margin: 0;
            font-weight: 700;
        }

        .reporte-header small {
            color: #d1d5db;
        }

        .reporte-action-btn {
            margin-left: 4px;
            margin-bottom: 4px;
        }

        .metric-card {
            border-radius: 8px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            padding: 16px;
            min-height: 105px;
            box-shadow: 0 2px 6px rgba(0,0,0,.05);
        }

        .metric-card .metric-title {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .metric-card .metric-value {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 0;
        }

        .metric-card .metric-icon {
            font-size: 28px;
            opacity: .25;
            float: right;
        }

        .table-report th {
            white-space: nowrap;
            vertical-align: middle;
        }

        .table-report td {
            vertical-align: middle;
        }

        .badge-soft {
            padding: 6px 8px;
            border-radius: 12px;
            font-size: 12px;
        }
    </style>

    <div class="reporte-header">
        <div class="row align-items-center">
            <div class="col-md-7">
                <h4>
                    <i class="fas fa-file-alt"></i>
                    Reporte de activos fijos
                </h4>
                <small>
                    Consulta general de activos, depreciación, valor en libros, bajas, ventas y recuperaciones.
                </small>
            </div>

            <div class="col-md-5 text-md-right mt-3 mt-md-0">
                <a href="{{ route('activos-fijos.index') }}" class="btn btn-light btn-sm reporte-action-btn">
                    <i class="fas fa-laptop-house"></i>
                    Activos
                </a>

                <a href="{{ route('activos-fijos.depreciaciones') }}" class="btn btn-light btn-sm reporte-action-btn">
                    <i class="fas fa-chart-line"></i>
                    Depreciaciones
                </a>

                <button type="button"
                        class="btn btn-outline-light btn-sm reporte-action-btn"
                        wire:click="limpiarFiltros">
                    <i class="fas fa-broom"></i>
                    Limpiar
                </button>

                <button type="button"
                        class="btn btn-success btn-sm reporte-action-btn"
                        wire:click="exportarExcel">
                    <i class="fas fa-file-excel"></i>
                    Exportar Excel
                </button>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card card-outline card-primary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-filter"></i>
                Filtros del reporte
            </h3>
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
            <div class="metric-card">
                <i class="fas fa-list metric-icon text-info"></i>
                <div class="metric-title">Total activos filtrados</div>
                <p class="metric-value text-info">{{ number_format($totalActivos, 0) }}</p>
                <small class="text-muted">Incluye activos vigentes y retirados</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="metric-card">
                <i class="fas fa-check-circle metric-icon text-success"></i>
                <div class="metric-title">Activos vigentes</div>
                <p class="metric-value text-success">{{ number_format($totalActivosVigentes, 0) }}</p>
                <small class="text-muted">Activos disponibles en uso</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="metric-card">
                <i class="fas fa-ban metric-icon text-secondary"></i>
                <div class="metric-title">Dados de baja</div>
                <p class="metric-value text-secondary">{{ number_format($totalBajas, 0) }}</p>
                <small class="text-muted">Retirados del uso</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="metric-card">
                <i class="fas fa-hand-holding-usd metric-icon text-primary"></i>
                <div class="metric-title">Vendidos</div>
                <p class="metric-value text-primary">{{ number_format($totalVendidos, 0) }}</p>
                <small class="text-muted">Activos vendidos</small>
            </div>
        </div>
    </div>

    <br>

    {{-- Resumen financiero --}}
    <div class="row">
        <div class="col-md-3">
            <div class="metric-card">
                <i class="fas fa-money-bill-wave metric-icon text-success"></i>
                <div class="metric-title">Valor compra vigente</div>
                <p class="metric-value text-success">L {{ number_format($valorCompraTotal, 2) }}</p>
                <small class="text-muted">Costo de activos activos</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="metric-card">
                <i class="fas fa-chart-line metric-icon text-warning"></i>
                <div class="metric-title">Depreciación acumulada</div>
                <p class="metric-value text-warning">L {{ number_format($depreciacionAcumuladaTotal, 2) }}</p>
                <small class="text-muted">Depreciación registrada</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="metric-card">
                <i class="fas fa-book metric-icon text-primary"></i>
                <div class="metric-title">Valor en libros vigente</div>
                <p class="metric-value text-primary">L {{ number_format($valorLibrosTotal, 2) }}</p>
                <small class="text-muted">Valor contable actual</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="metric-card">
                <i class="fas fa-coins metric-icon text-danger"></i>
                <div class="metric-title">Valor recuperado</div>
                <p class="metric-value text-danger">L {{ number_format($valorRecuperadoTotal, 2) }}</p>
                <small class="text-muted">Ventas, seguros o recuperaciones</small>
            </div>
        </div>
    </div>

    <br>

    {{-- Resumen por categoría --}}
    <div class="card card-outline card-info">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-layer-group"></i>
                Resumen por categoría vigente
            </h3>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover table-striped table-sm table-report">
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
    <div class="card card-outline card-secondary">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-clipboard-list"></i>
                Detalle de activos fijos
            </h3>

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
                <table class="table table-bordered table-hover table-striped table-sm table-report">
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
                                    <span class="badge badge-{{ $activo->estado_clase }} badge-soft">
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