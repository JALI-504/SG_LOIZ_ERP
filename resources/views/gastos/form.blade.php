@extends('adminlte::page')

@section('title', $gasto ? 'Editar gasto' : 'Registrar gasto')

@section('content_header')
    <h1>{{ $gasto ? 'Editar gasto' : 'Registrar gasto' }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                {{ $gasto ? 'Modificar información del gasto' : 'Nuevo gasto del negocio' }}
            </h3>
        </div>

        <form method="POST"
              action="{{ $gasto ? route('gastos.update', $gasto->id) : route('gastos.store') }}">
            @csrf

            @if ($gasto)
                @method('PUT')
            @endif

            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        Revisa los campos marcados antes de guardar.
                    </div>
                @endif

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Fecha <span class="text-danger">*</span></label>
                        <input type="date"
                               name="fecha"
                               class="form-control @error('fecha') is-invalid @enderror"
                               value="{{ old('fecha', $gasto->fecha ?? now()->format('Y-m-d')) }}">

                        @error('fecha')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label>Categoría <span class="text-danger">*</span></label>
                        <select name="categoria"
                                class="form-control @error('categoria') is-invalid @enderror">
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria }}"
                                    {{ old('categoria', $gasto->categoria ?? '') === $categoria ? 'selected' : '' }}>
                                    {{ $categoria }}
                                </option>
                            @endforeach
                        </select>

                        @error('categoria')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label>Monto <span class="text-danger">*</span></label>
                        <input type="number"
                               step="0.01"
                               min="0.01"
                               name="monto"
                               class="form-control @error('monto') is-invalid @enderror"
                               value="{{ old('monto', $gasto->monto ?? '') }}">

                        @error('monto')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Descripción <span class="text-danger">*</span></label>
                    <input type="text"
                           name="descripcion"
                           class="form-control @error('descripcion') is-invalid @enderror"
                           placeholder="Ej: Pago de energía eléctrica, compra de cinta adhesiva..."
                           value="{{ old('descripcion', $gasto->descripcion ?? '') }}">

                    @error('descripcion')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Método de pago <span class="text-danger">*</span></label>
                        <select name="metodo_pago"
                                class="form-control @error('metodo_pago') is-invalid @enderror">
                            @foreach ($metodosPago as $metodo)
                                <option value="{{ $metodo }}"
                                    {{ old('metodo_pago', $gasto->metodo_pago ?? '') === $metodo ? 'selected' : '' }}>
                                    {{ $metodo }}
                                </option>
                            @endforeach
                        </select>

                        @error('metodo_pago')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label>Referencia</label>
                        <input type="text"
                               name="referencia"
                               class="form-control @error('referencia') is-invalid @enderror"
                               placeholder="Ej: transferencia, factura, recibo..."
                               value="{{ old('referencia', $gasto->referencia ?? '') }}">

                        @error('referencia')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Proveedor / persona</label>
                    <input type="text"
                           name="proveedor"
                           class="form-control @error('proveedor') is-invalid @enderror"
                           placeholder="Ej: ENEE, Hondutel, proveedor local..."
                           value="{{ old('proveedor', $gasto->proveedor ?? '') }}">

                    @error('proveedor')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Observación</label>
                    <textarea name="observacion"
                              rows="3"
                              class="form-control @error('observacion') is-invalid @enderror">{{ old('observacion', $gasto->observacion ?? '') }}</textarea>

                    @error('observacion')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('gastos.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-success">
                    {{ $gasto ? 'Actualizar gasto' : 'Guardar gasto' }}
                </button>
            </div>
        </form>
    </div>
@stop