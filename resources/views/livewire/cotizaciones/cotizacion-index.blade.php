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

    @can('crear cotizaciones')
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Nueva cotización</h3>
            </div>

            <form wire:submit.prevent="registrarCotizacion">
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Cliente registrado</label>
                            <select class="form-control" wire:model="cliente_id">
                                <option value="">Cliente no registrado / manual</option>

                                @foreach ($clientes as $cliente)
                                    <option value="{{ $cliente->id }}">
                                        {{ $cliente->nombre_completo ?: 'Cliente #' . $cliente->id }}

                                        @if ($cliente->telefono)
                                            - Tel: {{ $cliente->telefono }}
                                        @endif
                                    </option>
                                @endforeach
                            </select>

                            @error('cliente_id')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-4">
                            <label>Nombre cliente</label>
                            <input type="text"
                                   class="form-control"
                                   wire:model.defer="cliente_nombre"
                                   placeholder="Nombre del cliente">

                            @error('cliente_nombre')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-4">
                            <label>Teléfono cliente</label>
                            <input type="text"
                                   class="form-control"
                                   wire:model.defer="cliente_telefono"
                                   placeholder="Teléfono o WhatsApp">

                            @error('cliente_telefono')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-5">
                            <label>Título de la cotización <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   wire:model.defer="titulo"
                                   placeholder="Ej. 100 tarjetas de presentación">

                            @error('titulo')
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

                        <div class="form-group col-md-2">
                            <label>Válida hasta</label>
                            <input type="date"
                                   class="form-control"
                                   wire:model.defer="fecha_validez">

                            @error('fecha_validez')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-3">
                            <label>Descuento</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model="descuento">

                            @error('descuento')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Descripción general</label>
                        <textarea class="form-control"
                                  rows="2"
                                  wire:model.defer="descripcion"
                                  placeholder="Descripción general de la cotización"></textarea>

                        @error('descripcion')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <hr>

                    <h5>Agregar detalle</h5>

                    <div class="form-row">
                        <div class="form-group col-md-2">
                            <label>Tipo</label>
                            <select class="form-control" wire:model="detalle_tipo_item">
                                <option value="Servicio">Servicio</option>
                                <option value="Producto">Producto</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>

                        @if ($detalle_tipo_item === 'Producto')
                            <div class="form-group col-md-4">
                                <label>Producto</label>
                                <select class="form-control" wire:model="detalle_producto_id">
                                    <option value="">Seleccione...</option>

                                    @foreach ($productos as $producto)
                                        <option value="{{ $producto->id }}">
                                            {{ $producto->nombre }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('detalle_producto_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        @endif

                        @if ($detalle_tipo_item === 'Servicio')
                            <div class="form-group col-md-4">
                                <label>Servicio</label>
                                <select class="form-control" wire:model="detalle_servicio_id">
                                    <option value="">Seleccione...</option>

                                    @foreach ($servicios as $servicio)
                                        <option value="{{ $servicio->id }}">
                                            {{ $servicio->nombre }}
                                        </option>
                                    @endforeach
                                </select>

                                @error('detalle_servicio_id')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        @endif

                        <div class="form-group {{ $detalle_tipo_item === 'Otro' ? 'col-md-4' : 'col-md-3' }}">
                            <label>Descripción <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   wire:model.defer="detalle_descripcion">

                            @error('detalle_descripcion')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-1">
                            <label>Cant.</label>
                            <input type="number"
                                   step="0.01"
                                   min="0.01"
                                   class="form-control"
                                   wire:model.defer="detalle_cantidad">

                            @error('detalle_cantidad')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-1">
                            <label>Precio</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model.defer="detalle_precio_unitario">

                            @error('detalle_precio_unitario')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-1 d-flex align-items-end">
                            <button type="button"
                                    class="btn btn-success btn-block"
                                    wire:click="agregarDetalle">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    @if (count($detalles) > 0)
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Tipo</th>
                                        <th>Descripción</th>
                                        <th class="text-right">Cantidad</th>
                                        <th class="text-right">Precio</th>
                                        <th class="text-right">Subtotal</th>
                                        <th width="70">Quitar</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @foreach ($detalles as $index => $detalle)
                                        <tr>
                                            <td>{{ $detalle['tipo_item'] }}</td>
                                            <td>{{ $detalle['descripcion'] }}</td>
                                            <td class="text-right">{{ number_format($detalle['cantidad'], 2) }}</td>
                                            <td class="text-right">L {{ number_format($detalle['precio_unitario'], 2) }}</td>
                                            <td class="text-right">
                                                <strong>L {{ number_format($detalle['subtotal'], 2) }}</strong>
                                            </td>
                                            <td>
                                                <button type="button"
                                                        class="btn btn-danger btn-xs"
                                                        wire:click="eliminarDetalle({{ $index }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    <hr>

                    <div class="row">
                        <div class="col-md-7">
                            <div class="form-group">
                                <label>Condiciones</label>
                                <textarea class="form-control"
                                          rows="3"
                                          wire:model.defer="condiciones"></textarea>

                                @error('condiciones')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label>Observación interna</label>
                                <textarea class="form-control"
                                          rows="2"
                                          wire:model.defer="observacion"></textarea>

                                @error('observacion')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-5">
                            <table class="table table-sm">
                                <tr>
                                    <th>Subtotal</th>
                                    <td class="text-right">L {{ number_format($subtotal, 2) }}</td>
                                </tr>

                                <tr>
                                    <th>Descuento</th>
                                    <td class="text-right">L {{ number_format($descuento, 2) }}</td>
                                </tr>

                                <tr>
                                    <th>Total</th>
                                    <td class="text-right">
                                        <strong class="text-success">
                                            L {{ number_format($total, 2) }}
                                        </strong>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <button type="submit"
                            class="btn btn-primary"
                            wire:loading.attr="disabled">
                        <i class="fas fa-save"></i> Guardar cotización
                    </button>

                    <span wire:loading class="text-info ml-2">
                        Guardando...
                    </span>
                </div>
            </form>
        </div>
    @endcan

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Historial de cotizaciones</h3>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-5">
                    <input type="text"
                           class="form-control"
                           placeholder="Buscar por código, cliente o título..."
                           wire:model.debounce.500ms="search">
                </div>

                <div class="col-md-3">
                    <select class="form-control" wire:model="filtroEstado">
                        <option value="todas">Todos los estados</option>
                        <option value="Pendiente">Pendiente</option>
                        <option value="Aprobada">Aprobada</option>
                        <option value="Rechazada">Rechazada</option>
                        <option value="Anulada">Anulada</option>
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
                            <th>Fecha</th>
                            <th>Cliente</th>
                            <th>Cotización</th>
                            <th>Estado</th>
                            <th class="text-right">Total</th>
                            <th width="240">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($cotizaciones as $cotizacion)
                            <tr>
                                <td>
                                    <strong>{{ $cotizacion->codigo }}</strong>
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($cotizacion->fecha)->format('d/m/Y') }}

                                    @if ($cotizacion->fecha_validez)
                                        <div class="small text-muted">
                                            Válida hasta:
                                            {{ \Carbon\Carbon::parse($cotizacion->fecha_validez)->format('d/m/Y') }}
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    {{ $cotizacion->cliente_nombre ?: ($cotizacion->cliente->nombre_completo ?? 'Cliente no registrado') }}

                                    @if ($cotizacion->cliente_telefono)
                                        <div class="small text-muted">
                                            {{ $cotizacion->cliente_telefono }}
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    {{ $cotizacion->titulo }}

                                    @if ($cotizacion->orden_trabajo_id)
                                        <div class="small text-success">
                                            Convertida en orden #{{ $cotizacion->orden_trabajo_id }}
                                        </div>
                                    @endif
                                </td>

                                <td>
                                    @if ($cotizacion->estado === 'Anulada')
                                        <span class="badge badge-danger">Anulada</span>
                                    @elseif ($cotizacion->estado === 'Aprobada')
                                        <span class="badge badge-success">Aprobada</span>
                                    @elseif ($cotizacion->estado === 'Rechazada')
                                        <span class="badge badge-secondary">Rechazada</span>
                                    @else
                                        <span class="badge badge-warning">Pendiente</span>
                                    @endif
                                </td>

                                <td class="text-right">
                                    <strong>L {{ number_format($cotizacion->total, 2) }}</strong>
                                </td>

                                <td>
                                    <button type="button"
                                            class="btn btn-info btn-xs"
                                            wire:click="verDetalle({{ $cotizacion->id }})">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>

                                    @can('imprimir cotizaciones')
                                        <a href="{{ route('cotizaciones.imprimir', $cotizacion->id) }}"
                                        target="_blank"
                                        class="btn btn-secondary btn-xs">
                                            <i class="fas fa-print"></i> Imprimir
                                        </a>
                                    @endcan

                                    @can('convertir cotizaciones')
                                        @if (!$cotizacion->orden_trabajo_id && $cotizacion->estado !== 'Anulada')
                                            <button type="button"
                                                    class="btn btn-success btn-xs"
                                                    wire:click="abrirConvertir({{ $cotizacion->id }})">
                                                <i class="fas fa-clipboard-list"></i> Orden
                                            </button>
                                        @endif
                                    @endcan

                                    @if ($cotizacion->orden_trabajo_id)
                                        <a href="{{ route('ordenes-trabajo.imprimir', $cotizacion->orden_trabajo_id) }}"
                                           target="_blank"
                                           class="btn btn-primary btn-xs">
                                            <i class="fas fa-print"></i> Orden
                                        </a>
                                    @endif

                                    @can('anular cotizaciones')
                                        @if ($cotizacion->estado !== 'Anulada' && !$cotizacion->orden_trabajo_id)
                                            <button type="button"
                                                    class="btn btn-danger btn-xs"
                                                    wire:click="abrirAnular({{ $cotizacion->id }})">
                                                <i class="fas fa-ban"></i> Anular
                                            </button>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">
                                    No hay cotizaciones registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $cotizaciones->links() }}
        </div>
    </div>

    @if ($mostrarModalDetalle && $cotizacionDetalle)
        <div class="modal fade show"
             style="display: block;"
             role="dialog"
             aria-modal="true">

            <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <div>
                            <h5 class="modal-title mb-0">
                                <i class="fas fa-file-invoice-dollar"></i>
                                Detalle de cotización
                            </h5>
                            <small class="text-muted">
                                Código: {{ $cotizacionDetalle->codigo }} | {{ $cotizacionDetalle->titulo }}
                            </small>
                        </div>

                        <button type="button" class="close" wire:click="cerrarModalDetalle">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body" style="max-height: calc(100vh - 220px); overflow-y: auto;">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-box mb-3">
                                    <span class="info-box-icon bg-info">
                                        <i class="fas fa-barcode"></i>
                                    </span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">Código</span>
                                        <span class="info-box-number">
                                            {{ $cotizacionDetalle->codigo }}
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
                                            {{ \Carbon\Carbon::parse($cotizacionDetalle->fecha)->format('d/m/Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="info-box mb-3">
                                    <span class="info-box-icon bg-secondary">
                                        <i class="far fa-calendar-check"></i>
                                    </span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">Validez</span>
                                        <span class="info-box-number">
                                            {{ $cotizacionDetalle->fecha_validez ? \Carbon\Carbon::parse($cotizacionDetalle->fecha_validez)->format('d/m/Y') : 'Sin fecha' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="info-box mb-3">
                                    <span class="info-box-icon {{ $cotizacionDetalle->estado === 'Anulada' ? 'bg-danger' : ($cotizacionDetalle->estado === 'Aprobada' ? 'bg-success' : 'bg-warning') }}">
                                        <i class="fas {{ $cotizacionDetalle->estado === 'Anulada' ? 'fa-ban' : ($cotizacionDetalle->estado === 'Aprobada' ? 'fa-check-circle' : 'fa-clock') }}"></i>
                                    </span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">Estado</span>
                                        <span class="info-box-number">
                                            {{ $cotizacionDetalle->estado }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($cotizacionDetalle->estado === 'Anulada')
                            <div class="alert alert-danger">
                                <h6 class="mb-2">
                                    <i class="fas fa-ban"></i>
                                    Cotización anulada
                                </h6>

                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Fecha de anulación:</strong><br>
                                        {{ $cotizacionDetalle->fecha_anulacion ? \Carbon\Carbon::parse($cotizacionDetalle->fecha_anulacion)->format('d/m/Y H:i') : 'No registrada' }}
                                    </div>

                                    <div class="col-md-4">
                                        <strong>Anulado por:</strong><br>
                                        {{ $cotizacionDetalle->usuarioAnulacion->name ?? 'Sistema' }}
                                    </div>

                                    <div class="col-md-4">
                                        <strong>Motivo:</strong><br>
                                        {{ $cotizacionDetalle->motivo_anulacion ?: 'Sin motivo registrado' }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-outline card-primary">
                                    <div class="card-header py-2">
                                        <h3 class="card-title">
                                            <i class="fas fa-user"></i>
                                            Datos del cliente
                                        </h3>
                                    </div>

                                    <div class="card-body">
                                        <strong>Cliente:</strong>
                                        <p class="mb-2">
                                            {{ $cotizacionDetalle->cliente_nombre ?: ($cotizacionDetalle->cliente->nombre_completo ?? 'Cliente no registrado') }}
                                        </p>

                                        <strong>Teléfono:</strong>
                                        <p class="mb-2">
                                            {{ $cotizacionDetalle->cliente_telefono ?: 'Sin teléfono' }}
                                        </p>

                                        <strong>Registrado por:</strong>
                                        <p class="mb-0">
                                            {{ $cotizacionDetalle->usuario->name ?? 'Sistema' }}
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card card-outline card-info">
                                    <div class="card-header py-2">
                                        <h3 class="card-title">
                                            <i class="fas fa-briefcase"></i>
                                            Datos de la cotización
                                        </h3>
                                    </div>

                                    <div class="card-body">
                                        <strong>Título:</strong>
                                        <p class="mb-2">
                                            {{ $cotizacionDetalle->titulo }}
                                        </p>

                                        <strong>Orden relacionada:</strong>
                                        <p class="mb-2">
                                            @if ($cotizacionDetalle->orden_trabajo_id)
                                                Orden #{{ $cotizacionDetalle->orden_trabajo_id }}
                                            @else
                                                Sin orden relacionada
                                            @endif
                                        </p>

                                        <strong>Descripción:</strong>
                                        <p class="mb-0">
                                            {{ $cotizacionDetalle->descripcion ?: 'Sin descripción' }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-outline card-warning">
                            <div class="card-header py-2">
                                <h3 class="card-title">
                                    <i class="fas fa-list"></i>
                                    Detalles cotizados
                                </h3>
                            </div>

                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-sm mb-0">
                                        <thead class="thead-light">
                                            <tr>
                                                <th>Tipo</th>
                                                <th>Descripción</th>
                                                <th class="text-right">Cantidad</th>
                                                <th class="text-right">Precio unitario</th>
                                                <th class="text-right">Subtotal</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @forelse ($cotizacionDetalle->detalles as $detalle)
                                                <tr>
                                                    <td>
                                                        @if ($detalle->tipo_item === 'Producto')
                                                            <span class="badge badge-primary">Producto</span>
                                                        @elseif ($detalle->tipo_item === 'Servicio')
                                                            <span class="badge badge-success">Servicio</span>
                                                        @else
                                                            <span class="badge badge-secondary">Otro</span>
                                                        @endif
                                                    </td>

                                                    <td>
                                                        <strong>{{ $detalle->descripcion }}</strong>

                                                        @if ($detalle->observacion)
                                                            <div class="small text-muted">
                                                                {{ $detalle->observacion }}
                                                            </div>
                                                        @endif
                                                    </td>

                                                    <td class="text-right">
                                                        {{ number_format($detalle->cantidad, 2) }}
                                                    </td>

                                                    <td class="text-right">
                                                        L {{ number_format($detalle->precio_unitario, 2) }}
                                                    </td>

                                                    <td class="text-right">
                                                        <strong>L {{ number_format($detalle->subtotal, 2) }}</strong>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="5" class="text-center">
                                                        No hay detalles registrados.
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-7">
                                <div class="card card-outline card-secondary">
                                    <div class="card-header py-2">
                                        <h3 class="card-title">
                                            <i class="fas fa-file-contract"></i>
                                            Condiciones
                                        </h3>
                                    </div>

                                    <div class="card-body">
                                        {{ $cotizacionDetalle->condiciones ?: 'Sin condiciones registradas' }}
                                    </div>
                                </div>

                                <div class="card card-outline card-secondary">
                                    <div class="card-header py-2">
                                        <h3 class="card-title">
                                            <i class="fas fa-comment-alt"></i>
                                            Observación interna
                                        </h3>
                                    </div>

                                    <div class="card-body">
                                        {{ $cotizacionDetalle->observacion ?: 'Sin observación' }}
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-5">
                                <div class="card card-outline card-success">
                                    <div class="card-header py-2">
                                        <h3 class="card-title">
                                            <i class="fas fa-coins"></i>
                                            Resumen financiero
                                        </h3>
                                    </div>

                                    <div class="card-body p-0">
                                        <table class="table table-sm mb-0">
                                            <tr>
                                                <th>Subtotal</th>
                                                <td class="text-right">
                                                    L {{ number_format($cotizacionDetalle->subtotal, 2) }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Descuento</th>
                                                <td class="text-right">
                                                    L {{ number_format($cotizacionDetalle->descuento, 2) }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Total</th>
                                                <td class="text-right">
                                                    <strong class="text-success">
                                                        L {{ number_format($cotizacionDetalle->total, 2) }}
                                                    </strong>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        @can('imprimir cotizaciones')
                            <a href="{{ route('cotizaciones.imprimir', $cotizacionDetalle->id) }}"
                            target="_blank"
                            class="btn btn-secondary">
                                <i class="fas fa-print"></i> Imprimir cotización
                            </a>
                        @endcan

                        @if ($cotizacionDetalle->orden_trabajo_id)
                            <a href="{{ route('ordenes-trabajo.imprimir', $cotizacionDetalle->orden_trabajo_id) }}"
                            target="_blank"
                            class="btn btn-primary">
                                <i class="fas fa-print"></i> Imprimir orden
                            </a>
                        @endif

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
             style="display: block;"
             role="dialog"
             aria-modal="true">

            <div class="modal-dialog" role="document">
                <form wire:submit.prevent="confirmarAnular" class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Anular cotización</h5>

                        <button type="button" class="close" wire:click="cerrarModalAnulacion">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-warning">
                            Esta acción marcará la cotización como anulada. No eliminará el registro.
                        </div>

                        <div class="form-group">
                            <label>Motivo de anulación <span class="text-danger">*</span></label>
                            <textarea class="form-control"
                                      rows="3"
                                      wire:model.defer="motivoAnulacion"></textarea>

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

    @if ($mostrarModalConvertir)
        <div class="modal fade show"
             style="display: block;"
             role="dialog"
             aria-modal="true">

            <div class="modal-dialog" role="document">
                <form wire:submit.prevent="confirmarConvertir" class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Convertir cotización en orden de trabajo</h5>

                        <button type="button" class="close" wire:click="cerrarModalConvertir">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-info">
                            Esta acción creará una orden de trabajo con los datos y detalles de la cotización.
                            La cotización quedará marcada como aprobada.
                        </div>

                        <p class="mb-0">
                            ¿Desea continuar?
                        </p>
                    </div>

                    <div class="modal-footer">
                        <button type="button"
                                class="btn btn-secondary"
                                wire:click="cerrarModalConvertir">
                            Cancelar
                        </button>

                        <button type="submit"
                                class="btn btn-success"
                                wire:loading.attr="disabled">
                            <i class="fas fa-clipboard-list"></i> Confirmar conversión
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal-backdrop fade show"></div>
    @endif
</div>