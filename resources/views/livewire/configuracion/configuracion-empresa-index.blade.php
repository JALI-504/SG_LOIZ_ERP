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

    @if ($errors->any())
    <div class="alert alert-danger">
        <strong>No se pudo guardar la configuración.</strong>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div>
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">Datos generales del negocio</h3>
            </div>

            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Nombre comercial <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control"
                               wire:model.defer="nombre_comercial">

                        @error('nombre_comercial')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label>Nombre legal</label>
                        <input type="text"
                               class="form-control"
                               wire:model.defer="nombre_legal">

                        @error('nombre_legal')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>RTN</label>
                        <input type="text"
                               class="form-control"
                               wire:model.defer="rtn">

                        @error('rtn')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label>Teléfono</label>
                        <input type="text"
                               class="form-control"
                               wire:model.defer="telefono">

                        @error('telefono')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group col-md-4">
                        <label>WhatsApp</label>
                        <input type="text"
                               class="form-control"
                               wire:model.defer="whatsapp">

                        @error('whatsapp')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Correo</label>
                        <input type="email"
                               class="form-control"
                               wire:model.defer="correo">

                        @error('correo')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label>Descripción del negocio</label>
                        <input type="text"
                               class="form-control"
                               placeholder="Ej: Impresiones, productos personalizados y servicios gráficos"
                               wire:model.defer="descripcion_negocio">

                        @error('descripcion_negocio')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="form-group">
                    <label>Dirección</label>
                    <textarea class="form-control"
                              rows="2"
                              wire:model.defer="direccion"></textarea>

                    @error('direccion')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header bg-primary">
                    <h3 class="card-title">Modo fiscal y facturación</h3>
                </div>

                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Modo interno:</strong> genera recibos internos no fiscales.<br>
                        <strong>Modo fiscal:</strong> genera facturas con CAI, rango autorizado y fecha límite de emisión.
                    </div>

                    <div class="row">
                        <div class="form-group col-md-4">
                            <label>Modo fiscal</label>
                            <select class="form-control" wire:model="modo_fiscal">
                                @foreach ($modosFiscales as $modo)
                                    <option value="{{ $modo }}">
                                        {{ $modo }}
                                    </option>
                                @endforeach
                            </select>

                            @error('modo_fiscal')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-4">
                            <label>Documento de venta activo</label>
                            <input type="text"
                                class="form-control"
                                wire:model.defer="documento_venta_activo"
                                readonly>

                            <small class="text-muted">
                                Se asigna automáticamente según el modo fiscal.
                            </small>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Porcentaje ISV general</label>
                            <input type="number"
                                step="0.01"
                                min="0"
                                class="form-control"
                                wire:model.defer="porcentaje_isv_general">

                            @error('porcentaje_isv_general')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-4">
                            <div class="custom-control custom-switch">
                                <input type="checkbox"
                                    class="custom-control-input"
                                    id="usa_impuestos"
                                    wire:model="usa_impuestos">

                                <label class="custom-control-label" for="usa_impuestos">
                                    Usar impuestos
                                </label>
                            </div>
                        </div>

                        <div class="form-group col-md-4">
                            <div class="custom-control custom-switch">
                                <input type="checkbox"
                                    class="custom-control-input"
                                    id="usa_retenciones"
                                    wire:model="usa_retenciones">

                                <label class="custom-control-label" for="usa_retenciones">
                                    Usar retenciones
                                </label>
                            </div>
                        </div>

                        <div class="form-group col-md-4">
                            <div class="custom-control custom-switch">
                                <input type="checkbox"
                                    class="custom-control-input"
                                    id="precios_incluyen_isv"
                                    wire:model="precios_incluyen_isv">

                                <label class="custom-control-label" for="precios_incluyen_isv">
                                    Precios incluyen ISV
                                </label>
                            </div>
                        </div>
                    </div>

                    @if ($modo_fiscal === 'Fiscal')
                        <hr>

                        <h5>Datos de facturación fiscal</h5>

                        <div class="alert alert-warning">
                            Antes de activar facturación fiscal, asegúrate de tener CAI, rango autorizado y fecha límite emitidos por el SAR.
                        </div>

                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>CAI</label>
                                <input type="text"
                                    class="form-control"
                                    wire:model.defer="cai"
                                    placeholder="Código de autorización de impresión">

                                @error('cai')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group col-md-4">
                                <label>Rango desde</label>
                                <input type="text"
                                    class="form-control"
                                    wire:model.defer="rango_desde"
                                    placeholder="000-001-01-00000001">

                                @error('rango_desde')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group col-md-4">
                                <label>Rango hasta</label>
                                <input type="text"
                                    class="form-control"
                                    wire:model.defer="rango_hasta"
                                    placeholder="000-001-01-00000100">

                                @error('rango_hasta')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-4">
                                <label>Fecha límite de emisión</label>
                                <input type="date"
                                    class="form-control"
                                    wire:model.defer="fecha_limite_emision">

                                @error('fecha_limite_emision')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group col-md-2">
                                <label>Establecimiento</label>
                                <input type="text"
                                    maxlength="3"
                                    class="form-control"
                                    wire:model.defer="establecimiento"
                                    placeholder="000">

                                @error('establecimiento')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group col-md-2">
                                <label>Punto emisión</label>
                                <input type="text"
                                    maxlength="3"
                                    class="form-control"
                                    wire:model.defer="punto_emision"
                                    placeholder="001">

                                @error('punto_emision')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group col-md-2">
                                <label>Tipo doc.</label>
                                <input type="text"
                                    maxlength="2"
                                    class="form-control"
                                    wire:model.defer="tipo_documento_fiscal"
                                    placeholder="01">

                                @error('tipo_documento_fiscal')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>

                            <div class="form-group col-md-2">
                                <label>No. actual factura</label>
                                <input type="number"
                                    min="0"
                                    class="form-control"
                                    wire:model.defer="numero_actual_factura">

                                <small class="text-muted">
                                    Usa 0 para iniciar en el rango desde.
                                </small>

                                @error('numero_actual_factura')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>

        {{-- Logo --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Logotipo</h3>
            </div>

            <div class="card-body">
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Subir logo</label>
                        <input type="file"
                               class="form-control"
                               wire:model="logoNuevo"
                               accept="image/*">

                        @error('logoNuevo')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror

                        <small class="text-muted">
                            Formatos permitidos: JPG, JPEG, PNG. Tamaño máximo: 2 MB.
                        </small>

                        <div wire:loading wire:target="logoNuevo" class="text-info mt-2">
                            Cargando logo...
                        </div>
                    </div>

                    <div class="form-group col-md-6">
                        <label>Vista previa</label><br>

                        @if ($logoNuevo)
                            <img src="{{ $logoNuevo->temporaryUrl() }}"
                                 style="max-height: 120px; max-width: 220px;"
                                 class="img-thumbnail">
                        @elseif ($logo)
                            <img src="{{ asset('storage/' . $logo) }}"
                                 style="max-height: 120px; max-width: 220px;"
                                 class="img-thumbnail">

                            <br>

                            <button type="button"
                                    class="btn btn-danger btn-sm mt-2"
                                    wire:click="eliminarLogo">
                                Eliminar logo
                            </button>
                        @else
                            <div class="alert alert-secondary mb-0">
                                No hay logo cargado.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Recibos --}}
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Configuración de recibos internos</h3>
            </div>

            <div class="card-body">
                <div class="alert alert-warning">
                    Estos recibos son comprobantes internos no fiscales. La numeración será generada automáticamente usando el prefijo configurado.
                </div>

                <div class="form-row">
                    <div class="form-group col-md-4">
                        <label>Prefijo del recibo <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control"
                               wire:model.defer="prefijo_recibo">

                        @error('prefijo_recibo')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror

                        <small class="text-muted">
                            Ejemplo: REC genera REC-000001.
                        </small>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Número actual</label>
                        <input type="number"
                               min="0"
                               class="form-control"
                               wire:model.defer="numero_actual_recibo">

                        @error('numero_actual_recibo')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror

                        <small class="text-muted">
                            Si está en 5, el próximo recibo será 6.
                        </small>
                    </div>

                    <div class="form-group col-md-4">
                        <label>Próximo recibo estimado</label>
                        <input type="text"
                               class="form-control"
                               value="{{ strtoupper($prefijo_recibo ?: 'REC') }}-{{ str_pad(((int) $numero_actual_recibo) + 1, 6, '0', STR_PAD_LEFT) }}"
                               readonly>
                    </div>
                </div>

                <div class="form-group">
                    <label>Mensaje del recibo</label>
                    <textarea class="form-control"
                              rows="2"
                              wire:model.defer="mensaje_recibo"></textarea>

                    @error('mensaje_recibo')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
        </div>

        <div class="mb-4">
            <button type="button"
                    class="btn btn-primary"
                    wire:click="guardar"
                    wire:loading.attr="disabled">
                <i class="fas fa-save"></i> Guardar configuración
            </button>

            <span wire:loading wire:target="guardar" class="text-info ml-2">
                Guardando...
            </span>
        </div>
    </div>
</div>