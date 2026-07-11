@extends('adminlte::page')

@section('title', 'Nueva compra')

@section('content_header')
    <h1>Nueva compra</h1>
@stop

@section('content')
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <form method="POST" action="{{ route('compras.store') }}">
        @csrf

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Datos generales de la compra</h3>
            </div>

            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        Revisa los campos marcados antes de guardar.
                    </div>
                @endif

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Proveedor</label>
                        <select name="proveedor_id" class="form-control">
                            <option value="">Sin proveedor</option>

                            @foreach ($proveedores as $proveedor)
                                <option value="{{ $proveedor->id }}"
                                    {{ old('proveedor_id') == $proveedor->id ? 'selected' : '' }}>
                                    {{ $proveedor->nombre_comercial }}
                                </option>
                            @endforeach
                        </select>

                        @error('proveedor_id')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group col-md-2">
                        <label>Fecha <span class="text-danger">*</span></label>
                        <input type="date"
                               name="fecha"
                               class="form-control"
                               value="{{ old('fecha', now()->format('Y-m-d')) }}">

                        @error('fecha')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label>Tipo comprobante <span class="text-danger">*</span></label>
                        <select name="tipo_comprobante" class="form-control">
                            <option value="Recibo" {{ old('tipo_comprobante', 'Recibo') === 'Recibo' ? 'selected' : '' }}>Recibo</option>
                            <option value="Factura" {{ old('tipo_comprobante') === 'Factura' ? 'selected' : '' }}>Factura</option>
                            <option value="Cotización" {{ old('tipo_comprobante') === 'Cotización' ? 'selected' : '' }}>Cotización</option>
                            <option value="Nota de entrega" {{ old('tipo_comprobante') === 'Nota de entrega' ? 'selected' : '' }}>Nota de entrega</option>
                            <option value="Otro" {{ old('tipo_comprobante') === 'Otro' ? 'selected' : '' }}>Otro</option>
                        </select>

                        @error('tipo_comprobante')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label>Número comprobante</label>
                        <input type="text"
                               name="numero_comprobante"
                               class="form-control"
                               value="{{ old('numero_comprobante') }}"
                               placeholder="Ej: Factura #001-001...">

                        @error('numero_comprobante')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>Tipo de pago <span class="text-danger">*</span></label>
                        <select name="tipo_pago" class="form-control">
                            <option value="Contado" {{ old('tipo_pago', 'Contado') === 'Contado' ? 'selected' : '' }}>
                                Contado
                            </option>
                            <option value="Crédito" {{ old('tipo_pago') === 'Crédito' ? 'selected' : '' }}>
                                Crédito
                            </option>
                        </select>

                        @error('tipo_pago')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label>Método de pago <span class="text-danger">*</span></label>
                        <select name="metodo_pago" class="form-control">
                            @foreach ($metodosPago as $metodo)
                                <option value="{{ $metodo }}"
                                    {{ old('metodo_pago', 'Efectivo') === $metodo ? 'selected' : '' }}>
                                    {{ $metodo }}
                                </option>
                            @endforeach
                        </select>

                        @error('metodo_pago')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label>Monto pagado inicial</label>
                        <input type="number"
                               step="0.01"
                               min="0"
                               name="monto_pagado"
                               class="form-control"
                               value="{{ old('monto_pagado', 0) }}">

                        @error('monto_pagado')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror

                        <small class="text-muted">
                            Si es contado, el sistema tomará el total como pagado.
                        </small>
                    </div>
                </div>

                <div class="form-group">
                    <label>Observación</label>
                    <textarea name="observacion"
                              rows="2"
                              class="form-control">{{ old('observacion') }}</textarea>

                    @error('observacion')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Detalle de compra</h3>
            </div>

            <div class="card-body">
                <div class="alert alert-info">
                    Puedes llenar una o varias filas. Las filas vacías serán ignoradas.
                </div>

                @error('items')
                    <div class="alert alert-danger">{{ $message }}</div>
                @enderror

                <div class="table-responsive">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Item</th>
                                <th width="120">Cantidad</th>
                                <th width="150">Costo unitario</th>
                                <th width="120">Descuento</th>
                            </tr>
                        </thead>

                        <tbody>
                            @for ($i = 0; $i < 10; $i++)
                                <tr>
                                    <td>
                                        <select name="items[{{ $i }}][item_key]" class="form-control form-control-sm">
                                            <option value="">Seleccione...</option>

                                            <optgroup label="Insumos">
                                                @foreach ($insumos as $insumo)
                                                    <option value="Insumo|{{ $insumo->id }}"
                                                        {{ old('items.' . $i . '.item_key') === 'Insumo|' . $insumo->id ? 'selected' : '' }}>
                                                        {{ $insumo->codigo }} - {{ $insumo->nombre }}
                                                    </option>
                                                @endforeach
                                            </optgroup>

                                            <optgroup label="Productos">
                                                @foreach ($productos as $producto)
                                                    <option value="Producto|{{ $producto->id }}"
                                                        {{ old('items.' . $i . '.item_key') === 'Producto|' . $producto->id ? 'selected' : '' }}>
                                                        {{ $producto->codigo }} - {{ $producto->nombre }}
                                                    </option>
                                                @endforeach
                                            </optgroup>
                                        </select>

                                        @error('items.' . $i . '.item_key')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </td>

                                    <td>
                                        <input type="number"
                                               step="0.01"
                                               min="0.01"
                                               name="items[{{ $i }}][cantidad]"
                                               class="form-control form-control-sm"
                                               value="{{ old('items.' . $i . '.cantidad', $i === 0 ? 1 : '') }}">

                                        @error('items.' . $i . '.cantidad')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </td>

                                    <td>
                                        <input type="number"
                                               step="0.0001"
                                               min="0.0001"
                                               name="items[{{ $i }}][costo_unitario]"
                                               class="form-control form-control-sm"
                                               value="{{ old('items.' . $i . '.costo_unitario') }}">

                                        @error('items.' . $i . '.costo_unitario')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </td>

                                    <td>
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               name="items[{{ $i }}][descuento]"
                                               class="form-control form-control-sm"
                                               value="{{ old('items.' . $i . '.descuento', 0) }}">

                                        @error('items.' . $i . '.descuento')
                                            <small class="text-danger">{{ $message }}</small>
                                        @enderror
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>

                <div class="alert alert-warning">
                    El total de la compra será calculado automáticamente al guardar.
                </div>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('compras.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-success">
                    Guardar compra
                </button>
            </div>
        </div>
    </form>
@stop