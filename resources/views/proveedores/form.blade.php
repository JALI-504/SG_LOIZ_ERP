@extends('adminlte::page')

@section('title', $proveedor ? 'Editar proveedor' : 'Nuevo proveedor')

@section('content_header')
    <h1>{{ $proveedor ? 'Editar proveedor' : 'Nuevo proveedor' }}</h1>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                {{ $proveedor ? 'Modificar información del proveedor' : 'Registrar proveedor' }}
            </h3>
        </div>

        <form method="POST"
              action="{{ $proveedor ? route('proveedores.update', $proveedor->id) : route('proveedores.store') }}">
            @csrf

            @if ($proveedor)
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
                        <label>Nombre comercial <span class="text-danger">*</span></label>
                        <input type="text"
                               name="nombre_comercial"
                               class="form-control @error('nombre_comercial') is-invalid @enderror"
                               value="{{ old('nombre_comercial', $proveedor->nombre_comercial ?? '') }}">

                        @error('nombre_comercial')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label>Nombre legal</label>
                        <input type="text"
                               name="nombre_legal"
                               class="form-control @error('nombre_legal') is-invalid @enderror"
                               value="{{ old('nombre_legal', $proveedor->nombre_legal ?? '') }}">

                        @error('nombre_legal')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label>Tipo de proveedor <span class="text-danger">*</span></label>
                        <select name="tipo_proveedor"
                                class="form-control @error('tipo_proveedor') is-invalid @enderror">
                            @foreach ($tiposProveedor as $tipo)
                                <option value="{{ $tipo }}"
                                    {{ old('tipo_proveedor', $proveedor->tipo_proveedor ?? 'General') === $tipo ? 'selected' : '' }}>
                                    {{ $tipo }}
                                </option>
                            @endforeach
                        </select>

                        @error('tipo_proveedor')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <hr>

                <div class="form-row">
                    <div class="form-group col-md-3">
                        <label>RTN</label>
                        <input type="text"
                               name="rtn"
                               class="form-control @error('rtn') is-invalid @enderror"
                               value="{{ old('rtn', $proveedor->rtn ?? '') }}">

                        @error('rtn')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label>DNI</label>
                        <input type="text"
                               name="dni"
                               class="form-control @error('dni') is-invalid @enderror"
                               value="{{ old('dni', $proveedor->dni ?? '') }}">

                        @error('dni')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label>Teléfono</label>
                        <input type="text"
                               name="telefono"
                               class="form-control @error('telefono') is-invalid @enderror"
                               value="{{ old('telefono', $proveedor->telefono ?? '') }}">

                        @error('telefono')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group col-md-3">
                        <label>WhatsApp</label>
                        <input type="text"
                               name="whatsapp"
                               class="form-control @error('whatsapp') is-invalid @enderror"
                               value="{{ old('whatsapp', $proveedor->whatsapp ?? '') }}">

                        @error('whatsapp')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Correo</label>
                        <input type="email"
                               name="correo"
                               class="form-control @error('correo') is-invalid @enderror"
                               value="{{ old('correo', $proveedor->correo ?? '') }}">

                        @error('correo')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label>Persona contacto</label>
                        <input type="text"
                               name="persona_contacto"
                               class="form-control @error('persona_contacto') is-invalid @enderror"
                               value="{{ old('persona_contacto', $proveedor->persona_contacto ?? '') }}">

                        @error('persona_contacto')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label>Teléfono contacto</label>
                        <input type="text"
                               name="telefono_contacto"
                               class="form-control @error('telefono_contacto') is-invalid @enderror"
                               value="{{ old('telefono_contacto', $proveedor->telefono_contacto ?? '') }}">

                        @error('telefono_contacto')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Dirección</label>
                    <textarea name="direccion"
                              rows="2"
                              class="form-control @error('direccion') is-invalid @enderror">{{ old('direccion', $proveedor->direccion ?? '') }}</textarea>

                    @error('direccion')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="form-group">
                    <label>Observación</label>
                    <textarea name="observacion"
                              rows="3"
                              class="form-control @error('observacion') is-invalid @enderror">{{ old('observacion', $proveedor->observacion ?? '') }}</textarea>

                    @error('observacion')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('proveedores.index') }}" class="btn btn-secondary">
                    Cancelar
                </a>

                <button type="submit" class="btn btn-success">
                    {{ $proveedor ? 'Actualizar proveedor' : 'Guardar proveedor' }}
                </button>
            </div>
        </form>
    </div>
@stop