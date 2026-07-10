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
            <h3 class="card-title">Listado de productos</h3>

            <div class="card-tools">
                <button class="btn btn-primary btn-sm" wire:click="create">
                    <i class="fas fa-plus"></i> Nuevo producto
                </button>
            </div>
        </div>

        <div class="card-body">

            <div class="row mb-3">
                <div class="col-md-4">
                    <input type="text"
                           class="form-control"
                           placeholder="Buscar por código, barra, nombre, categoría o tipo..."
                           wire:model.debounce.500ms="search">
                </div>

                <div class="col-md-3">
                    <select class="form-control" wire:model="filtroCategoria">
                        <option value="todas">Todas las categorías</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria }}">{{ $categoria }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <select class="form-control" wire:model="filtroTipo">
                        <option value="todos">Todos los tipos</option>
                        @foreach ($tiposProducto as $tipo)
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

                <div class="col-md-1">
                    <select class="form-control" wire:model="perPage">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Categoría / Tipo</th>
                            <th>Inventario</th>
                            <th>Costos</th>
                            <th>Precio</th>
                            <th>Utilidad</th>
                            <th>Estado</th>
                            <th width="190">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($productos as $producto)
                            <tr>
                                <td>
                                    <strong>{{ $producto->codigo }}</strong>

                                    @if ($producto->codigo_barra)
                                        <br>
                                        <small class="text-muted">
                                            Barra: {{ $producto->codigo_barra }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    <strong>{{ $producto->nombre }}</strong><br>

                                    @if ($producto->descripcion)
                                        <small>{{ $producto->descripcion }}</small><br>
                                    @endif

                                    @if ($producto->ancho_cm || $producto->largo_cm || $producto->espesor_mm)
                                        <small class="text-muted">
                                            Medidas:
                                            {{ $producto->ancho_cm ? number_format($producto->ancho_cm, 2) . ' cm' : '' }}
                                            {{ $producto->largo_cm ? ' x ' . number_format($producto->largo_cm, 2) . ' cm' : '' }}
                                            {{ $producto->espesor_mm ? ' / ' . number_format($producto->espesor_mm, 2) . ' mm' : '' }}
                                        </small>
                                    @endif
                                </td>

                                <td>
                                    <span class="badge badge-info">
                                        {{ $producto->categoria }}
                                    </span>
                                    <br>
                                    <span class="badge badge-primary mt-1">
                                        {{ $producto->tipo_producto }}
                                    </span>
                                    <br>
                                    <small class="text-muted">
                                        Venta: {{ $producto->unidad_venta }}
                                    </small>
                                </td>

                                <td>
                                    @if ($producto->maneja_inventario)
                                        @if ($producto->stock_bajo)
                                            <span class="badge badge-danger">Stock bajo</span><br>
                                        @else
                                            <span class="badge badge-success">Disponible</span><br>
                                        @endif

                                        <strong>
                                            {{ number_format($producto->stock_actual, 2) }}
                                            {{ $producto->unidad_venta }}
                                        </strong>
                                        <br>

                                        <small>
                                            Mínimo:
                                            {{ number_format($producto->stock_minimo, 2) }}
                                        </small>
                                    @else
                                        <span class="badge badge-secondary">
                                            No maneja inventario
                                        </span>
                                    @endif

                                    <br>

                                    @if ($producto->usa_receta)
                                        <span class="badge badge-warning mt-1">
                                            Usa receta
                                        </span>
                                    @else
                                        <span class="badge badge-light mt-1">
                                            Sin receta
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <small>
                                        Compra:
                                        <strong>L {{ number_format($producto->costo_compra, 2) }}</strong>
                                        <br>

                                        Unitario:
                                        <strong>L {{ number_format($producto->costo_unitario, 2) }}</strong>
                                    </small>
                                </td>

                                <td>
                                    <strong>
                                        L {{ number_format($producto->precio_venta, 2) }}
                                    </strong>
                                </td>

                                <td>
                                    <strong>
                                        L {{ number_format($producto->utilidad, 2) }}
                                    </strong>
                                    <br>
                                    <small>
                                        {{ number_format($producto->margen, 2) }}%
                                    </small>
                                </td>

                                <td>
                                    @if ($producto->activo)
                                        <span class="badge badge-success">Activo</span>
                                    @else
                                        <span class="badge badge-secondary">Inactivo</span>
                                    @endif
                                </td>

                                <td>
                                    <button class="btn btn-warning btn-xs"
                                            wire:click="edit({{ $producto->id }})">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    @if ($producto->usa_receta)
                                        <a href="{{ route('productos.insumos', $producto->id) }}"
                                        class="btn btn-info btn-xs">
                                            Insumos
                                        </a>
                                    @endif

                                    <button class="btn btn-{{ $producto->activo ? 'secondary' : 'success' }} btn-xs"
                                            wire:click="cambiarEstado({{ $producto->id }})">
                                        @if ($producto->activo)
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
                                    No hay productos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $productos->links() }}
        </div>
    </div>

    {{-- Modal producto --}}
    <div wire:ignore.self class="modal fade" id="productoModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <form wire:submit.prevent="{{ $producto_id ? 'update' : 'store' }}" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $modalTitle }}</h5>

                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    <h5>Datos principales</h5>
                    <hr>

                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Código <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   placeholder="Ej: USB-32GB"
                                   wire:model.defer="codigo">

                            @error('codigo')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-3">
                            <label>Código de barra</label>
                            <input type="text"
                                   class="form-control"
                                   placeholder="Opcional"
                                   wire:model.defer="codigo_barra">

                            @error('codigo_barra')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>Nombre del producto <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   placeholder="Ej: Memoria USB 32GB"
                                   wire:model.defer="nombre">

                            @error('nombre')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Categoría <span class="text-danger">*</span></label>
                            <select class="form-control" wire:model.defer="categoria">
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria }}">{{ $categoria }}</option>
                                @endforeach
                            </select>

                            @error('categoria')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-4">
                            <label>Tipo de producto <span class="text-danger">*</span></label>
                            <select class="form-control" wire:model="tipo_producto">
                                @foreach ($tiposProducto as $tipo)
                                    <option value="{{ $tipo }}">{{ $tipo }}</option>
                                @endforeach
                            </select>

                            @error('tipo_producto')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-4">
                            <label>Unidad de venta <span class="text-danger">*</span></label>
                            <select class="form-control" wire:model.defer="unidad_venta">
                                @foreach ($unidadesVenta as $unidad)
                                    <option value="{{ $unidad }}">{{ $unidad }}</option>
                                @endforeach
                            </select>

                            @error('unidad_venta')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <h5 class="mt-3">Comportamiento del producto</h5>
                    <hr>

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Maneja inventario</label>
                            <select class="form-control" wire:model="maneja_inventario">
                                <option value="1">Sí</option>
                                <option value="0">No</option>
                            </select>

                            @error('maneja_inventario')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror

                            <small class="text-muted">
                                Use “Sí” para productos que tienen stock físico.
                            </small>
                        </div>

                        <div class="form-group col-md-6">
                            <label>Usa receta de insumos</label>
                            <select class="form-control" wire:model="usa_receta">
                                <option value="1">Sí</option>
                                <option value="0">No</option>
                            </select>

                            @error('usa_receta')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror

                            <small class="text-muted">
                                Use “Sí” para productos fabricados o personalizados.
                            </small>
                        </div>
                    </div>

                    <h5 class="mt-3">Medidas opcionales</h5>
                    <hr>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Ancho en cm</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model.defer="ancho_cm">

                            @error('ancho_cm')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-4">
                            <label>Largo en cm</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model.defer="largo_cm">

                            @error('largo_cm')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-4">
                            <label>Espesor en mm</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model.defer="espesor_mm">

                            @error('espesor_mm')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <h5 class="mt-3">Inventario</h5>
                    <hr>

                    @if (!$maneja_inventario)
                        <div class="alert alert-secondary">
                            Este producto no maneja inventario. El stock se guardará en cero.
                        </div>
                    @endif

                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label>Stock actual</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model.defer="stock_actual"
                                   {{ !$maneja_inventario ? 'readonly' : '' }}>

                            @error('stock_actual')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-6">
                            <label>Stock mínimo</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model.defer="stock_minimo"
                                   {{ !$maneja_inventario ? 'readonly' : '' }}>

                            @error('stock_minimo')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <h5 class="mt-3">Costos y precio</h5>
                    <hr>

                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Costo compra</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model="costo_compra">

                            @error('costo_compra')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror

                            <small class="text-muted">
                                Para reventa, es el costo de compra del producto.
                            </small>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Costo unitario</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model.defer="costo_unitario"
                                   {{ !$usa_receta ? 'readonly' : '' }}>

                            @error('costo_unitario')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror

                            @if (!$usa_receta)
                                <small class="text-muted">
                                    Se iguala automáticamente al costo compra.
                                </small>
                            @else
                                <small class="text-muted">
                                    Luego se calculará con la receta de insumos.
                                </small>
                            @endif
                        </div>

                        <div class="form-group col-md-4">
                            <label>Precio venta <span class="text-danger">*</span></label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model.defer="precio_venta">

                            @error('precio_venta')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Descripción / observación</label>
                        <textarea class="form-control"
                                  rows="2"
                                  placeholder="Descripción opcional del producto..."
                                  wire:model.defer="descripcion"></textarea>

                        @error('descripcion')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        Cancelar
                    </button>

                    <button type="submit" class="btn btn-primary">
                        {{ $producto_id ? 'Actualizar producto' : 'Guardar producto' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>