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

    @can('crear ordenes trabajo')
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Nueva orden de trabajo</h3>
            </div>

            <form wire:submit.prevent="registrarOrden">
                <div class="card-body">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Cliente registrado</label>
                            <select class="form-control" wire:model="cliente_id">
                                <option value="">Cliente no registrado / manual</option>

                                @foreach ($clientes as $cliente)
                                    <option value="{{ $cliente->id }}">
                                        {{ $cliente->nombre_cliente
                                            ?? $cliente->nombre_completo
                                            ?? $cliente->razon_social
                                            ?? $cliente->cliente
                                            ?? 'Cliente #' . $cliente->id }}
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
                            <label>Título del trabajo <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   wire:model.defer="titulo"
                                   placeholder="Ej. 25 stickers personalizados">

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
                            <label>Entrega</label>
                            <input type="date"
                                   class="form-control"
                                   wire:model.defer="fecha_entrega">

                            @error('fecha_entrega')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-3">
                            <label>Prioridad</label>
                            <select class="form-control" wire:model.defer="prioridad">
                                @foreach ($prioridades as $prioridadItem)
                                    <option value="{{ $prioridadItem }}">
                                        {{ $prioridadItem }}
                                    </option>
                                @endforeach
                            </select>

                            @error('prioridad')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Descripción general</label>
                        <textarea class="form-control"
                                  rows="2"
                                  wire:model.defer="descripcion"
                                  placeholder="Detalles del trabajo solicitado"></textarea>

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
                        </div>

                        <div class="form-group col-md-1">
                            <label>Precio</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model.defer="detalle_precio_unitario">
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
                                <label>Observación interna</label>
                                <textarea class="form-control"
                                          rows="2"
                                          wire:model.defer="observacion"></textarea>
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
                                    <td>
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               class="form-control form-control-sm text-right"
                                               wire:model="descuento">
                                    </td>
                                </tr>
                                <tr>
                                    <th>Total</th>
                                    <td class="text-right">
                                        <strong>L {{ number_format($total, 2) }}</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Abono inicial</th>
                                    <td>
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               class="form-control form-control-sm text-right"
                                               wire:model="abono">
                                    </td>
                                </tr>
                                <tr>
                                    <th>Saldo</th>
                                    <td class="text-right">
                                        <strong class="{{ $saldo > 0 ? 'text-danger' : 'text-success' }}">
                                            L {{ number_format($saldo, 2) }}
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
                        <i class="fas fa-save"></i> Guardar orden
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
            <h3 class="card-title">Historial de órdenes de trabajo</h3>
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
                        <option value="En diseño">En diseño</option>
                        <option value="En producción">En producción</option>
                        <option value="Terminado">Terminado</option>
                        <option value="Entregada">Entregada</option>
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
                            <th>Trabajo</th>
                            <th>Estado</th>
                            <th class="text-right">Total</th>
                            <th class="text-right">Saldo</th>
                            <th width="230">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($ordenes as $orden)
                            <tr>
                                <td><strong>{{ $orden->codigo }}</strong></td>
                                <td>{{ \Carbon\Carbon::parse($orden->fecha)->format('d/m/Y') }}</td>
                                <td>
                                    {{ $orden->cliente_nombre ?: ($orden->cliente->nombre ?? 'Cliente no registrado') }}

                                    @if ($orden->cliente_telefono)
                                        <div class="small text-muted">{{ $orden->cliente_telefono }}</div>
                                    @endif
                                </td>
                                <td>
                                    {{ $orden->titulo }}
                                    <div class="small text-muted">
                                        Prioridad: {{ $orden->prioridad }}
                                    </div>
                                </td>
                                <td>
                                    @if ($orden->estado === 'Anulada')
                                        <span class="badge badge-danger">Anulada</span>
                                    @elseif ($orden->estado === 'Entregada')
                                        <span class="badge badge-success">Entregada</span>
                                    @elseif ($orden->estado === 'Terminado')
                                        <span class="badge badge-primary">Terminado</span>
                                    @else
                                        <span class="badge badge-warning">{{ $orden->estado }}</span>
                                    @endif

                                    @can('cambiar estado ordenes trabajo')
                                        @if ($orden->estado !== 'Anulada')
                                            <select class="form-control form-control-sm mt-1"
                                                    wire:change="cambiarEstado({{ $orden->id }}, $event.target.value)">
                                                <option value="">Cambiar...</option>
                                                @foreach ($estados as $estadoItem)
                                                    <option value="{{ $estadoItem }}">
                                                        {{ $estadoItem }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        @endif
                                    @endcan
                                </td>
                                <td class="text-right">L {{ number_format($orden->total, 2) }}</td>
                                <td class="text-right">
                                    <strong class="{{ $orden->saldo > 0 ? 'text-danger' : 'text-success' }}">
                                        L {{ number_format($orden->saldo, 2) }}
                                    </strong>
                                </td>
                                <td>
                                    <button type="button"
                                            class="btn btn-info btn-xs"
                                            wire:click="verDetalle({{ $orden->id }})">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>

                                    @can('anular ordenes trabajo')
                                        @if ($orden->estado !== 'Anulada')
                                            <button type="button"
                                                    class="btn btn-danger btn-xs"
                                                    wire:click="abrirAnular({{ $orden->id }})">
                                                <i class="fas fa-ban"></i> Anular
                                            </button>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    No hay órdenes de trabajo registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $ordenes->links() }}
        </div>
    </div>

    @if ($mostrarModalDetalle && $ordenDetalle)
        <div class="modal fade show"
            id="detalleOrdenModal"
            style="display: block;"
            role="dialog"
            aria-modal="true">

            <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <div>
                            <h5 class="modal-title mb-0">
                                <i class="fas fa-clipboard-list"></i>
                                Detalle de orden de trabajo
                            </h5>
                            <small class="text-muted">
                                Código: {{ $ordenDetalle->codigo }} | {{ $ordenDetalle->titulo }}
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
                                            {{ $ordenDetalle->codigo }}
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
                                            {{ \Carbon\Carbon::parse($ordenDetalle->fecha)->format('d/m/Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="info-box mb-3">
                                    <span class="info-box-icon {{ $ordenDetalle->estado === 'Anulada' ? 'bg-danger' : ($ordenDetalle->estado === 'Entregada' ? 'bg-success' : 'bg-warning') }}">
                                        <i class="fas {{ $ordenDetalle->estado === 'Anulada' ? 'fa-ban' : ($ordenDetalle->estado === 'Entregada' ? 'fa-check-circle' : 'fa-clock') }}"></i>
                                    </span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">Estado</span>
                                        <span class="info-box-number">
                                            {{ $ordenDetalle->estado }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="info-box mb-3">
                                    <span class="info-box-icon {{ $ordenDetalle->prioridad === 'Urgente' ? 'bg-danger' : ($ordenDetalle->prioridad === 'Alta' ? 'bg-warning' : 'bg-secondary') }}">
                                        <i class="fas fa-flag"></i>
                                    </span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">Prioridad</span>
                                        <span class="info-box-number">
                                            {{ $ordenDetalle->prioridad }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Datos de anulación --}}
                        @if ($ordenDetalle->estado === 'Anulada')
                            <div class="alert alert-danger">
                                <h6 class="mb-2">
                                    <i class="fas fa-ban"></i>
                                    Orden anulada
                                </h6>

                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Fecha de anulación:</strong><br>
                                        {{ $ordenDetalle->fecha_anulacion ? \Carbon\Carbon::parse($ordenDetalle->fecha_anulacion)->format('d/m/Y H:i') : 'No registrada' }}
                                    </div>

                                    <div class="col-md-4">
                                        <strong>Anulado por:</strong><br>
                                        {{ $ordenDetalle->usuarioAnulacion->name ?? 'Sistema' }}
                                    </div>

                                    <div class="col-md-4">
                                        <strong>Motivo:</strong><br>
                                        {{ $ordenDetalle->motivo_anulacion ?: 'Sin motivo registrado' }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        {{-- Cliente y trabajo --}}
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
                                        <div class="row">
                                            <div class="col-md-12">
                                                <strong>Cliente:</strong>
                                                <p class="mb-2">
                                                    {{ $ordenDetalle->cliente_nombre
                                                        ?: ($ordenDetalle->cliente->nombre_cliente
                                                            ?? $ordenDetalle->cliente->nombre_completo
                                                            ?? $ordenDetalle->cliente->razon_social
                                                            ?? $ordenDetalle->cliente->cliente
                                                            ?? 'Cliente no registrado') }}
                                                </p>
                                            </div>

                                            <div class="col-md-6">
                                                <strong>Teléfono:</strong>
                                                <p class="mb-2">
                                                    {{ $ordenDetalle->cliente_telefono ?: 'Sin teléfono' }}
                                                </p>
                                            </div>

                                            <div class="col-md-6">
                                                <strong>Registrado por:</strong>
                                                <p class="mb-2">
                                                    {{ $ordenDetalle->usuario->name ?? 'Sistema' }}
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card card-outline card-info">
                                    <div class="card-header py-2">
                                        <h3 class="card-title">
                                            <i class="fas fa-briefcase"></i>
                                            Datos del trabajo
                                        </h3>
                                    </div>

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <strong>Título:</strong>
                                                <p class="mb-2">
                                                    {{ $ordenDetalle->titulo }}
                                                </p>
                                            </div>

                                            <div class="col-md-6">
                                                <strong>Fecha de entrega:</strong>
                                                <p class="mb-2">
                                                    {{ $ordenDetalle->fecha_entrega ? \Carbon\Carbon::parse($ordenDetalle->fecha_entrega)->format('d/m/Y') : 'Sin fecha' }}
                                                </p>
                                            </div>

                                            <div class="col-md-6">
                                                <strong>Venta relacionada:</strong>
                                                <p class="mb-2">
                                                    @if ($ordenDetalle->venta_id)
                                                        Venta #{{ $ordenDetalle->venta_id }}
                                                    @else
                                                        Sin venta relacionada
                                                    @endif
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Descripción --}}
                        <div class="card card-outline card-secondary">
                            <div class="card-header py-2">
                                <h3 class="card-title">
                                    <i class="fas fa-align-left"></i>
                                    Descripción general
                                </h3>
                            </div>

                            <div class="card-body">
                                {{ $ordenDetalle->descripcion ?: 'Sin descripción' }}
                            </div>
                        </div>

                        {{-- Detalles del trabajo --}}
                        <div class="card card-outline card-warning">
                            <div class="card-header py-2">
                                <h3 class="card-title">
                                    <i class="fas fa-list"></i>
                                    Detalles del trabajo
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
                                            @forelse ($ordenDetalle->detalles as $detalle)
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

                        {{-- Totales --}}
                        <div class="row">
                            <div class="col-md-7">
                                <div class="card card-outline card-secondary">
                                    <div class="card-header py-2">
                                        <h3 class="card-title">
                                            <i class="fas fa-comment-alt"></i>
                                            Observación interna
                                        </h3>
                                    </div>

                                    <div class="card-body">
                                        {{ $ordenDetalle->observacion ?: 'Sin observación' }}
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
                                                    L {{ number_format($ordenDetalle->subtotal, 2) }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Descuento</th>
                                                <td class="text-right">
                                                    L {{ number_format($ordenDetalle->descuento, 2) }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Total</th>
                                                <td class="text-right">
                                                    <strong>L {{ number_format($ordenDetalle->total, 2) }}</strong>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Abono</th>
                                                <td class="text-right text-success">
                                                    L {{ number_format($ordenDetalle->abono, 2) }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Saldo</th>
                                                <td class="text-right">
                                                    <strong class="{{ $ordenDetalle->saldo > 0 ? 'text-danger' : 'text-success' }}">
                                                        L {{ number_format($ordenDetalle->saldo, 2) }}
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
                        <h5 class="modal-title">Anular orden de trabajo</h5>

                        <button type="button" class="close" wire:click="cerrarModalAnulacion">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-warning">
                            Esta acción marcará la orden como anulada. No eliminará el registro.
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
                            Confirmar anulación
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="modal-backdrop fade show"></div>
    @endif
</div>