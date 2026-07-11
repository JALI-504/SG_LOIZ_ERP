<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recibo {{ $venta->numero }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #000;
            margin: 0;
            padding: 20px;
        }

        .recibo {
            max-width: 780px;
            margin: 0 auto;
            border: 1px solid #333;
            padding: 20px;
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

        .logo {
            max-height: 90px;
            max-width: 180px;
            margin-bottom: 8px;
        }

        .titulo-negocio {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .subtitulo {
            font-size: 14px;
            margin-bottom: 3px;
        }

        .numero {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }

        .no-fiscal {
            display: inline-block;
            border: 2px solid #000;
            padding: 5px 12px;
            margin-top: 8px;
            font-weight: bold;
            font-size: 13px;
        }

        .seccion {
            margin-top: 18px;
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

        .totales {
            width: 320px;
            margin-left: auto;
            margin-top: 15px;
        }

        .totales td {
            padding: 6px;
        }

        .total-final {
            font-size: 18px;
            font-weight: bold;
        }

        .botones {
            max-width: 780px;
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

        @media print {
            .botones {
                display: none;
            }

            body {
                padding: 0;
            }

            .recibo {
                border: none;
                max-width: 100%;
            }
        }
    </style>
</head>

<body>

    <div class="botones">
        <button onclick="window.print()" class="btn">
            Imprimir
        </button>

        <a href="{{ route('ventas.historial') }}" class="btn">
            Volver
        </a>
    </div>

    <div class="recibo">
        <div class="text-center">

            @if ($configuracion->logo)
                <img src="{{ asset('storage/' . $configuracion->logo) }}"
                     class="logo"
                     alt="Logo">
            @endif

            <div class="titulo-negocio">
                {{ $configuracion->nombre_comercial }}
            </div>

            @if ($configuracion->descripcion_negocio)
                <div class="subtitulo">
                    {{ $configuracion->descripcion_negocio }}
                </div>
            @endif

            @if ($configuracion->telefono || $configuracion->whatsapp)
                <div class="subtitulo">
                    @if ($configuracion->telefono)
                        Tel: {{ $configuracion->telefono }}
                    @endif

                    @if ($configuracion->telefono && $configuracion->whatsapp)
                        |
                    @endif

                    @if ($configuracion->whatsapp)
                        WhatsApp: {{ $configuracion->whatsapp }}
                    @endif
                </div>
            @endif

            @if ($configuracion->correo)
                <div class="subtitulo">
                    {{ $configuracion->correo }}
                </div>
            @endif

            @if ($configuracion->direccion)
                <div class="subtitulo">
                    {{ $configuracion->direccion }}
                </div>
            @endif

            @if ($configuracion->rtn)
                <div class="subtitulo">
                    RTN: {{ $configuracion->rtn }}
                </div>
            @endif

            <div class="subtitulo text-muted">
                {{ $venta->tipo_comprobante }}
            </div>

            <div class="numero">
                {{ $venta->numero }}
            </div>

            @if ($configuracion->usa_facturacion_fiscal && $configuracion->cai)
                <div class="no-fiscal">
                    DATOS FISCALES CONFIGURADOS
                </div>
            @else
                <div class="no-fiscal">
                    COMPROBANTE NO FISCAL
                </div>
            @endif
        </div>

        @if ($configuracion->usa_facturacion_fiscal && $configuracion->cai)
            <div class="seccion">
                <table class="sin-borde">
                    <tr>
                        <td>
                            <strong>CAI:</strong>
                            {{ $configuracion->cai }}
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <strong>Rango autorizado:</strong>
                            {{ $configuracion->rango_desde }}
                            a
                            {{ $configuracion->rango_hasta }}
                        </td>
                    </tr>

                    <tr>
                        <td>
                            <strong>Fecha límite de emisión:</strong>
                            {{ $configuracion->fecha_limite_emision }}
                        </td>
                    </tr>
                </table>
            </div>
        @endif

        <div class="seccion">
            <table class="sin-borde">
                <tr>
                    <td>
                        <strong>Fecha:</strong>
                        {{ $venta->fecha }}
                    </td>

                    <td>
                        <strong>Hora:</strong>
                        {{ $venta->hora }}
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong>Método de pago:</strong>
                        {{ $venta->metodo_pago }}
                    </td>

                    <td>
                        <strong>Estado:</strong>
                        {{ $venta->estado }}
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <strong>Cliente:</strong>

                        @if ($venta->cliente)
                            {{ trim($venta->cliente->primer_nombre . ' ' . $venta->cliente->segundo_nombre . ' ' . $venta->cliente->primer_apellido . ' ' . $venta->cliente->segundo_apellido) }}

                            @if ($venta->cliente->dni)
                                | DNI: {{ $venta->cliente->dni }}
                            @endif

                            @if ($venta->cliente->telefono)
                                | Tel: {{ $venta->cliente->telefono }}
                            @endif
                        @else
                            Consumidor final
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <div class="seccion">
            <table>
                <thead>
                    <tr>
                        <th width="10%">Cant.</th>
                        <th width="15%">Código</th>
                        <th>Descripción</th>
                        <th width="15%">Precio</th>
                        <th width="15%">Desc.</th>
                        <th width="15%">Total</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($venta->detalles as $detalle)
                        <tr>
                            <td class="text-center">
                                {{ number_format($detalle->cantidad, 2) }}
                            </td>

                            <td>
                                {{ $detalle->codigo }}
                            </td>

                            <td>
                                {{ $detalle->descripcion }}
                                <br>
                                <small class="text-muted">
                                    {{ $detalle->tipo_item }}
                                </small>
                            </td>

                            <td class="text-right">
                                L {{ number_format($detalle->precio_unitario, 2) }}
                            </td>

                            <td class="text-right">
                                L {{ number_format($detalle->descuento, 2) }}
                            </td>

                            <td class="text-right">
                                L {{ number_format($detalle->total, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <table class="totales">
            <tr>
                <td>
                    Subtotal:
                </td>

                <td class="text-right">
                    L {{ number_format($venta->subtotal, 2) }}
                </td>
            </tr>

            <tr>
                <td>
                    Descuento:
                </td>

                <td class="text-right">
                    L {{ number_format($venta->descuento, 2) }}
                </td>
            </tr>

            <tr>
                <td>
                    Impuesto:
                </td>

                <td class="text-right">
                    L {{ number_format($venta->impuesto, 2) }}
                </td>
            </tr>

            <tr>
                <td>
                    Pagado:
                </td>

                <td class="text-right">
                    L {{ number_format($venta->monto_pagado, 2) }}
                </td>
            </tr>

            <tr>
                <td>
                    Saldo pendiente:
                </td>

                <td class="text-right">
                    L {{ number_format($venta->saldo_pendiente, 2) }}
                </td>
            </tr>

            <tr class="total-final">
                <td>
                    Total:
                </td>

                <td class="text-right">
                    L {{ number_format($venta->total, 2) }}
                </td>
            </tr>
        </table>

        @if ($venta->observacion)
            <div class="seccion">
                <strong>Observación:</strong><br>
                {{ $venta->observacion }}
            </div>
        @endif

        @if ($configuracion->mensaje_recibo)
            <div class="seccion text-center">
                {{ $configuracion->mensaje_recibo }}
            </div>
        @endif

        <div class="seccion text-center text-muted">
            @if ($configuracion->usa_facturacion_fiscal && $configuracion->cai)
                Documento generado por el sistema.
            @else
                Este documento es un comprobante interno no fiscal.
                No sustituye factura fiscal autorizada.
            @endif
        </div>
    </div>

</body>
</html>