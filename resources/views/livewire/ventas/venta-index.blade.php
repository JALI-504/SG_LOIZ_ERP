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

    <div class="row">
        {{-- Lado izquierdo: búsqueda --}}
        <div class="col-md-7">

            {{-- Cliente --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Cliente
                    </h3>
                </div>

                <div class="card-body">
                    <div class="form-group">
                        <label>Buscar cliente</label>
                        <input type="text"
                               class="form-control"
                               placeholder="Buscar por nombre, DNI, RTN o teléfono..."
                               wire:model.debounce.500ms="searchCliente">
                    </div>

                    <div class="form-group mb-0">
                        <label>Seleccionar cliente</label>
                        <select class="form-control" wire:model.defer="cliente_id">
                            <option value="">Consumidor final / Sin cliente</option>

                            @foreach ($clientes as $cliente)
                                <option value="{{ $cliente->id }}">
                                    {{ trim($cliente->primer_nombre . ' ' . $cliente->segundo_nombre . ' ' . $cliente->primer_apellido . ' ' . $cliente->segundo_apellido) }}
                                    @if ($cliente->dni)
                                        - DNI: {{ $cliente->dni }}
                                    @endif
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
                </div>
            </div>

            {{-- Buscar productos o servicios --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Productos y servicios
                    </h3>
                </div>

                <div class="card-body">

                    <div class="row mb-3">
                        <div class="col-md-8">
                            <input type="text"
                                   class="form-control"
                                   placeholder="Buscar producto, código, código de barra o servicio..."
                                   wire:model.debounce.500ms="searchItem">
                        </div>

                        <div class="col-md-4">
                            <select class="form-control" wire:model="tipoFiltro">
                                <option value="todos">Productos y servicios</option>
                                <option value="productos">Solo productos</option>
                                <option value="servicios">Solo servicios</option>
                            </select>
                        </div>
                    </div>

                    @if ($tipoFiltro === 'todos' || $tipoFiltro === 'productos')
                        <h5>Productos</h5>

                        <div class="table-responsive mb-4">
                            <table class="table table-bordered table-hover table-sm">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Código</th>
                                        <th>Producto</th>
                                        <th>Stock</th>
                                        <th>Precio</th>
                                        <th width="90">Acción</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse ($productos as $producto)
                                        <tr>
                                            <td>
                                                <strong>{{ $producto->codigo }}</strong>
                                                @if ($producto->codigo_barra)
                                                    <br>
                                                    <small>{{ $producto->codigo_barra }}</small>
                                                @endif
                                            </td>

                                            <td>
                                                {{ $producto->nombre }} <br>
                                                <small>
                                                    {{ $producto->categoria }} /
                                                    {{ $producto->tipo_producto }}
                                                </small>
                                            </td>

                                            <td>
                                                @if ($producto->maneja_inventario)
                                                    @if ($producto->stock_actual <= 0)
                                                        <span class="badge badge-danger">
                                                            Sin stock
                                                        </span>
                                                    @elseif ($producto->stock_actual <= $producto->stock_minimo)
                                                        <span class="badge badge-warning">
                                                            Stock bajo
                                                        </span>
                                                    @else
                                                        <span class="badge badge-success">
                                                            Disponible
                                                        </span>
                                                    @endif

                                                    <br>
                                                    <strong>{{ number_format($producto->stock_actual, 2) }}</strong>
                                                    {{ $producto->unidad_venta }}
                                                @else
                                                    <span class="badge badge-info">
                                                        No controla stock
                                                    </span>
                                                @endif
                                            </td>

                                            <td>
                                                <strong>L {{ number_format($producto->precio_venta, 2) }}</strong>
                                            </td>

                                            <td>
                                                <button class="btn btn-primary btn-sm"
                                                        wire:click="agregarProducto({{ $producto->id }})">
                                                    Agregar
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">
                                                No hay productos disponibles.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if ($tipoFiltro === 'todos' || $tipoFiltro === 'servicios')
                        <h5>Servicios</h5>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Código</th>
                                        <th>Servicio</th>
                                        <th>Tipo</th>
                                        <th>Precio</th>
                                        <th width="90">Acción</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse ($servicios as $servicio)
                                        <tr>
                                            <td>
                                                <strong>{{ $servicio->codigo }}</strong>
                                            </td>

                                            <td>
                                                {{ $servicio->nombre }} <br>
                                                <small>
                                                    {{ $servicio->tamano_papel }} /
                                                    {{ $servicio->color }} /
                                                    {{ $servicio->caras }}
                                                </small>
                                            </td>

                                            <td>
                                                <span class="badge badge-info">
                                                    {{ $servicio->tipo_servicio }}
                                                </span>
                                            </td>

                                            <td>
                                                <strong>L {{ number_format($servicio->precio_unitario, 2) }}</strong>
                                            </td>

                                            <td>
                                                <button class="btn btn-primary btn-sm"
                                                        wire:click="agregarServicio({{ $servicio->id }})">
                                                    Agregar
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center">
                                                No hay servicios disponibles.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    @endif

                </div>
            </div>
        </div>

        {{-- Lado derecho: carrito --}}
        <div class="col-md-5">

            <div class="card">
                <div class="card-header bg-success">
                    <h3 class="card-title">
                        Venta actual
                    </h3>
                </div>

                <div class="card-body">

                    <div class="alert alert-warning">
                        <strong>Comprobante no fiscal.</strong><br>
                        Este módulo genera recibos internos tipo REC-000001.
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Método de pago</label>
                            <select class="form-control" wire:model.defer="metodo_pago">
                                @foreach ($metodosPago as $metodo)
                                    <option value="{{ $metodo }}">{{ $metodo }}</option>
                                @endforeach
                            </select>

                            @error('metodo_pago')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>Estado</label>
                            <select class="form-control" wire:model.defer="estado">
                                @foreach ($estadosVenta as $estadoOpcion)
                                    @if ($estadoOpcion !== 'Anulada')
                                        <option value="{{ $estadoOpcion }}">{{ $estadoOpcion }}</option>
                                    @endif
                                @endforeach
                            </select>

                            @error('estado')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Detalle</th>
                                    <th width="90">Cant.</th>
                                    <th width="100">Precio</th>
                                    <th width="95">Desc.</th>
                                    <th width="90">Total</th>
                                    <th width="40"></th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($carrito as $index => $item)
                                    <tr>
                                        <td>
                                            <span class="badge badge-{{ $item['tipo_item'] === 'Producto' ? 'primary' : 'info' }}">
                                                {{ $item['tipo_item'] }}
                                            </span>

                                            <br>

                                            <strong>{{ $item['descripcion'] }}</strong>

                                            <br>

                                            <small>
                                                {{ $item['codigo'] }}
                                            </small>
                                        </td>

                                        <td>
                                            <input type="number"
                                                   step="0.01"
                                                   min="0.01"
                                                   class="form-control form-control-sm"
                                                   wire:model.lazy="carrito.{{ $index }}.cantidad">

                                            <div class="btn-group btn-group-sm mt-1">
                                                <button type="button"
                                                        class="btn btn-secondary"
                                                        wire:click="disminuirCantidad({{ $index }})">
                                                    -
                                                </button>

                                                <button type="button"
                                                        class="btn btn-secondary"
                                                        wire:click="aumentarCantidad({{ $index }})">
                                                    +
                                                </button>
                                            </div>
                                        </td>

                                        <td>
                                            <input type="number"
                                                   step="0.01"
                                                   min="0"
                                                   class="form-control form-control-sm"
                                                   wire:model.lazy="carrito.{{ $index }}.precio_unitario">
                                        </td>

                                        <td>
                                            <input type="number"
                                                   step="0.01"
                                                   min="0"
                                                   class="form-control form-control-sm"
                                                   wire:model.lazy="carrito.{{ $index }}.descuento">
                                        </td>

                                        <td>
                                            <strong>
                                                L {{ number_format($item['total'], 2) }}
                                            </strong>
                                        </td>

                                        <td>
                                            <button type="button"
                                                    class="btn btn-danger btn-xs"
                                                    wire:click="eliminarItem({{ $index }})">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">
                                            No hay productos o servicios agregados.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="form-group">
                        <label>Descuento general</label>
                        <input type="number"
                               step="0.01"
                               min="0"
                               class="form-control"
                               wire:model.lazy="descuento_general">

                        @error('descuento_general')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label>Observación</label>
                        <textarea class="form-control"
                                  rows="2"
                                  placeholder="Observación de la venta..."
                                  wire:model.defer="observacion"></textarea>

                        @error('observacion')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <span>Subtotal:</span>
                        <strong>L {{ number_format($subtotal, 2) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>Descuento:</span>
                        <strong>L {{ number_format($descuento_total, 2) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between">
                        <span>Impuesto:</span>
                        <strong>L {{ number_format($impuesto, 2) }}</strong>
                    </div>

                    <div class="d-flex justify-content-between mt-2">
                        <h4>Total:</h4>
                        <h4>
                            <strong>L {{ number_format($total, 2) }}</strong>
                        </h4>
                    </div>

                    <hr>

                    <div class="row">
                        <div class="col-md-6">
                            <button type="button"
                                    class="btn btn-secondary btn-block"
                                    wire:click="limpiarCarrito">
                                Limpiar
                            </button>
                        </div>

                        <div class="col-md-6">
                            <button type="button"
                                    class="btn btn-success btn-block"
                                    wire:click="guardarVenta">
                                Guardar venta
                            </button>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
</div>