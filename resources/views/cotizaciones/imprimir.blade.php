<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización {{ $cotizacion->codigo }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #000;
            margin: 0;
            padding: 20px;
        }

        .documento {
            max-width: 800px;
            margin: 0 auto;
            border: 1px solid #333;
            padding: 20px;
        }

        .botones {
            max-width: 800px;
            margin: 15px auto;
            text-align: right;
        }

        .btn {
            padding: 8px 14px;
            border: 1px solid #333;
            background: #f5f5f5;
            cursor: pointer;
            text-decoration: none;
            color: #000;
            font-size: 13px;
        }

        .encabezado {
            display: table;
            width: 100%;
            border-bottom: 2px solid #333;
            padding-bottom: 12px;
            margin-bottom: 18px;
        }

        .empresa {
            display: table-cell;
            width: 58%;
            vertical-align: top;
        }

        .cotizacion-info {
            display: table-cell;
            width: 42%;
            vertical-align: top;
            text-align: right;
        }

        .logo {
            max-height: 80px;
            max-width: 170px;
            margin-bottom: 8px;
        }

        .nombre-negocio {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .dato-negocio {
            font-size: 12px;
            margin: 2px 0;
        }

        .caja-cotizacion {
            border: 2px solid #333;
            padding: 10px;
            text-align: center;
        }

        .titulo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .numero {
            font-size: 18px;
            font-weight: bold;
        }

        .estado {
            display: inline-block;
            border: 1px solid #333;
            padding: 4px 8px;
            font-weight: bold;
            margin-top: 8px;
        }

        .seccion {
            margin-top: 16px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #f0f0f0;
        }

        th, td {
            border: 1px solid #333;
            padding: 6px;
        }

        .sin-borde td {
            border: none;
            padding: 3px 0;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-muted {
            color: #555;
        }

        .anulada {
            border: 3px solid #000;
            padding: 8px;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            margin-top: 12px;
        }

        .nota {
            font-size: 12px;
            color: #333;
            margin-top: 12px;
        }

        .firmas {
            margin-top: 45px;
            display: table;
            width: 100%;
        }

        .firma {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 0 25px;
        }

        .linea {
            border-top: 1px solid #333;
            padding-top: 5px;
        }

        @media print {
            .botones {
                display: none;
            }

            body {
                padding: 0;
            }

            .documento {
                border: none;
                max-width: 100%;
            }
        }
    </style>
</head>

<body>
    @php
        $cotizacionAnulada = $cotizacion->estado === 'Anulada';

        $clienteNombre = $cotizacion->cliente_nombre
            ?: ($cotizacion->cliente->nombre_completo ?? 'Cliente no registrado');

        $clienteNombre = trim(str_replace('No definido', '', $clienteNombre));

        $clienteTelefono = $cotizacion->cliente_telefono
            ?: ($cotizacion->cliente->telefono ?? null);

        $clienteEsAutomatico = $cotizacion->cliente
            && $cotizacion->cliente->notas
            && strpos($cotizacion->cliente->notas, 'Cliente creado automáticamente desde orden de trabajo') !== false;

        if ($clienteEsAutomatico && $clienteTelefono && substr($clienteTelefono, 0, 2) === '99') {
            $clienteTelefono = null;
        }
    @endphp

    <div class="botones">
        <button onclick="window.print()" class="btn">
            Imprimir
        </button>

        <a href="{{ route('cotizaciones.index') }}" class="btn">
            Volver
        </a>
    </div>

    <div class="documento">
        <div class="encabezado">
            <div class="empresa">
                @if ($configuracion->logo)
                    <img src="{{ asset('storage/' . $configuracion->logo) }}"
                         class="logo"
                         alt="Logo">
                @endif

                <div class="nombre-negocio">
                    {{ $configuracion->nombre_comercial }}
                </div>

                @if ($configuracion->nombre_legal)
                    <div class="dato-negocio">
                        {{ $configuracion->nombre_legal }}
                    </div>
                @endif

                @if ($configuracion->rtn)
                    <div class="dato-negocio">
                        <strong>RTN:</strong> {{ $configuracion->rtn }}
                    </div>
                @endif

                @if ($configuracion->direccion)
                    <div class="dato-negocio">
                        <strong>Dirección:</strong> {{ $configuracion->direccion }}
                    </div>
                @endif

                @if ($configuracion->telefono || $configuracion->whatsapp)
                    <div class="dato-negocio">
                        @if ($configuracion->telefono)
                            <strong>Tel:</strong> {{ $configuracion->telefono }}
                        @endif

                        @if ($configuracion->telefono && $configuracion->whatsapp)
                            |
                        @endif

                        @if ($configuracion->whatsapp)
                            <strong>WhatsApp:</strong> {{ $configuracion->whatsapp }}
                        @endif
                    </div>
                @endif

                @if ($configuracion->correo)
                    <div class="dato-negocio">
                        <strong>Correo:</strong> {{ $configuracion->correo }}
                    </div>
                @endif
            </div>

            <div class="cotizacion-info">
                <div class="caja-cotizacion">
                    <div class="titulo">
                        COTIZACIÓN
                    </div>

                    <div class="numero">
                        No.: {{ $cotizacion->codigo }}
                    </div>

                    <div class="estado">
                        {{ $cotizacion->estado }}
                    </div>
                </div>

                @if ($cotizacionAnulada)
                    <div class="anulada">
                        COTIZACIÓN ANULADA
                    </div>
                @endif
            </div>
        </div>

        <div class="seccion">
            <table class="sin-borde">
                <tr>
                    <td>
                        <strong>Fecha:</strong>
                        {{ \Carbon\Carbon::parse($cotizacion->fecha)->format('d/m/Y') }}
                    </td>

                    <td>
                        <strong>Válida hasta:</strong>
                        {{ $cotizacion->fecha_validez ? \Carbon\Carbon::parse($cotizacion->fecha_validez)->format('d/m/Y') : 'Sin fecha' }}
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong>Cliente:</strong>
                        {{ $clienteNombre ?: 'Cliente' }}
                    </td>

                    <td>
                        <strong>Teléfono:</strong>
                        {{ $clienteTelefono ?: '' }}
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong>Registrado por:</strong>
                        {{ $cotizacion->usuario->name ?? 'Sistema' }}
                    </td>

                    <td>
                        @if ($cotizacion->orden_trabajo_id)
                            <strong>Orden relacionada:</strong>
                            Orden #{{ $cotizacion->orden_trabajo_id }}
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="seccion">
            <strong>Título:</strong><br>
            {{ $cotizacion->titulo }}
        </div>

        <div class="seccion">
            <strong>Descripción:</strong><br>
            {{ $cotizacion->descripcion ?: 'Sin descripción' }}
        </div>

        @if ($cotizacionAnulada)
            <div class="seccion">
                <strong>Información de anulación:</strong><br>
                Fecha:
                {{ $cotizacion->fecha_anulacion ? \Carbon\Carbon::parse($cotizacion->fecha_anulacion)->format('d/m/Y H:i') : 'No registrada' }}
                <br>
                Anulado por:
                {{ $cotizacion->usuarioAnulacion->name ?? 'Sistema' }}
                <br>
                Motivo:
                {{ $cotizacion->motivo_anulacion ?: 'Sin motivo registrado' }}
            </div>
        @endif

        <div class="seccion">
            <table>
                <thead>
                    <tr>
                        <th width="12%">Tipo</th>
                        <th>Descripción</th>
                        <th width="12%">Cant.</th>
                        <th width="15%">Precio</th>
                        <th width="15%">Subtotal</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($cotizacion->detalles as $detalle)
                        <tr>
                            <td class="text-center">
                                {{ $detalle->tipo_item }}
                            </td>

                            <td>
                                {{ $detalle->descripcion }}

                                @if ($detalle->observacion)
                                    <br>
                                    <small class="text-muted">
                                        {{ $detalle->observacion }}
                                    </small>
                                @endif
                            </td>

                            <td class="text-right">
                                {{ number_format($detalle->cantidad, 2) }}
                            </td>

                            <td class="text-right">
                                L {{ number_format($detalle->precio_unitario, 2) }}
                            </td>

                            <td class="text-right">
                                L {{ number_format($detalle->subtotal, 2) }}
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

        <div class="seccion">
            <table style="width: 320px; margin-left: auto;">
                <tr>
                    <td><strong>Subtotal:</strong></td>
                    <td class="text-right">
                        L {{ number_format($cotizacion->subtotal, 2) }}
                    </td>
                </tr>

                <tr>
                    <td><strong>Descuento:</strong></td>
                    <td class="text-right">
                        L {{ number_format($cotizacion->descuento, 2) }}
                    </td>
                </tr>

                <tr>
                    <td><strong>Total:</strong></td>
                    <td class="text-right">
                        <strong>L {{ number_format($cotizacion->total, 2) }}</strong>
                    </td>
                </tr>
            </table>
        </div>

        @if ($cotizacion->condiciones)
            <div class="seccion">
                <strong>Condiciones:</strong><br>
                {{ $cotizacion->condiciones }}
            </div>
        @endif

        @if ($cotizacion->observacion)
            <div class="seccion">
                <strong>Observación interna:</strong><br>
                {{ $cotizacion->observacion }}
            </div>
        @endif

        <div class="nota">
            Esta cotización no representa una factura ni comprobante fiscal.
            Los precios pueden variar según disponibilidad de materiales, cambios solicitados o vigencia indicada.
        </div>

        <div class="firmas">
            <div class="firma">
                <div class="linea">
                    Firma del cliente
                </div>
            </div>

            <div class="firma">
                <div class="linea">
                    Autorizado por
                </div>
            </div>
        </div>
    </div>
</body>
</html>