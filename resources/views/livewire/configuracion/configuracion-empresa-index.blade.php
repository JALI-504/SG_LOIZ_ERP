<div>
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert">
                <span>&times;</span>
            </button>
        </div>
    @endif

    <form wire:submit.prevent="guardar" enctype="multipart/form-data">
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

        {{-- Facturación fiscal futura --}}
        <div class="card">
            <div class="card-header bg-secondary">
                <h3 class="card-title">Datos fiscales futuros</h3>
            </div>

            <div class="card-body">
                <div class="alert alert-info">
                    Esta sección queda preparada para cuando el negocio esté registrado y cuente con autorización fiscal.
                </div>

                <div class="form-group">
                    <div class="custom-control custom-switch">
                        <input type="checkbox"
                               class="custom-control-input"
                               id="usa_facturacion_fiscal"
                               wire:model.defer="usa_facturacion_fiscal">

                        <label class="custom-control-label" for="usa_facturacion_fiscal">
                            Usar facturación fiscal
                        </label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>CAI</label>
                        <input type="text"
                               class="form-control"
                               wire:model.defer="cai">

                        @error('cai')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label>Fecha límite de emisión</label>
                        <input type="date"
                               class="form-control"
                               wire:model.defer="fecha_limite_emision">

                        @error('fecha_limite_emision')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label>Rango desde</label>
                        <input type="text"
                               class="form-control"
                               wire:model.defer="rango_desde">

                        @error('rango_desde')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>

                    <div class="form-group col-md-6">
                        <label>Rango hasta</label>
                        <input type="text"
                               class="form-control"
                               wire:model.defer="rango_hasta">

                        @error('rango_hasta')
                            <small class="text-danger">{{ $message }}</small>
                        @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-4">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar configuración
            </button>
        </div>
    </form>
</div>