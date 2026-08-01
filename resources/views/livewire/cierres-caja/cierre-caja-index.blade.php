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

    @can('crear cierres caja')
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title">
                    <i class="fas fa-cash-register"></i>
                    Nuevo cierre de caja
                </h3>
            </div>

            <form wire:submit.prevent="registrarCierre">
                <div class="card-body">
                    <div class="alert alert-info">
                        <strong>Resumen:</strong>
                        el sistema calcula los ingresos y egresos del día seleccionado.
                        Usted solo debe ingresar el monto inicial, efectivo contado y cualquier ajuste adicional.
                    </div>

                    <div class="row">
                        <div class="form-group col-md-3">
                            <label>Fecha <span class="text-danger">*</span></label>
                            <input type="date"
                                   class="form-control"
                                   wire:model="fecha">

                            @error('fecha')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-3">
                            <label>Monto inicial <span class="text-danger">*</span></label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model="monto_inicial">

                            @error('monto_inicial')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-3">
                            <label>Efectivo contado <span class="text-danger">*</span></label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model="efectivo_contado">

                            @error('efectivo_contado')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="form-group col-md-3">
                            <label>Diferencia</label>
                            <input type="text"
                                   class="form-control {{ $diferencia < 0 ? 'is-invalid' : ($diferencia > 0 ? 'is-valid' : '') }}"
                                   value="L {{ number_format($diferencia, 2) }}"
                                   readonly>

                            @if ($diferencia > 0)
                                <small class="text-success">
                                    Sobrante de caja.
                                </small>
                            @elseif ($diferencia < 0)
                                <small class="text-danger">
                                    Faltante de caja.
                                </small>
                            @else
                                <small class="text-muted">
                                    Caja cuadrada.
                                </small>
                            @endif
                        </div>
                    </div>

                    <hr>

                    <h5>
                        <i class="fas fa-arrow-down text-success"></i>
                        Ingresos del día
                    </h5>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h4>L {{ number_format($ventas_efectivo, 2) }}</h4>
                                    <p>Ventas / abonos en efectivo</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h4>L {{ number_format($ventas_transferencia, 2) }}</h4>
                                    <p>Transferencias / depósitos</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-university"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="small-box bg-primary">
                                <div class="inner">
                                    <h4>L {{ number_format($ventas_tarjeta, 2) }}</h4>
                                    <p>Pagos con tarjeta / POS</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-credit-card"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="small-box bg-secondary">
                                <div class="inner">
                                    <h4>L {{ number_format($ventas_otros, 2) }}</h4>
                                    <p>Otros métodos de pago</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-wallet"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="form-group col-md-3">
                            <label>Otros ingresos</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model="otros_ingresos">

                            @error('otros_ingresos')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-success">
                                    <i class="fas fa-receipt"></i>
                                </span>

                                <div class="info-box-content">
                                    <span class="info-box-text">Pagos de ventas</span>
                                    <span class="info-box-number">
                                        {{ number_format($cantidad_pagos_ventas, 0) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-primary">
                                    <i class="fas fa-coins"></i>
                                </span>

                                <div class="info-box-content">
                                    <span class="info-box-text">Ingresos por ventas</span>
                                    <span class="info-box-number">
                                        L {{ number_format($total_ingresos_ventas, 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h5>
                        <i class="fas fa-arrow-up text-danger"></i>
                        Egresos del día
                    </h5>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h4>L {{ number_format($gastos_registrados, 2) }}</h4>
                                    <p>Gastos registrados</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-money-bill-wave"></i>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h4>L {{ number_format($pagos_proveedores, 2) }}</h4>
                                    <p>Pagos a proveedores</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-truck-loading"></i>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Otros egresos</label>
                            <input type="number"
                                   step="0.01"
                                   min="0"
                                   class="form-control"
                                   wire:model="otros_egresos">

                            @error('otros_egresos')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <div class="col-md-3">
                            <div class="info-box bg-light">
                                <span class="info-box-icon bg-danger">
                                    <i class="fas fa-list"></i>
                                </span>

                                <div class="info-box-content">
                                    <span class="info-box-text">Movimientos de egreso</span>
                                    <span class="info-box-number">
                                        {{ number_format($cantidad_gastos + $cantidad_pagos_proveedores, 0) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <h5>
                        <i class="fas fa-calculator"></i>
                        Resumen del cierre
                    </h5>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Observación</label>
                                <textarea class="form-control"
                                          rows="3"
                                          wire:model.defer="observacion"
                                          placeholder="Ej. Caja cuadrada, faltante pendiente de revisar, sobrante por vuelto, etc."></textarea>

                                @error('observacion')
                                    <small class="text-danger">{{ $message }}</small>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <table class="table table-bordered table-sm">
                                <tr>
                                    <th>Monto inicial</th>
                                    <td class="text-right">
                                        L {{ number_format($monto_inicial, 2) }}
                                    </td>
                                </tr>

                                <tr>
                                    <th>Total ingresos</th>
                                    <td class="text-right text-success">
                                        L {{ number_format($total_ingresos, 2) }}
                                    </td>
                                </tr>

                                <tr>
                                    <th>Total egresos</th>
                                    <td class="text-right text-danger">
                                        L {{ number_format($total_egresos, 2) }}
                                    </td>
                                </tr>

                                <tr>
                                    <th>Efectivo esperado</th>
                                    <td class="text-right">
                                        <strong>L {{ number_format($efectivo_esperado, 2) }}</strong>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Efectivo contado</th>
                                    <td class="text-right">
                                        <strong>L {{ number_format($efectivo_contado, 2) }}</strong>
                                    </td>
                                </tr>

                                <tr>
                                    <th>Diferencia</th>
                                    <td class="text-right">
                                        @if ($diferencia > 0)
                                            <strong class="text-success">
                                                L {{ number_format($diferencia, 2) }}
                                            </strong>
                                        @elseif ($diferencia < 0)
                                            <strong class="text-danger">
                                                L {{ number_format($diferencia, 2) }}
                                            </strong>
                                        @else
                                            <strong class="text-muted">
                                                L {{ number_format($diferencia, 2) }}
                                            </strong>
                                        @endif
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
                        <i class="fas fa-save"></i> Registrar cierre
                    </button>

                    <span wire:loading class="text-info ml-2">
                        Procesando...
                    </span>
                </div>
            </form>
        </div>
    @endcan

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-history"></i>
                Historial de cierres de caja
            </h3>
        </div>

        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-5">
                    <input type="text"
                           class="form-control"
                           placeholder="Buscar por código, fecha o estado..."
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
                            <th>Usuario</th>
                            <th class="text-right">Efectivo esperado</th>
                            <th class="text-right">Efectivo contado</th>
                            <th class="text-right">Diferencia</th>
                            <th>Estado</th>
                            <th width="170">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($cierres as $cierre)
                            <tr class="{{ $cierre->estado === 'Anulado' ? 'table-secondary' : '' }}">
                                <td>
                                    <strong>{{ $cierre->codigo }}</strong>
                                </td>

                                <td>
                                    {{ \Carbon\Carbon::parse($cierre->fecha)->format('d/m/Y') }}
                                </td>

                                <td>
                                    {{ $cierre->usuario->name ?? 'Sistema' }}
                                </td>

                                <td class="text-right">
                                    L {{ number_format($cierre->efectivo_esperado, 2) }}
                                </td>

                                <td class="text-right">
                                    L {{ number_format($cierre->efectivo_contado, 2) }}
                                </td>

                                <td class="text-right">
                                    @if ($cierre->diferencia > 0)
                                        <span class="text-success">
                                            <strong>L {{ number_format($cierre->diferencia, 2) }}</strong>
                                        </span>
                                    @elseif ($cierre->diferencia < 0)
                                        <span class="text-danger">
                                            <strong>L {{ number_format($cierre->diferencia, 2) }}</strong>
                                        </span>
                                    @else
                                        <span class="text-muted">
                                            L {{ number_format($cierre->diferencia, 2) }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    @if ($cierre->estado === 'Anulado')
                                        <span class="badge badge-danger">Anulado</span>
                                    @else
                                        <span class="badge badge-success">Cerrado</span>
                                    @endif
                                </td>

                                <td>
                                    <button type="button"
                                            class="btn btn-info btn-xs"
                                            wire:click="verDetalle({{ $cierre->id }})">
                                        <i class="fas fa-eye"></i> Ver
                                    </button>

                                    @can('imprimir cierres caja')
                                        <a href="{{ route('cierres-caja.imprimir', $cierre->id) }}"
                                        target="_blank"
                                        class="btn btn-secondary btn-xs">
                                            <i class="fas fa-print"></i> Imprimir
                                        </a>
                                    @endcan

                                    @can('anular cierres caja')
                                        @if ($cierre->estado !== 'Anulado')
                                            <button type="button"
                                                    class="btn btn-danger btn-xs"
                                                    wire:click="abrirAnular({{ $cierre->id }})">
                                                <i class="fas fa-ban"></i> Anular
                                            </button>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    No hay cierres de caja registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $cierres->links() }}
        </div>
    </div>

    @if ($mostrarModalDetalle && $cierreDetalle)
        <div class="modal fade show"
             style="display: block;"
             role="dialog"
             aria-modal="true">

            <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <div>
                            <h5 class="modal-title mb-0">
                                <i class="fas fa-cash-register"></i>
                                Detalle de cierre de caja
                            </h5>

                            <small class="text-muted">
                                Código: {{ $cierreDetalle->codigo }}
                            </small>
                        </div>

                        <button type="button" class="close" wire:click="cerrarModalDetalle">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body" style="max-height: calc(100vh - 220px); overflow-y: auto;">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-primary">
                                        <i class="fas fa-barcode"></i>
                                    </span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">Código</span>
                                        <span class="info-box-number">
                                            {{ $cierreDetalle->codigo }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-info">
                                        <i class="far fa-calendar-alt"></i>
                                    </span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">Fecha</span>
                                        <span class="info-box-number">
                                            {{ \Carbon\Carbon::parse($cierreDetalle->fecha)->format('d/m/Y') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon bg-secondary">
                                        <i class="fas fa-user"></i>
                                    </span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">Usuario</span>
                                        <span class="info-box-number">
                                            {{ $cierreDetalle->usuario->name ?? 'Sistema' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="info-box">
                                    <span class="info-box-icon {{ $cierreDetalle->estado === 'Anulado' ? 'bg-danger' : 'bg-success' }}">
                                        <i class="fas {{ $cierreDetalle->estado === 'Anulado' ? 'fa-ban' : 'fa-check' }}"></i>
                                    </span>

                                    <div class="info-box-content">
                                        <span class="info-box-text">Estado</span>
                                        <span class="info-box-number">
                                            {{ $cierreDetalle->estado }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($cierreDetalle->estado === 'Anulado')
                            <div class="alert alert-danger">
                                <h6>
                                    <i class="fas fa-ban"></i>
                                    Cierre anulado
                                </h6>

                                <div class="row">
                                    <div class="col-md-4">
                                        <strong>Fecha de anulación:</strong><br>
                                        {{ $cierreDetalle->fecha_anulacion ? \Carbon\Carbon::parse($cierreDetalle->fecha_anulacion)->format('d/m/Y H:i') : 'No registrada' }}
                                    </div>

                                    <div class="col-md-4">
                                        <strong>Anulado por:</strong><br>
                                        {{ $cierreDetalle->usuarioAnulacion->name ?? 'Sistema' }}
                                    </div>

                                    <div class="col-md-4">
                                        <strong>Motivo:</strong><br>
                                        {{ $cierreDetalle->motivo_anulacion ?: 'Sin motivo registrado' }}
                                    </div>
                                </div>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-outline card-success">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-arrow-down"></i>
                                            Ingresos
                                        </h3>
                                    </div>

                                    <div class="card-body p-0">
                                        <table class="table table-sm mb-0">
                                            <tr>
                                                <th>Monto inicial</th>
                                                <td class="text-right">
                                                    L {{ number_format($cierreDetalle->monto_inicial, 2) }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Ventas efectivo</th>
                                                <td class="text-right">
                                                    L {{ number_format($cierreDetalle->ventas_efectivo, 2) }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Ventas transferencia</th>
                                                <td class="text-right">
                                                    L {{ number_format($cierreDetalle->ventas_transferencia, 2) }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Ventas tarjeta / POS</th>
                                                <td class="text-right">
                                                    L {{ number_format($cierreDetalle->ventas_tarjeta, 2) }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Otros métodos de pago</th>
                                                <td class="text-right">
                                                    L {{ number_format($cierreDetalle->ventas_otros, 2) }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Otros ingresos</th>
                                                <td class="text-right">
                                                    L {{ number_format($cierreDetalle->otros_ingresos, 2) }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Total ingresos por ventas</th>
                                                <td class="text-right">
                                                    <strong>L {{ number_format($cierreDetalle->total_ingresos_ventas, 2) }}</strong>
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Total ingresos</th>
                                                <td class="text-right text-success">
                                                    <strong>L {{ number_format($cierreDetalle->total_ingresos, 2) }}</strong>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card card-outline card-danger">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-arrow-up"></i>
                                            Egresos
                                        </h3>
                                    </div>

                                    <div class="card-body p-0">
                                        <table class="table table-sm mb-0">
                                            <tr>
                                                <th>Gastos registrados</th>
                                                <td class="text-right">
                                                    L {{ number_format($cierreDetalle->gastos_registrados, 2) }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Pagos a proveedores</th>
                                                <td class="text-right">
                                                    L {{ number_format($cierreDetalle->pagos_proveedores, 2) }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Otros egresos</th>
                                                <td class="text-right">
                                                    L {{ number_format($cierreDetalle->otros_egresos, 2) }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Cantidad pagos ventas</th>
                                                <td class="text-right">
                                                    {{ number_format($cierreDetalle->cantidad_pagos_ventas, 0) }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Cantidad gastos</th>
                                                <td class="text-right">
                                                    {{ number_format($cierreDetalle->cantidad_gastos, 0) }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Cantidad pagos proveedores</th>
                                                <td class="text-right">
                                                    {{ number_format($cierreDetalle->cantidad_pagos_proveedores, 0) }}
                                                </td>
                                            </tr>

                                            <tr>
                                                <th>Total egresos</th>
                                                <td class="text-right text-danger">
                                                    <strong>L {{ number_format($cierreDetalle->total_egresos, 2) }}</strong>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-balance-scale"></i>
                                    Resultado de caja
                                </h3>
                            </div>

                            <div class="card-body p-0">
                                <table class="table table-bordered table-sm mb-0">
                                    <tr>
                                        <th>Efectivo esperado</th>
                                        <td class="text-right">
                                            L {{ number_format($cierreDetalle->efectivo_esperado, 2) }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Efectivo contado</th>
                                        <td class="text-right">
                                            L {{ number_format($cierreDetalle->efectivo_contado, 2) }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Diferencia</th>
                                        <td class="text-right">
                                            @if ($cierreDetalle->diferencia > 0)
                                                <strong class="text-success">
                                                    Sobrante: L {{ number_format($cierreDetalle->diferencia, 2) }}
                                                </strong>
                                            @elseif ($cierreDetalle->diferencia < 0)
                                                <strong class="text-danger">
                                                    Faltante: L {{ number_format($cierreDetalle->diferencia, 2) }}
                                                </strong>
                                            @else
                                                <strong class="text-muted">
                                                    Caja cuadrada: L {{ number_format($cierreDetalle->diferencia, 2) }}
                                                </strong>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="card card-outline card-secondary">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-comment-alt"></i>
                                    Observación
                                </h3>
                            </div>

                            <div class="card-body">
                                {{ $cierreDetalle->observacion ?: 'Sin observación registrada.' }}
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        @can('imprimir cierres caja')
                            <a href="{{ route('cierres-caja.imprimir', $cierreDetalle->id) }}"
                            target="_blank"
                            class="btn btn-secondary">
                                <i class="fas fa-print"></i> Imprimir cierre
                            </a>
                        @endcan

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
                        <h5 class="modal-title">
                            Anular cierre de caja
                        </h5>

                        <button type="button" class="close" wire:click="cerrarModalAnulacion">
                            <span>&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">
                        <div class="alert alert-warning">
                            Esta acción marcará el cierre como anulado.
                            No eliminará el registro.
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
</div>