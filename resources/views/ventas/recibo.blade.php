<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
   <title>{{ $venta->es_fiscal ? 'Factura' : 'Recibo' }} {{ $venta->numero }}</title>

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

        .encabezado-documento {
            display: table;
            width: 100%;
            margin-bottom: 18px;
            border-bottom: 2px solid #333;
            padding-bottom: 12px;
        }

        .empresa-columna {
            display: table-cell;
            width: 58%;
            vertical-align: top;
            text-align: left;
        }

        .documento-columna {
            display: table-cell;
            width: 42%;
            vertical-align: top;
            text-align: right;
        }

        .nombre-negocio {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .dato-negocio {
            margin: 2px 0;
            font-size: 12px;
        }

        .caja-documento {
            border: 2px solid #333;
            padding: 10px;
            text-align: center;
        }

        .titulo-documento {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .leyenda-documento {
            font-size: 13px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .numero-documento {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .dato-fiscal {
            font-size: 12px;
            margin: 3px 0;
        }

        .no-fiscal-box {
            border: 2px solid #333;
            padding: 8px;
            margin-top: 8px;
            font-weight: bold;
            font-size: 12px;
            text-align: center;
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

    @php
    $esFiscal = (bool) $venta->es_fiscal;
    $tituloDocumento = $esFiscal ? 'FACTURA' : 'RECIBO INTERNO';
    $leyendaDocumento = $esFiscal
        ? 'DOCUMENTO FISCAL'
        : 'COMPROBANTE INTERNO NO FISCAL';
    @endphp

    <div class="botones">
        <button onclick="window.print()" class="btn">
            Imprimir
        </button>

        <a href="{{ route('ventas.historial') }}" class="btn">
            Volver
        </a>
    </div>

    <div class="recibo">
       <div class="encabezado-documento">

    <div class="empresa-columna">
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

                @if ($configuracion->descripcion_negocio)
                    <div class="dato-negocio text-muted">
                        {{ $configuracion->descripcion_negocio }}
                    </div>
                @endif
            </div>

            <div class="documento-columna">
                <div class="caja-documento">
                    <div class="titulo-documento">
                        {{ $tituloDocumento }}
                    </div>

                    <div class="leyenda-documento">
                        {{ $leyendaDocumento }}
                    </div>

                    <div class="numero-documento">
                        No.: {{ $venta->numero }}
                    </div>

                    @if ($esFiscal)
                        <div class="dato-fiscal">
                            <strong>CAI:</strong> {{ $venta->cai }}
                        </div>

                        <div class="dato-fiscal">
                            <strong>Rango autorizado:</strong><br>
                            {{ $venta->rango_autorizado_desde }}
                            al
                            {{ $venta->rango_autorizado_hasta }}
                        </div>

                        @if ($venta->fecha_limite_emision)
                            <div class="dato-fiscal">
                                <strong>Fecha límite:</strong>
                                {{ \Carbon\Carbon::parse($venta->fecha_limite_emision)->format('d/m/Y') }}
                            </div>
                        @endif
                    @else
                        <div class="no-fiscal-box">
                            COMPROBANTE INTERNO NO FISCAL
                        </div>
                    @endif
                </div>
            </div>

        </div>

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

                            @if ($venta->cliente->rtn)
                                | RTN: {{ $venta->cliente->rtn }}
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

        <table style="width: 100%; margin-top: 10px;">
            <tr>
                <td class="text-right"><strong>Subtotal bruto:</strong></td>
                <td class="text-right">
                    L {{ number_format($venta->subtotal, 2) }}
                </td>
            </tr>

            <tr>
                <td class="text-right"><strong>Descuento:</strong></td>
                <td class="text-right">
                    L {{ number_format($venta->descuento, 2) }}
                </td>
            </tr>

            @if ($venta->subtotal_gravado > 0 || $venta->subtotal_exento > 0 || $venta->subtotal_no_sujeto > 0 || $venta->isv_15 > 0)
                <tr>
                    <td class="text-right"><strong>Subtotal gravado:</strong></td>
                    <td class="text-right">
                        L {{ number_format($venta->subtotal_gravado, 2) }}
                    </td>
                </tr>

                <tr>
                    <td class="text-right"><strong>Subtotal exento:</strong></td>
                    <td class="text-right">
                        L {{ number_format($venta->subtotal_exento, 2) }}
                    </td>
                </tr>

                <tr>
                    <td class="text-right"><strong>Subtotal no sujeto:</strong></td>
                    <td class="text-right">
                        L {{ number_format($venta->subtotal_no_sujeto, 2) }}
                    </td>
                </tr>

                <tr>
                    <td class="text-right"><strong>ISV 15%:</strong></td>
                    <td class="text-right">
                        L {{ number_format($venta->isv_15, 2) }}
                    </td>
                </tr>
            @endif

            <tr>
                <td class="text-right"><strong>Total venta:</strong></td>
                <td class="text-right">
                    <strong>L {{ number_format($venta->total, 2) }}</strong>
                </td>
            </tr>

            @if ($venta->retencion > 0)
                <tr>
                    <td class="text-right"><strong>Retención:</strong></td>
                    <td class="text-right">
                        L {{ number_format($venta->retencion, 2) }}
                    </td>
                </tr>

                <tr>
                    <td class="text-right"><strong>Neto recibido:</strong></td>
                    <td class="text-right">
                        <strong>L {{ number_format($venta->neto_recibido, 2) }}</strong>
                    </td>
                </tr>
            @endif

            @if ($venta->monto_pagado > 0)
                <tr>
                    <td class="text-right"><strong>Monto pagado:</strong></td>
                    <td class="text-right">
                        L {{ number_format($venta->monto_pagado, 2) }}
                    </td>
                </tr>
            @endif

            @if ($venta->saldo_pendiente > 0)
                <tr>
                    <td class="text-right"><strong>Saldo pendiente:</strong></td>
                    <td class="text-right">
                        L {{ number_format($venta->saldo_pendiente, 2) }}
                    </td>
                </tr>
            @endif
        </table>

        @if ($venta->observacion)
            <div class="seccion">
                <strong>Observación:</strong><br>
                {{ $venta->observacion }}
            </div>
        @endif

        @if ($configuracion->mensaje_recibo)
            <p class="text-center" style="margin-top: 15px;">
                {{ $configuracion->mensaje_recibo }}
            </p>
        @endif

        @if (!$esFiscal)
            <p class="text-center" style="font-size: 11px;">
                Este documento es un comprobante interno y no constituye factura fiscal.
            </p>
        @endif

       <div class="seccion text-center text-muted">
            @if ($esFiscal)
                Documento fiscal generado por el sistema.
            @else
                Este documento es un comprobante interno no fiscal.
                No sustituye factura fiscal autorizada.
            @endif
        </div>
    </div>

</body>
</html>