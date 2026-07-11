<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de pago {{ $pago->compra->numero }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #000;
            margin: 0;
            padding: 20px;
        }

        .recibo {
            max-width: 720px;
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
            padding: 7px;
        }

        .sin-borde td {
            border: none;
            padding: 4px 0;
        }

        .total-final {
            font-size: 18px;
            font-weight: bold;
        }

        .botones {
            max-width: 720px;
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

        <a href="{{ route('compras.cuentas-por-pagar') }}" class="btn">
            Volver
        </a>
    </div>

    <div class="recibo">
        <div class="text-center">
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

            <div class="numero">
                COMPROBANTE DE PAGO A PROVEEDOR
            </div>

            <div class="no-fiscal">
                COMPROBANTE INTERNO NO FISCAL
            </div>
        </div>

        <div class="seccion">
            <table class="sin-borde">
                <tr>
                    <td>
                        <strong>Compra:</strong>
                        {{ $pago->compra->numero }}
                    </td>

                    <td>
                        <strong>Fecha pago:</strong>
                        {{ $pago->fecha }}
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong>Hora:</strong>
                        {{ $pago->hora }}
                    </td>

                    <td>
                        <strong>Método de pago:</strong>
                        {{ $pago->metodo_pago }}
                    </td>
                </tr>

                <tr>
                    <td colspan="2">
                        <strong>Proveedor:</strong>

                        @if ($pago->compra->proveedor)
                            {{ $pago->compra->proveedor->nombre_comercial }}

                            @if ($pago->compra->proveedor->rtn)
                                | RTN: {{ $pago->compra->proveedor->rtn }}
                            @endif

                            @if ($pago->compra->proveedor->telefono)
                                | Tel: {{ $pago->compra->proveedor->telefono }}
                            @endif
                        @else
                            Sin proveedor registrado
                        @endif
                    </td>
                </tr>

                @if ($pago->referencia)
                    <tr>
                        <td colspan="2">
                            <strong>Referencia:</strong>
                            {{ $pago->referencia }}
                        </td>
                    </tr>
                @endif
            </table>
        </div>

        <div class="seccion">
            <table>
                <thead>
                    <tr>
                        <th>Concepto</th>
                        <th class="text-right">Monto</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>Total de la compra</td>
                        <td class="text-right">
                            L {{ number_format($pago->compra->total, 2) }}
                        </td>
                    </tr>

                    <tr>
                        <td>Monto pagado en este abono</td>
                        <td class="text-right total-final">
                            L {{ number_format($pago->monto, 2) }}
                        </td>
                    </tr>

                    <tr>
                        <td>Total pagado acumulado</td>
                        <td class="text-right">
                            L {{ number_format($pago->compra->monto_pagado, 2) }}
                        </td>
                    </tr>

                    <tr>
                        <td>Saldo pendiente actual</td>
                        <td class="text-right">
                            L {{ number_format($pago->compra->saldo_pendiente, 2) }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if ($pago->observacion)
            <div class="seccion">
                <strong>Observación:</strong><br>
                {{ $pago->observacion }}
            </div>
        @endif

        @if ($configuracion->mensaje_recibo)
            <div class="seccion text-center">
                {{ $configuracion->mensaje_recibo }}
            </div>
        @endif

        <div class="seccion text-center text-muted">
            Este documento es un comprobante interno no fiscal.
        </div>
    </div>
</body>
</html>