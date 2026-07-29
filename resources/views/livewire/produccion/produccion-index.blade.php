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

    @can('crear produccion')
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Registrar producción</h3>
            </div>

            <form wire:submit.prevent="registrarProduccion">
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-5">
                            <label>Producto a producir <span class="text-danger">*</span></label>
                            <select class="form-control" wire:model="producto_id">
                                <option value="">Seleccione...</option>

                                @foreach ($productos as $producto)
                                    <option value="{{ $producto->id }}">
                                        {{ $producto->codigo }} - {{ $producto->nombre }}
                                    </option>
                                @endforeach
                            </select>

                            @error('producto_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-2">
                            <label>Cantidad <span class="text-danger">*</span></label>
                            <input type="number"
                                   step="0.01"
                                   min="0.01"
                                   class="form-control"
                                   wire:model="cantidad">

                            @error('cantidad')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-2">
                            <label>Fecha <span class="text-danger">*</span></label>
                            <input type="date"
                                   class="form-control"
                                   wire:model.defer="fecha">

                            @error('fecha')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-3">
                            <label>Observación</label>
                            <input type="text"
                                   class="form-control"
                                   placeholder="Opcional"
                                   wire:model.defer="observacion">

                            @error('observacion')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    @if (count($recetaCalculada) > 0)
                        <div class="table-responsive mt-3">
                            <h5>Insumos requeridos</h5>

                            <table class="table table-bordered table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Insumo</th>
                                        <th>Por unidad</th>
                                        <th>Cantidad necesaria</th>
                                        <th>Stock disponible</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($recetaCalculada as $item)
                                        <tr>
                                            <td>{{ $item['insumo'] }}</td>
                                            <td>
                                                {{ number_format($item['cantidad_por_unidad'], 4) }}
                                                {{ $item['unidad'] }}
                                            </td>
                                            <td>
                                                {{ number_format($item['cantidad_necesaria'], 4) }}
                                                {{ $item['unidad'] }}
                                            </td>
                                            <td>
                                                {{ number_format($item['stock_disponible'], 4) }}
                                                {{ $item['unidad'] }}
                                            </td>
                                            <td>
                                                @if ($item['suficiente'])
                                                    <span class="badge badge-success">Disponible</span>
                                                @else
                                                    <span class="badge badge-danger">Insuficiente</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div class="card-footer">
                    <button type="submit"
                            class="btn btn-primary"
                            wire:loading.attr="disabled">
                        <i class="fas fa-industry"></i> Registrar producción
                    </button>

                    <span wire:loading class="text-info ml-2">
                        Procesando producción...
                    </span>
                </div>
            </form>
        </div>
    @endcan

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Historial de producción</h3>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-5">
                    <input type="text"
                           class="form-control"
                           placeholder="Buscar por código o producto..."
                           wire:model.debounce.500ms="search">
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
                            <th>Fecha</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Costo unitario</th>
                            <th>Costo total</th>
                            <th>Estado</th>
                            <th>Usuario</th>
                            <th width="100">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($producciones as $produccion)
                            <tr>
                                <td>
                                    <strong>{{ $produccion->codigo }}</strong>
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($produccion->fecha)->format('d/m/Y') }}
                                </td>

                                <td>
                                    {{ $produccion->producto->nombre ?? '' }}

                                    <div class="small text-muted">
                                        {{ $produccion->insumos->count() }} insumos usados
                                    </div>
                                </td>

                                <td>
                                    {{ number_format($produccion->cantidad, 2) }}
                                </td>

                                <td>
                                    L {{ number_format($produccion->costo_unitario, 4) }}
                                </td>

                                <td>
                                    <strong>L {{ number_format($produccion->costo_total, 2) }}</strong>
                                </td>

                                <td>
                                    @if ($produccion->estado === 'Registrada')
                                        <span class="badge badge-success">Registrada</span>
                                    @else
                                        <span class="badge badge-danger">{{ $produccion->estado }}</span>
                                    @endif
                                </td>

                                <td>
                                    {{ $produccion->usuario->name ?? 'Sistema' }}
                                </td>
                                <td>
                                    <button type="button"
                                            class="btn btn-info btn-xs"
                                            wire:click="verDetalle({{ $produccion->id }})">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>

                                    @can('anular produccion')
                                        @if ($produccion->estado === 'Registrada')
                                            <button type="button"
                                                    class="btn btn-danger btn-xs"
                                                    wire:click="abrirAnularProduccion({{ $produccion->id }})">
                                                <i class="fas fa-ban"></i> Anular
                                            </button>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">
                                    No hay producciones registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $producciones->links() }}
        </div>
    </div>
    @if ($mostrarModalDetalle && $produccionDetalle)
        <div class="modal fade show"
            id="detalleProduccionModal"
            tabindex="-1"
            role="dialog"
            style="display: block;"
            aria-modal="true">

            <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <div>
                            <h5 class="modal-title mb-0">
                                <i class="fas fa-industry"></i>
                                Detalle de producción
                            </h5>
                            <small class="text-muted">
                                Código: {{ $produccionDetalle->codigo }}
                            </small>
                        </div>

                        <button type="button" class="close" wire:click="cerrarModalDetalle">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body" style="max-height: calc(100vh - 220px); overflow-y: auto;">

                        {{-- Resumen principal --}}
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-box mb-3">
                                    <span class="info-box-icon bg-info">
                                        <i class="fas fa-barcode"></i>
                                    </span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">Código</span>
                                        <span class="info-box-number">
                                            {{ $produccionDetalle->codigo }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="info-box mb-3">
                                    <span class="info-box-icon bg-primary">
                                        <i class="far fa-calendar-alt"></i>
                                    </span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">Fecha</span>
                                        <span class="info-box-number">
                                            {{ \Carbon\Carbon::parse($produccionDetalle->fecha)->format('d/m/Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="info-box mb-3">
                                    <span class="info-box-icon {{ $produccionDetalle->estado === 'Registrada' ? 'bg-success' : 'bg-danger' }}">
                                        <i class="fas {{ $produccionDetalle->estado === 'Registrada' ? 'fa-check-circle' : 'fa-ban' }}"></i>
                                    </span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">Estado</span>
                                        <span class="info-box-number">
                                            {{ $produccionDetalle->estado }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="info-box mb-3">
                                    <span class="info-box-icon bg-secondary">
                                        <i class="fas fa-user"></i>
                                    </span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">Registrado por</span>
                                        <span class="info-box-number">
                                            {{ $produccionDetalle->usuario->name ?? 'Sistema' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Datos de anulación --}}
                        @if ($produccionDetalle->estado === 'Anulada')
                            <div class="alert alert-danger">
                                <h6 class="mb-2">
                                    <i class="fas fa-ban"></i>
                                    Producción anulada
                                </h6>

                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Fecha de anulación:</strong><br>
                                        {{ $produccionDetalle->fecha_anulacion ? \Carbon\Carbon::parse($produccionDetalle->fecha_anulacion)->format('d/m/Y H:i') : 'No registrada' }}
                                    </div>

                                    <div class="col-md-4">
                                        <strong>Anulado por:</strong><br>
                                        {{ $produccionDetalle->usuarioAnulacion->name ?? 'Sistema' }}
                                    </div>

                                    <div class="col-md-4">
                                        <strong>Motivo:</strong><br>
                                        {{ $produccionDetalle->motivo_anulacion ?: 'Sin motivo registrado' }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Producto y costos --}}
                        <div class="row">
                            <div class="col-md-7">
                                <div class="card card-outline card-primary">
                                    <div class="card-header py-2">
                                        <h3 class="card-title">
                                            <i class="fas fa-box"></i>
                                            Producto producido
                                        </h3>
                                    </div>

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <strong>Producto:</strong>
                                                <p class="mb-2">
                                                    {{ $produccionDetalle->producto->nombre ?? '' }}
                                                </p>
                                            </div>

                                            <div class="col-md-4">
                                                <strong>Cantidad producida:</strong>
                                                <p class="mb-2">
                                                    {{ number_format($produccionDetalle->cantidad, 2) }}
                                                </p>
                                            </div>

                                            <div class="col-md-12">
                                                <strong>Movimiento de producto:</strong>
                                                <p class="mb-0">
                                                    @if ($produccionDetalle->movimientoProducto)
                                                        #{{ $produccionDetalle->movimientoProducto->id }}
                                                        - {{ $produccionDetalle->movimientoProducto->tipo_movimiento }}
                                                    @else
                                                        Sin movimiento relacionado
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="card card-outline card-success">
                                    <div class="card-header py-2">
                                        <h3 class="card-title">
                                            <i class="fas fa-coins"></i>
                                            Costos de producción
                                        </h3>
                                    </div>

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <strong>Costo unitario:</strong>
                                                <h5 class="mt-1">
                                                    L {{ number_format($produccionDetalle->costo_unitario, 4) }}
                                                </h5>
                                            </div>

                                            <div class="col-md-6">
                                                <strong>Costo total:</strong>
                                                <h5 class="mt-1 text-success">
                                                    L {{ number_format($produccionDetalle->costo_total, 2) }}
                                                </h5>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Observación --}}
                        <div class="card card-outline card-secondary">
                            <div class="card-header py-2">
                                <h3 class="card-title">
                                    <i class="fas fa-comment-alt"></i>
                                    Observación
                                </h3>
                            </div>

                            <div class="card-body">
                                {{ $produccionDetalle->observacion ?: 'Sin observación' }}
                            </div>
                        </div>

                        {{-- Insumos consumidos --}}
                        <div class="card card-outline card-warning">
                            <div class="card-header py-2">
                                <h3 class="card-title">
                                    <i class="fas fa-boxes"></i>
                                    Insumos consumidos
                                </h3>
                            </div>

                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-sm mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Insumo</th>
                                                <th class="text-right">Cantidad por unidad</th>
                                                <th class="text-right">Cantidad total</th>
                                                <th class="text-right">Costo unitario</th>
                                                <th class="text-right">Costo total</th>
                                                <th>Movimiento inventario</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @forelse ($produccionDetalle->insumos as $detalle)
                                                <tr>
                                                    <td>
                                                        <strong>{{ $detalle->insumo->nombre ?? '' }}</strong>
                                                        <div class="small text-muted">
                                                            {{ $detalle->insumo->codigo ?? '' }}
                                                        </div>
                                                    </td>

                                                    <td class="text-right">
                                                        {{ number_format($detalle->cantidad_por_unidad, 4) }}
                                                        {{ $detalle->insumo->unidad_consumo ?? '' }}
                                                    </td>

                                                    <td class="text-right">
                                                        {{ number_format($detalle->cantidad_total, 4) }}
                                                        {{ $detalle->insumo->unidad_consumo ?? '' }}
                                                    </td>

                                                    <td class="text-right">
                                                        L {{ number_format($detalle->costo_unitario, 4) }}
                                                    </td>

                                                    <td class="text-right">
                                                        <strong>L {{ number_format($detalle->costo_total, 2) }}</strong>
                                                    </td>

                                                    <td>
                                                        @if ($detalle->movimientoInventario)
                                                            #{{ $detalle->movimientoInventario->id }}
                                                            - {{ $detalle->movimientoInventario->tipo_movimiento }}
                                                        @else
                                                            <span class="text-muted">Sin movimiento</span>
                                                        @endif
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="6" class="text-center">
                                                        No hay insumos registrados para esta producción.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>

                                        <tfoot>
                                            <tr>
                                                <th colspan="4" class="text-right">
                                                    Total consumido:
                                                </th>
                                                <th class="text-right">
                                                    L {{ number_format($produccionDetalle->insumos->sum('costo_total'), 2) }}
                                                </th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-secondary"
                                wire:click="cerrarModalDetalle">
                            Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-backdrop fade show"></div>
    @endif
    @if ($mostrarModalAnulacion)
        <div class="modal fade show"
            id="anularProduccionModal"
            tabindex="-1"
            role="dialog"
            style="display: block;"
            aria-modal="true">

            <div class="modal-dialog" role="document">
                <form wire:submit.prevent="confirmarAnularProduccion" class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Anular producción</h5>

                        <button type="button" class="close" wire:click="cerrarModalAnulacion">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-warning">
                            Esta acción intentará revertir el producto terminado y devolver los insumos consumidos.
                            Si el producto producido ya fue vendido o consumido parcialmente, el sistema no permitirá la anulación.
                        </div>

                        <div class="form-group">
                            <label>Motivo de anulación <span class="text-danger">*</span></label>
                            <textarea class="form-control"
                                    rows="3"
                                    wire:model.defer="motivoAnulacion"
                                    placeholder="Explique por qué se anula esta producción"></textarea>

                            @error('motivoAnulacion')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-secondary"
                                wire:click="cerrarModalAnulacion">
                            Cancelar
                        </button>

                        <button type="submit"
                                class="btn btn-danger"
                                wire:loading.attr="disabled">
                            <i class="fas fa-ban"></i> Confirmar anulación
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal-backdrop fade show"></div>
    @endif
</div>