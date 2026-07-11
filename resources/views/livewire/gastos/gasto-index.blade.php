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
        <a href="{{ route('gastos.create') }}"
        class="btn btn-success btn-sm">
            <i class="fas fa-plus"></i> Registrar gasto
        </a>

        <button type="button"
                class="btn btn-secondary btn-sm"
                wire:click="limpiarFiltros">
            <i class="fas fa-broom"></i> Limpiar filtros
        </button>
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
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4>L {{ number_format($totalGastos, 2) }}</h4>
                    <p>Total gastos activos</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h4>{{ number_format($cantidadAnulados, 0) }}</h4>
                    <p>Gastos anulados</p>
                </div>
                <div class="icon">
                    <i class="fas fa-ban"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>L {{ number_format($totalAnulados, 2) }}</h4>
                    <p>Monto anulado</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
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
                <div class="col-md-3">
                    <label>Buscar</label>
                    <input type="text"
                           class="form-control"
                           placeholder="Descripción, proveedor, referencia..."
                           wire:model.debounce.500ms="search">
                </div>

                <div class="col-md-2">
                    <label>Desde</label>
                    <input type="date"
                           class="form-control"
                           wire:model="fechaDesde">
                </div>

                <div class="col-md-2">
                    <label>Hasta</label>
                    <input type="date"
                           class="form-control"
                           wire:model="fechaHasta">
                </div>

                <div class="col-md-2">
                    <label>Categoría</label>
                    <select class="form-control" wire:model="filtroCategoria">
                        <option value="todos">Todas</option>

                        @foreach ($categorias as $categoriaOption)
                            <option value="{{ $categoriaOption }}">
                                {{ $categoriaOption }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label>Estado</label>
                    <select class="form-control" wire:model="filtroEstado">
                        <option value="todos">Todos</option>
                        <option value="Registrado">Registrado</option>
                        <option value="Anulado">Anulado</option>
                    </select>
                </div>

                <div class="col-md-1">
                    <label>Mostrar</label>
                    <select class="form-control" wire:model="perPage">
                        <option value="10">10</option>
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
            <h3 class="card-title">Listado de gastos</h3>
        </div>

        <div class="card-body">
            <div class="alert alert-info">
                Aquí puedes registrar gastos del negocio. Los gastos anulados no deben tomarse como gasto activo en los reportes.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Categoría</th>
                            <th>Descripción</th>
                            <th>Proveedor</th>
                            <th>Monto</th>
                            <th>Método</th>
                            <th>Referencia</th>
                            <th>Estado</th>
                            <th>Observación</th>
                            <th width="150">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($gastos as $gasto)
                            <tr class="{{ $gasto->estado === 'Anulado' ? 'table-secondary' : '' }}">
                                <td>
                                    {{ $gasto->fecha }}

                                    @if ($gasto->hora)
                                        <br>
                                        <small>{{ $gasto->hora }}</small>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge badge-info">
                                        {{ $gasto->categoria }}
                                    </span>
                                </td>

                                <td>
                                    <strong>{{ $gasto->descripcion }}</strong>
                                </td>

                                <td>
                                    {{ $gasto->proveedor ?? 'Sin proveedor' }}
                                </td>

                                <td>
                                    @if ($gasto->estado === 'Anulado')
                                        <span class="text-muted">
                                            L {{ number_format($gasto->monto, 2) }}
                                        </span>
                                    @else
                                        <strong class="text-danger">
                                            L {{ number_format($gasto->monto, 2) }}
                                        </strong>
                                    @endif
                                </td>

                                <td>
                                    {{ $gasto->metodo_pago }}
                                </td>

                                <td>
                                    {{ $gasto->referencia ?? 'Sin referencia' }}
                                </td>

                                <td>
                                    @if ($gasto->estado === 'Registrado')
                                        <span class="badge badge-success">Registrado</span>
                                    @else
                                        <span class="badge badge-secondary">Anulado</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $gasto->observacion ?? 'Sin observación' }}
                                </td>

                                <td>
                                    @if ($gasto->estado !== 'Anulado')
                                        <a href="{{ route('gastos.edit', $gasto->id) }}"
                                        class="btn btn-primary btn-xs">
                                            Editar
                                        </a>

                                        <button type="button"
                                                class="btn btn-danger btn-xs"
                                                onclick="confirm('¿Seguro que desea anular este gasto?') || event.stopImmediatePropagation()"
                                                wire:click="anular({{ $gasto->id }})">
                                            Anular
                                        </button>
                                    @else
                                        <button type="button"
                                                class="btn btn-warning btn-xs"
                                                onclick="confirm('¿Seguro que desea reactivar este gasto?') || event.stopImmediatePropagation()"
                                                wire:click="reactivar({{ $gasto->id }})">
                                            Reactivar
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">
                                    No hay gastos registrados con los filtros seleccionados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $gastos->links() }}
        </div>
    </div>


</div>