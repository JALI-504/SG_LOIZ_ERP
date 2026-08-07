<div>
    <div class="alert alert-info">
        <strong>Resumen del día:</strong>
        {{ \Carbon\Carbon::parse($hoy)->format('d/m/Y') }}
    </div>

    {{-- Alertas administrativas --}}
    <div class="row">
        <div class="col-md-4">
            @if ($aperturaCajaAbierta)
                <div class="alert {{ $cajaPendienteCierre ? 'alert-danger' : 'alert-success' }}">
                    <strong>
                        <i class="fas fa-cash-register"></i>
                        Caja abierta:
                    </strong>
                    {{ $aperturaCajaAbierta->codigo }}

                    <br>
                    <small>
                        Fecha:
                        {{ \Carbon\Carbon::parse($aperturaCajaAbierta->fecha)->format('d/m/Y') }}
                        |
                        Monto inicial:
                        L {{ number_format($aperturaCajaAbierta->monto_inicial, 2) }}
                    </small>

                    <br>
                    <small>
                        Responsable:
                        {{ $aperturaCajaAbierta->usuario->name ?? 'Sistema' }}
                    </small>

                    @if ($cajaPendienteCierre)
                        <br>
                        <span class="badge badge-danger">
                            Caja pendiente de cierre
                        </span>
                    @endif
                </div>
            @else
                <div class="alert alert-warning">
                    <strong>
                        <i class="fas fa-exclamation-triangle"></i>
                        No hay caja abierta.
                    </strong>
                    <br>
                    <small>Debe realizar apertura de caja antes de operar el cierre.</small>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            @if ($ultimoRespaldo)
                <div class="alert {{ $respaldoPendiente ? 'alert-warning' : 'alert-success' }}">
                    <strong>
                        <i class="fas fa-database"></i>
                        Último respaldo:
                    </strong>
                    {{ $ultimoRespaldo->nombre_archivo }}

                    <br>
                    <small>
                        Generado:
                        {{ $ultimoRespaldo->fecha_generacion ? \Carbon\Carbon::parse($ultimoRespaldo->fecha_generacion)->format('d/m/Y H:i') : 'No registrado' }}
                    </small>

                    <br>
                    <small>
                        Por:
                        {{ $ultimoRespaldo->usuario->name ?? 'Sistema' }}
                        |
                        Hace:
                        {{ $diasUltimoRespaldo ?? 0 }} día(s)
                    </small>

                    @if ($respaldoPendiente)
                        <br>
                        <span class="badge badge-warning">
                            Se recomienda generar un respaldo reciente
                        </span>
                    @endif
                </div>
            @else
                <div class="alert alert-danger">
                    <strong>
                        <i class="fas fa-database"></i>
                        No hay respaldos registrados.
                    </strong>
                    <br>
                    <small>Genere un respaldo de la base de datos.</small>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="alert {{ ($totalProductosStockBajo + $totalInsumosStockBajo) > 0 ? 'alert-danger' : 'alert-success' }}">
                <strong>
                    <i class="fas fa-boxes"></i>
                    Alertas de stock:
                </strong>

                <br>
                <small>
                    Productos con stock bajo:
                    <strong>{{ number_format($totalProductosStockBajo, 0) }}</strong>
                </small>

                <br>
                <small>
                    Insumos con stock bajo:
                    <strong>{{ number_format($totalInsumosStockBajo, 0) }}</strong>
                </small>

                @if (($totalProductosStockBajo + $totalInsumosStockBajo) > 0)
                    <br>
                    <span class="badge badge-danger">
                        Revisar inventario
                    </span>
                @endif
            </div>
        </div>  
    </div>

    {{-- Accesos rápidos --}}
    <div class="mb-3">
        @can('crear ventas')
            <a href="{{ route('ventas.index') }}" class="btn btn-success">
                <i class="fas fa-cash-register"></i> Nueva venta
            </a>
        @endcan

        @can('ver aperturas caja')
            <a href="{{ route('aperturas-caja.index') }}" class="btn btn-success">
                <i class="fas fa-door-open"></i> Apertura caja
            </a>
        @endcan

        @can('ver cierres caja')
            <a href="{{ route('cierres-caja.index') }}" class="btn btn-dark">
                <i class="fas fa-cash-register"></i> Cierre caja
            </a>
        @endcan

        @can('ver respaldos')
            <a href="{{ route('respaldos.index') }}" class="btn btn-secondary">
                <i class="fas fa-database"></i> Respaldos
            </a>
        @endcan

        @can('ver cotizaciones')
            <a href="{{ route('cotizaciones.index') }}" class="btn btn-warning">
                <i class="fas fa-file-invoice-dollar"></i> Cotizaciones
            </a>
        @endcan

        @can('ver ordenes trabajo')
            <a href="{{ route('ordenes-trabajo.index') }}" class="btn btn-dark">
                <i class="fas fa-clipboard-list"></i> Órdenes
            </a>
        @endcan

        @can('ver historial ventas')
            <a href="{{ route('ventas.historial') }}" class="btn btn-primary">
                <i class="fas fa-receipt"></i> Historial ventas
            </a>
        @endcan

        @can('ver reporte ventas')
            <a href="{{ route('reportes.ventas') }}" class="btn btn-info">
                <i class="fas fa-chart-line"></i> Reporte ventas
            </a>
        @endcan

        @can('ver productos')
            <a href="{{ route('productos.index') }}" class="btn btn-secondary">
                <i class="fas fa-cube"></i> Productos
            </a>
        @endcan

        @can('ver insumos')
            <a href="{{ route('insumos.index') }}" class="btn btn-secondary">
                <i class="fas fa-boxes"></i> Insumos
            </a>
        @endcan
    </div>

    {{-- Resumen principal --}}
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ number_format($totalVentasHoy, 0) }}</h4>
                    <p>Ventas válidas hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>L {{ number_format($totalVendidoHoy, 2) }}</h4>
                    <p>Total vendido hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-coins"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>L {{ number_format($utilidadEstimadaHoy, 2) }}</h4>
                    <p>Utilidad estimada hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h4>L {{ number_format($ticketPromedioHoy, 2) }}</h4>
                    <p>Ticket promedio</p>
                </div>
                <div class="icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Resumen secundario --}}
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h4>L {{ number_format($costoEstimadoHoy, 2) }}</h4>
                    <p>Costo estimado hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-boxes"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>L {{ number_format($totalDescuentosHoy, 2) }}</h4>
                    <p>Descuentos hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-tags"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4>{{ number_format($ventasPendientesHoy, 0) }}</h4>
                    <p>Ventas pendientes hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clock"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4>{{ number_format($ventasAnuladasHoy, 0) }}</h4>
                    <p>Ventas anuladas hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-ban"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Resumen fiscal del día --}}
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>{{ number_format($totalFacturasFiscalesHoy, 0) }}</h4>
                    <p>Facturas fiscales hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h4>{{ number_format($totalRecibosInternosHoy, 0) }}</h4>
                    <p>Recibos internos hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>L {{ number_format($totalIsv15Hoy, 2) }}</h4>
                    <p>ISV generado hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-percent"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h4>L {{ number_format($totalNetoRecibidoHoy, 2) }}</h4>
                    <p>Neto recibido hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Flujo operativo del día --}}
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>L {{ number_format($totalGastosHoy, 2) }}</h4>
                    <p>Gastos hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4>L {{ number_format($totalPagosProveedoresActivosHoy, 2) }}</h4>
                    <p>Pagos proveedores hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-truck-loading"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h4>L {{ number_format($totalEgresosOperativosHoy, 2) }}</h4>
                    <p>Egresos operativos hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-wallet"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box {{ $flujoNetoEstimadoHoy >= 0 ? 'bg-info' : 'bg-danger' }}">
                <div class="inner">
                    <h4>L {{ number_format($flujoNetoEstimadoHoy, 2) }}</h4>
                    <p>Flujo neto estimado hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-chart-line"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Cuentas pendientes --}}
    @can('ver cuentas por pagar')
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4>L {{ number_format($cuentasPorPagarPendientes, 2) }}</h4>
                    <p>Cuentas por pagar pendientes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h4>{{ number_format($cantidadCuentasPorPagarPendientes, 0) }}</h4>
                    <p>Compras pendientes de pago</p>
                </div>
                <div class="icon">
                    <i class="fas fa-list"></i>
                </div>
            </div>
        </div>
    </div>
    @endcan

    {{-- Cuentas por cobrar --}}
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>L {{ number_format($cuentasPorCobrarPendientes, 2) }}</h4>
                    <p>Cuentas por cobrar</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hand-holding-usd"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ number_format($cantidadCuentasPorCobrarPendientes, 0) }}</h4>
                    <p>Ventas pendientes de cobro</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Cotizaciones y órdenes --}}
    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>{{ number_format($cotizacionesPendientes, 0) }}</h4>
                    <p>Cotizaciones pendientes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>{{ number_format($cotizacionesAprobadas, 0) }}</h4>
                    <p>Cotizaciones aprobadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box {{ $cotizacionesPorVencer > 0 ? 'bg-warning' : 'bg-secondary' }}">
                <div class="inner">
                    <h4>{{ number_format($cotizacionesPorVencer, 0) }}</h4>
                    <p>Cotizaciones por vencer</p>
                </div>
                <div class="icon">
                    <i class="fas fa-hourglass-half"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box {{ $cotizacionesVencidas > 0 ? 'bg-danger' : 'bg-secondary' }}">
                <div class="inner">
                    <h4>{{ number_format($cotizacionesVencidas, 0) }}</h4>
                    <p>Cotizaciones vencidas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-calendar-times"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>{{ number_format($ordenesPendientes, 0) }}</h4>
                    <p>Órdenes pendientes</p>
                </div>
                <div class="icon">
                    <i class="fas fa-clipboard-list"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ number_format($ordenesEnProceso, 0) }}</h4>
                    <p>Órdenes en proceso</p>
                </div>
                <div class="icon">
                    <i class="fas fa-cogs"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h4>{{ number_format($ordenesTerminadas, 0) }}</h4>
                    <p>Órdenes terminadas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-check"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box {{ $ordenesVencidas > 0 ? 'bg-danger' : 'bg-secondary' }}">
                <div class="inner">
                    <h4>{{ number_format($ordenesVencidas, 0) }}</h4>
                    <p>Órdenes vencidas</p>
                </div>
                <div class="icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="small-box {{ $ordenesParaEntregarHoy > 0 ? 'bg-success' : 'bg-secondary' }}">
                <div class="inner">
                    <h4>{{ number_format($ordenesParaEntregarHoy, 0) }}</h4>
                    <p>Órdenes para entregar hoy</p>
                </div>
                <div class="icon">
                    <i class="fas fa-truck"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Gráficas del Dashboard --}}
    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-chart-line"></i> Ventas de los últimos 7 días
                    </h3>
                </div>

                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="graficaVentas7Dias"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-outline card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-wallet"></i> Flujo de hoy
                    </h3>
                </div>

                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="graficaFlujoHoy"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-cube"></i> Productos más vendidos hoy
                    </h3>
                </div>

                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="graficaProductosHoy"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-concierge-bell"></i> Servicios más vendidos hoy
                    </h3>
                </div>

                <div class="card-body">
                    <div style="height: 300px;">
                        <canvas id="graficaServiciosHoy"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Seguimiento operativo --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card card-outline card-warning">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-file-invoice-dollar"></i>
                        Cotizaciones pendientes
                    </h3>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Código</th>
                                    <th>Cliente</th>
                                    <th>Validez</th>
                                    <th>Total</th>
                                    <th width="90">Acción</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($ultimasCotizacionesPendientes as $cotizacion)
                                    <tr>
                                        <td>
                                            <strong>{{ $cotizacion->codigo }}</strong>
                                        </td>

                                        <td>
                                            {{ $cotizacion->cliente_nombre ?: ($cotizacion->cliente->nombre_completo ?? 'Cliente no registrado') }}
                                        </td>

                                        <td>
                                            @if ($cotizacion->fecha_validez)
                                                {{ \Carbon\Carbon::parse($cotizacion->fecha_validez)->format('d/m/Y') }}

                                                @if ($cotizacion->fecha_validez < $hoy)
                                                    <br>
                                                    <span class="badge badge-danger">Vencida</span>
                                                @endif
                                            @else
                                                <span class="text-muted">Sin fecha</span>
                                            @endif
                                        </td>

                                        <td>
                                            <strong>L {{ number_format($cotizacion->total, 2) }}</strong>
                                        </td>

                                        <td>
                                            @can('imprimir cotizaciones')
                                                <a href="{{ route('cotizaciones.imprimir', $cotizacion->id) }}"
                                                target="_blank"
                                                class="btn btn-secondary btn-xs">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            No hay cotizaciones pendientes.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @can('ver cotizaciones')
                        <a href="{{ route('cotizaciones.index') }}" class="btn btn-warning btn-sm">
                            Ver cotizaciones
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clipboard-list"></i>
                        Órdenes próximas a entregar
                    </h3>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Código</th>
                                    <th>Cliente</th>
                                    <th>Entrega</th>
                                    <th>Estado</th>
                                    <th width="90">Acción</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($ordenesProximasEntrega as $orden)
                                    <tr>
                                        <td>
                                            <strong>{{ $orden->codigo }}</strong>
                                        </td>

                                        <td>
                                            {{ $orden->cliente_nombre ?: ($orden->cliente->nombre_completo ?? 'Cliente no registrado') }}
                                        </td>

                                        <td>
                                            {{ \Carbon\Carbon::parse($orden->fecha_entrega)->format('d/m/Y') }}

                                            @if ($orden->fecha_entrega < $hoy)
                                                <br>
                                                <span class="badge badge-danger">Vencida</span>
                                            @elseif ($orden->fecha_entrega == $hoy)
                                                <br>
                                                <span class="badge badge-success">Hoy</span>
                                            @endif
                                        </td>

                                        <td>
                                            <span class="badge badge-warning">
                                                {{ $orden->estado }}
                                            </span>
                                        </td>

                                        <td>
                                            @can('ver ordenes trabajo')
                                                <a href="{{ route('ordenes-trabajo.imprimir', $orden->id) }}"
                                                target="_blank"
                                                class="btn btn-secondary btn-xs">
                                                    <i class="fas fa-print"></i>
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">
                                            No hay órdenes próximas a entregar.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @can('ver ordenes trabajo')
                        <a href="{{ route('ordenes-trabajo.index') }}" class="btn btn-info btn-sm">
                            Ver órdenes
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Últimas ventas --}}
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Últimas ventas
                    </h3>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Fecha</th>
                                    <th>Número</th>
                                    <th>Cliente</th>
                                    <th>Estado</th>
                                    <th>Total</th>
                                    @can('imprimir recibos ventas')
                                        <th width="90">Acción</th>
                                    @endcan
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($ultimasVentas as $venta)
                                    <tr>
                                        <td>
                                            {{ $venta->fecha }}

                                            @if ($venta->hora)
                                                <br>
                                                <small>{{ $venta->hora }}</small>
                                            @endif
                                        </td>

                                        <td>
                                            <strong>{{ $venta->numero }}</strong><br>

                                            @if ($venta->es_fiscal)
                                                <span class="badge badge-success">
                                                    <i class="fas fa-file-invoice"></i> Factura fiscal
                                                </span>
                                            @else
                                                <span class="badge badge-secondary">
                                                    <i class="fas fa-receipt"></i> Recibo interno
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            @if ($venta->cliente)
                                                {{ trim($venta->cliente->primer_nombre . ' ' . $venta->cliente->segundo_nombre . ' ' . $venta->cliente->primer_apellido . ' ' . $venta->cliente->segundo_apellido) }}
                                            @else
                                                <span class="text-muted">Consumidor final</span>
                                            @endif
                                        </td>

                                        <td>
                                            @if ($venta->estado === 'Pagada')
                                                <span class="badge badge-success">Pagada</span>
                                            @elseif ($venta->estado === 'Pendiente')
                                                <span class="badge badge-warning">Pendiente</span>
                                            @elseif ($venta->estado === 'Anulada')
                                                <span class="badge badge-danger">Anulada</span>
                                            @else
                                                <span class="badge badge-secondary">
                                                    {{ $venta->estado }}
                                                </span>
                                            @endif
                                        </td>

                                        <td>
                                            <strong>L {{ number_format($venta->total, 2) }}</strong>
                                        </td>

                                        @can('imprimir recibos ventas')
                                        <td>
                                            <a href="{{ route('ventas.recibo', $venta->id) }}"
                                            target="_blank"
                                            class="btn btn-success btn-xs">
                                                @if ($venta->es_fiscal)
                                                    <i class="fas fa-file-invoice"></i> Factura
                                                @else
                                                    <i class="fas fa-receipt"></i> Recibo
                                                @endif
                                            </a>
                                        </td>
                                        @endcan
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ auth()->user()->can('imprimir recibos ventas') ? 6 : 5 }}" class="text-center">
                                            No hay ventas registradas.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @can('ver historial ventas')
                        <a href="{{ route('ventas.historial') }}" class="btn btn-primary btn-sm">
                            Ver historial completo
                        </a>
                    @endcan
                </div>
            </div>
        </div>

        {{-- Alertas de stock --}}
        <div class="col-md-5">
            <div class="card">
                <div class="card-header {{ ($totalProductosStockBajo + $totalInsumosStockBajo) > 0 ? 'bg-danger' : 'bg-success' }}">
                    <h3 class="card-title">
                        Alertas de inventario
                        @if (($totalProductosStockBajo + $totalInsumosStockBajo) > 0)
                            <span class="badge badge-light ml-2">
                                {{ number_format($totalProductosStockBajo + $totalInsumosStockBajo, 0) }}
                            </span>
                        @endif
                    </h3>
                </div>

                <div class="card-body">
                    <h5>
                        Productos con stock bajo
                        <span class="badge badge-danger">
                            {{ number_format($totalProductosStockBajo, 0) }}
                        </span>
                    </h5>

                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Producto</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($productosStockBajo as $producto)
                                    <tr>
                                        <td>
                                            <strong>{{ $producto->codigo }}</strong><br>
                                            {{ $producto->nombre }}
                                        </td>

                                        <td>
                                            <span class="badge badge-danger">
                                                {{ number_format($producto->stock_actual, 2) }}
                                            </span>
                                            <br>
                                            <small>
                                                Mínimo:
                                                {{ number_format($producto->stock_minimo, 2) }}
                                            </small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">
                                            No hay productos con stock bajo.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <h5>
                        Insumos con stock bajo
                        <span class="badge badge-danger">
                            {{ number_format($totalInsumosStockBajo, 0) }}
                        </span>
                    </h5>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover table-sm">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Insumo</th>
                                    <th>Stock</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($insumosStockBajo as $insumo)
                                    <tr>
                                        <td>
                                            <strong>{{ $insumo->codigo }}</strong><br>
                                            {{ $insumo->nombre }}
                                        </td>

                                        <td>
                                            <span class="badge badge-danger">
                                                {{ number_format($insumo->stock_actual, 2) }}
                                                {{ $insumo->unidad_consumo }}
                                            </span>
                                            <br>
                                            <small>
                                                Mínimo:
                                                {{ number_format($insumo->stock_minimo, 2) }}
                                                {{ $insumo->unidad_consumo }}
                                            </small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center">
                                            No hay insumos con stock bajo.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @can('ver insumos')
                        <a href="{{ route('insumos.index') }}" class="btn btn-secondary btn-sm">
                            Ver insumos
                        </a>
                    @endcan

                    @can('ver productos')
                        <a href="{{ route('productos.index') }}" class="btn btn-secondary btn-sm">
                            Ver productos
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    {{-- Más vendidos del día --}}
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Productos más vendidos hoy
                    </h3>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Código</th>
                                <th>Producto</th>
                                <th>Cant.</th>
                                <th>Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($productosMasVendidosHoy as $item)
                                <tr>
                                    <td>{{ $item->codigo }}</td>
                                    <td>{{ $item->descripcion }}</td>
                                    <td>{{ number_format($item->cantidad_total, 2) }}</td>
                                    <td>
                                        <strong>L {{ number_format($item->total_vendido, 2) }}</strong>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">
                                        No hay productos vendidos hoy.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        Servicios más vendidos hoy
                    </h3>
                </div>

                <div class="card-body">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="thead-dark">
                            <tr>
                                <th>Código</th>
                                <th>Servicio</th>
                                <th>Cant.</th>
                                <th>Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($serviciosMasVendidosHoy as $item)
                                <tr>
                                    <td>{{ $item->codigo }}</td>
                                    <td>{{ $item->descripcion }}</td>
                                    <td>{{ number_format($item->cantidad_total, 2) }}</td>
                                    <td>
                                        <strong>L {{ number_format($item->total_vendido, 2) }}</strong>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center">
                                        No hay servicios vendidos hoy.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    function cargarGraficasDashboard() {
        if (typeof Chart === 'undefined') {
            return;
        }

        if (window.graficasDashboard) {
            window.graficasDashboard.forEach(function (grafica) {
                grafica.destroy();
            });
        }

        window.graficasDashboard = [];

        var labels7Dias = @json($graficaVentas7DiasLabels);
        var ventas7Dias = @json($graficaVentas7DiasMontos);
        var neto7Dias = @json($graficaNetoRecibido7Dias);
        var egresos7Dias = @json($graficaEgresos7Dias);
        var flujo7Dias = @json($graficaFlujo7Dias);

        var productosLabels = @json($graficaProductosLabels);
        var productosCantidades = @json($graficaProductosCantidades);

        var serviciosLabels = @json($graficaServiciosLabels);
        var serviciosCantidades = @json($graficaServiciosCantidades);

        var flujoHoyLabels = [
            'Neto recibido',
            'Egresos operativos',
            'Flujo neto'
        ];

        var flujoHoyData = [
            Number(@json($totalNetoRecibidoHoy)),
            Number(@json($totalEgresosOperativosHoy)),
            Number(@json($flujoNetoEstimadoHoy))
        ];

        var canvasVentas = document.getElementById('graficaVentas7Dias');

        if (canvasVentas) {
            window.graficasDashboard.push(new Chart(canvasVentas, {
                type: 'line',
                data: {
                    labels: labels7Dias,
                    datasets: [
                        {
                            label: 'Total vendido',
                            data: ventas7Dias,
                            tension: 0.3
                        },
                        {
                            label: 'Neto recibido',
                            data: neto7Dias,
                            tension: 0.3
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            }));
        }

        var canvasFlujo = document.getElementById('graficaFlujoHoy');

        if (canvasFlujo) {
            window.graficasDashboard.push(new Chart(canvasFlujo, {
                type: 'bar',
                data: {
                    labels: flujoHoyLabels,
                    datasets: [
                        {
                            label: 'Monto',
                            data: flujoHoyData
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            }));
        }

        var canvasProductos = document.getElementById('graficaProductosHoy');

        if (canvasProductos) {
            window.graficasDashboard.push(new Chart(canvasProductos, {
                type: 'bar',
                data: {
                    labels: productosLabels,
                    datasets: [
                        {
                            label: 'Cantidad vendida',
                            data: productosCantidades
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y'
                }
            }));
        }

        var canvasServicios = document.getElementById('graficaServiciosHoy');

        if (canvasServicios) {
            window.graficasDashboard.push(new Chart(canvasServicios, {
                type: 'bar',
                data: {
                    labels: serviciosLabels,
                    datasets: [
                        {
                            label: 'Cantidad vendida',
                            data: serviciosCantidades
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y'
                }
            }));
        }
    }

    document.addEventListener('DOMContentLoaded', cargarGraficasDashboard);
    document.addEventListener('livewire:load', cargarGraficasDashboard);
</script>
</div>