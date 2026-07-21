<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <style>
       table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        th {
            background-color: #000000;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            border: 1px solid #000000;
            padding: 8px;
            white-space: nowrap;
        }

        td {
            border: 1px solid #999999;
            padding: 6px;
            vertical-align: middle;
        }

        .titulo {
            font-size: 18px;
            font-weight: bold;
            background-color: #d9d9d9;
            text-align: center;
        }

        .subtitulo {
            font-weight: bold;
            background-color: #eeeeee;
        }

        .texto {
            mso-number-format: "\@";
        }

        .numero {
            mso-number-format: "0.00";
        }

        .moneda {
            mso-number-format: '"L" #,##0.00';
        }

        .moneda4 {
            mso-number-format: '"L" #,##0.0000';
        }

        .fecha {
            mso-number-format: "dd/mm/yyyy";
        }

        .wrap {
            white-space: normal;
        }

        .factura {
            background-color: #d4edda;
            font-weight: bold;
        }

        .recibo {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .fiscal-header {
            background-color: #1f4e79;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
        }

        .detalle-header {
            background-color: #595959;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
        }

        .venta-header {
            background-color: #385723;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
        }
    </style>
</head>

<body>
    <table>
        <tr>
            <td colspan="33" class="titulo">
                REPORTE DE VENTAS
            </td>
        </tr>

        <tr>
            <td colspan="4" class="subtitulo">Desde</td>
            <td colspan="4">{{ $fechaDesde }}</td>
            <td colspan="4" class="subtitulo">Hasta</td>
            <td colspan="4">{{ $fechaHasta }}</td>
            <td colspan="17" class="subtitulo">Generado: {{ $generado }}</td>
        </tr>

        <tr>
            <td colspan="33"></td>
        </tr>

        <tr>
            <td colspan="12" class="fiscal-header">Datos del comprobante</td>
            <td colspan="15" class="detalle-header">Detalle vendido</td>
            <td colspan="6" class="venta-header">Totales fiscales de la venta</td>
        </tr>

        <tr>
            <th style="width: 90px;">Fecha</th>
            <th style="width: 80px;">Hora</th>
            <th style="width: 140px;">Número</th>
            <th style="width: 130px;">Comprobante</th>
            <th style="width: 130px;">Fiscal / Interno</th>
            <th style="width: 180px;">CAI</th>
            <th style="width: 150px;">Rango desde</th>
            <th style="width: 150px;">Rango hasta</th>
            <th style="width: 120px;">Fecha límite</th>
            <th style="width: 200px;">Cliente</th>
            <th style="width: 120px;">Método pago</th>
            <th style="width: 100px;">Estado</th>

            <th style="width: 100px;">Tipo</th>
            <th style="width: 100px;">Código</th>
            <th style="width: 260px;">Descripción</th>
            <th style="width: 90px;">Cantidad</th>
            <th style="width: 120px;">Precio unitario</th>
            <th style="width: 120px;">Costo unitario</th>
            <th style="width: 130px;">Tipo impuesto</th>
            <th style="width: 80px;">% ISV</th>
            <th style="width: 150px;">Subtotal gravado detalle</th>
            <th style="width: 150px;">Subtotal exento detalle</th>
            <th style="width: 160px;">Subtotal no sujeto detalle</th>
            <th style="width: 120px;">ISV detalle</th>
            <th style="width: 120px;">Descuento</th>
            <th style="width: 120px;">Total línea</th>
            <th style="width: 130px;">Utilidad estimada</th>

            <th style="width: 150px;">Subtotal gravado venta</th>
            <th style="width: 150px;">Subtotal exento venta</th>
            <th style="width: 160px;">Subtotal no sujeto venta</th>
            <th style="width: 120px;">ISV venta</th>
            <th style="width: 120px;">Retención</th>
            <th style="width: 130px;">Neto recibido</th>
        </tr>

        @forelse ($detalles as $detalle)
            <tr>
                <td class="fecha">{{ $detalle->fecha }}</td>
                <td class="texto">{{ $detalle->hora }}</td>
                <td class="texto">{{ $detalle->numero }}</td>
                <td>{{ $detalle->tipo_comprobante ?? '' }}</td>

                <td class="{{ ($detalle->es_fiscal ?? false) ? 'factura' : 'recibo' }}">
                    {{ ($detalle->es_fiscal ?? false) ? 'Factura fiscal' : 'Recibo interno' }}
                </td>

                <td class="texto">{{ $detalle->cai ?? '' }}</td>
                <td class="texto">{{ $detalle->rango_autorizado_desde ?? '' }}</td>
                <td class="texto">{{ $detalle->rango_autorizado_hasta ?? '' }}</td>
                <td class="fecha">{{ $detalle->fecha_limite_emision ?? '' }}</td>

                <td class="wrap">{{ $detalle->cliente ?: 'Consumidor final' }}</td>
                <td>{{ $detalle->metodo_pago }}</td>
                <td>{{ $detalle->estado }}</td>

                <td>{{ $detalle->tipo_item }}</td>
                <td class="texto">{{ $detalle->codigo }}</td>
                <td class="wrap">{{ $detalle->descripcion }}</td>

                <td class="numero">
                    {{ number_format((float) $detalle->cantidad, 2, '.', '') }}
                </td>

                <td class="moneda">
                    {{ number_format((float) $detalle->precio_unitario, 2, '.', '') }}
                </td>

                <td class="moneda4">
                    {{ number_format((float) $detalle->costo_unitario, 4, '.', '') }}
                </td>

                <td>{{ $detalle->tipo_impuesto ?? '' }}</td>

                <td class="numero">
                    {{ number_format((float) ($detalle->porcentaje_isv ?? 0), 2, '.', '') }}
                </td>

                <td class="moneda">
                    {{ number_format((float) ($detalle->detalle_subtotal_gravado ?? 0), 2, '.', '') }}
                </td>

                <td class="moneda">
                    {{ number_format((float) ($detalle->detalle_subtotal_exento ?? 0), 2, '.', '') }}
                </td>

                <td class="moneda">
                    {{ number_format((float) ($detalle->detalle_subtotal_no_sujeto ?? 0), 2, '.', '') }}
                </td>

                <td class="moneda">
                    {{ number_format((float) ($detalle->detalle_impuesto ?? 0), 2, '.', '') }}
                </td>

                <td class="moneda">
                    {{ number_format((float) $detalle->descuento, 2, '.', '') }}
                </td>

                <td class="moneda">
                    {{ number_format((float) $detalle->total, 2, '.', '') }}
                </td>

                <td class="moneda">
                    {{ number_format((float) $detalle->utilidad, 2, '.', '') }}
                </td>

                <td class="moneda">
                    {{ number_format((float) ($detalle->subtotal_gravado ?? 0), 2, '.', '') }}
                </td>

                <td class="moneda">
                    {{ number_format((float) ($detalle->subtotal_exento ?? 0), 2, '.', '') }}
                </td>

                <td class="moneda">
                    {{ number_format((float) ($detalle->subtotal_no_sujeto ?? 0), 2, '.', '') }}
                </td>

                <td class="moneda">
                    {{ number_format((float) ($detalle->isv_15 ?? 0), 2, '.', '') }}
                </td>

                <td class="moneda">
                    {{ number_format((float) ($detalle->retencion ?? 0), 2, '.', '') }}
                </td>

                <td class="moneda">
                    {{ number_format((float) ($detalle->neto_recibido ?? 0), 2, '.', '') }}
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="33" style="text-align: center;">
                    No hay ventas registradas con los filtros seleccionados.
                </td>
            </tr>
        @endforelse

        <tr>
            <td colspan="33"></td>
        </tr>

        <tr>
            <td colspan="33" class="subtitulo">
                Nota: los valores fiscales de la venta se repiten por cada línea de detalle para facilitar filtros y análisis en Excel.
            </td>
        </tr>
    </table>
</body>
</html>