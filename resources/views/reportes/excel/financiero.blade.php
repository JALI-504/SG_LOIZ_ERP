<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <style>
        table {
            border-collapse: collapse;
            width: auto;
            font-family: Arial, sans-serif;
            font-size: 12pt !important;
            table-layout: fixed;
            border: 2pt solid #000000;
        }

        th {
            background-color: #000000;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            border: 1pt solid #000000;
            padding: 8px;
            white-space: nowrap;
            font-size: 12pt !important;
            mso-font-charset: 0;
        }

        td {
            border: 1pt solid #000000;
            padding: 6px;
            vertical-align: middle;
            font-size: 12pt !important;
            white-space: normal;
            mso-font-charset: 0;
        }

        .titulo {
            font-size: 14pt !important;
            font-weight: bold;
            background-color: #d9d9d9;
            text-align: center;
            border: 2pt solid #000000;
        }

        .subtitulo {
            font-weight: bold;
            background-color: #eeeeee;
            font-size: 12pt !important;
            border: 1pt solid #000000;
        }

        .seccion {
            background-color: #1f4e79;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            font-size: 12pt !important;
            border: 2pt solid #000000;
        }

        .seccion-verde {
            background-color: #385723;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            font-size: 12pt !important;
            border: 2pt solid #000000;
        }

        .seccion-naranja {
            background-color: #bf8f00;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
            font-size: 12pt !important;
            border: 2pt solid #000000;
        }

        .positivo {
            background-color: #d4edda;
            font-weight: bold;
            border: 1pt solid #000000;
        }

        .negativo {
            background-color: #f8d7da;
            font-weight: bold;
            border: 1pt solid #000000;
        }

        .resaltado {
            background-color: #fff2cc;
            font-weight: bold;
            border: 1pt solid #000000;
        }

        .col-concepto {
            width: 700px;
        }

        .col-cantidad {
            width: 300px;
        }

        .col-monto {
            width: 350px;
        }

        .col-observacion {
            width: 930px;
        }
                .numero {
            mso-number-format: "0";
            text-align: center;
            white-space: nowrap;
        }

        .moneda {
            mso-number-format: '"L." #,##0.00';
            text-align: right;
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <table>
         <colgroup>
            <col class="col-concepto">
            <col class="col-cantidad">
            <col class="col-monto">
            <col class="col-observacion">
        </colgroup>
        <tr>
            <td colspan="4" class="titulo">
                REPORTE FINANCIERO
            </td>
        </tr>

        <tr>
            <td class="subtitulo">Desde</td>
            <td>{{ $fechaDesde }}</td>
            <td class="subtitulo">Hasta</td>
            <td>{{ $fechaHasta }}</td>
        </tr>

        <tr>
            <td colspan="4"></td>
        </tr>

        <tr>
            <td colspan="4" class="seccion">
                RESUMEN FINANCIERO GENERAL
            </td>
        </tr>

        <tr>
            <th>Concepto</th>
            <th>Cantidad</th>
            <th>Monto</th>
            <th>Observación</th>
        </tr>

        <tr>
            <td>Ventas válidas</td>
            <td class="numero">{{ number_format($cantidadVentas, 0, '.', '') }}</td>
            <td class="moneda">{{ number_format($totalVentas, 2, '.', '') }}</td>
            <td>Ventas no anuladas del período</td>
        </tr>

        <tr>
            <td>Descuentos en ventas</td>
            <td></td>
            <td class="moneda">{{ number_format($totalDescuentosVentas, 2, '.', '') }}</td>
            <td>Total de descuentos aplicados</td>
        </tr>

        <tr>
            <td>Costo de ventas</td>
            <td></td>
            <td class="moneda">{{ number_format($costoVentas, 2, '.', '') }}</td>
            <td>Costo estimado según detalle de ventas</td>
        </tr>

        <tr>
            <td class="resaltado">Utilidad bruta estimada</td>
            <td class="resaltado"></td>
            <td class="moneda resaltado">{{ number_format($utilidadBruta, 2, '.', '') }}</td>
            <td class="resaltado">Total ventas - costo ventas</td>
        </tr>

        <tr>
            <td>Gastos registrados</td>
            <td class="numero">{{ number_format($cantidadGastos, 0, '.', '') }}</td>
            <td class="moneda">{{ number_format($totalGastos, 2, '.', '') }}</td>
            <td>Gastos con estado registrado</td>
        </tr>

        <tr>
            <td class="{{ $utilidadNetaEstimada >= 0 ? 'positivo' : 'negativo' }}">
                Utilidad neta estimada
            </td>
            <td class="{{ $utilidadNetaEstimada >= 0 ? 'positivo' : 'negativo' }}"></td>
            <td class="moneda {{ $utilidadNetaEstimada >= 0 ? 'positivo' : 'negativo' }}">
                {{ number_format($utilidadNetaEstimada, 2, '.', '') }}
            </td>
            <td class="{{ $utilidadNetaEstimada >= 0 ? 'positivo' : 'negativo' }}">
                Utilidad bruta - gastos
            </td>
        </tr>

        <tr>
            <td colspan="4"></td>
        </tr>

        <tr>
            <td colspan="4" class="seccion-verde">
                DESGLOSE FISCAL DE VENTAS
            </td>
        </tr>

        <tr>
            <th>Concepto</th>
            <th>Cantidad</th>
            <th>Monto</th>
            <th>Observación</th>
        </tr>

        <tr>
            <td>Facturas fiscales</td>
            <td class="numero">{{ number_format($totalFacturasFiscales, 0, '.', '') }}</td>
            <td></td>
            <td>Ventas emitidas como factura fiscal</td>
        </tr>

        <tr>
            <td>Recibos internos</td>
            <td class="numero">{{ number_format($totalRecibosInternos, 0, '.', '') }}</td>
            <td></td>
            <td>Comprobantes internos no fiscales</td>
        </tr>

        <tr>
            <td>Subtotal gravado</td>
            <td></td>
            <td class="moneda">{{ number_format($totalSubtotalGravado, 2, '.', '') }}</td>
            <td>Base gravada del período</td>
        </tr>

        <tr>
            <td>Subtotal exento</td>
            <td></td>
            <td class="moneda">{{ number_format($totalSubtotalExento, 2, '.', '') }}</td>
            <td>Ventas exentas</td>
        </tr>

        <tr>
            <td>Subtotal no sujeto</td>
            <td></td>
            <td class="moneda">{{ number_format($totalSubtotalNoSujeto, 2, '.', '') }}</td>
            <td>Ventas no sujetas</td>
        </tr>

        <tr>
            <td class="resaltado">ISV 15%</td>
            <td class="resaltado"></td>
            <td class="moneda resaltado">{{ number_format($totalIsv15, 2, '.', '') }}</td>
            <td class="resaltado">Impuesto generado en ventas válidas</td>
        </tr>

        <tr>
            <td>Retenciones</td>
            <td></td>
            <td class="moneda">{{ number_format($totalRetencion, 2, '.', '') }}</td>
            <td>Retenciones aplicadas</td>
        </tr>

        <tr>
            <td class="positivo">Neto recibido</td>
            <td class="positivo"></td>
            <td class="moneda positivo">{{ number_format($totalNetoRecibido, 2, '.', '') }}</td>
            <td class="positivo">Total recibido después de retenciones</td>
        </tr>

        <tr>
            <td colspan="4"></td>
        </tr>

        <tr>
            <td colspan="4" class="seccion-naranja">
                COMPRAS, CUENTAS Y OBLIGACIONES
            </td>
        </tr>

        <tr>
            <th>Concepto</th>
            <th>Cantidad</th>
            <th>Monto</th>
            <th>Observación</th>
        </tr>

        <tr>
            <td>Compras del período</td>
            <td class="numero">{{ number_format($cantidadCompras, 0, '.', '') }}</td>
            <td class="moneda">{{ number_format($totalCompras, 2, '.', '') }}</td>
            <td>Compras no anuladas</td>
        </tr>

        <tr>
            <td>Cuentas por cobrar</td>
            <td></td>
            <td class="moneda">{{ number_format($cuentasPorCobrar, 2, '.', '') }}</td>
            <td>Saldo pendiente de ventas</td>
        </tr>

        <tr>
            <td>Cuentas por pagar</td>
            <td></td>
            <td class="moneda">{{ number_format($cuentasPorPagar, 2, '.', '') }}</td>
            <td>Saldo pendiente de compras</td>
        </tr>

        <tr>
            <td colspan="4"></td>
        </tr>

        <tr>
            <td colspan="4" class="seccion">
                VENTAS POR MÉTODO DE PAGO
            </td>
        </tr>

        <tr>
            <th>Método de pago</th>
            <th>Cantidad</th>
            <th>Total</th>
            <th>Observación</th>
        </tr>

        @forelse ($ventasPorMetodo as $metodo)
            <tr>
                <td>{{ $metodo->metodo_pago }}</td>
                <td class="numero">{{ number_format($metodo->cantidad, 0, '.', '') }}</td>
                <td class="moneda">{{ number_format($metodo->total, 2, '.', '') }}</td>
                <td></td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="text-align: center;">
                    No hay ventas por método de pago.
                </td>
            </tr>
        @endforelse

        <tr>
            <td colspan="4"></td>
        </tr>

        <tr>
            <td colspan="4" class="seccion">
                GASTOS POR CATEGORÍA
            </td>
        </tr>

        <tr>
            <th>Categoría</th>
            <th>Cantidad</th>
            <th>Total</th>
            <th>Observación</th>
        </tr>

        @forelse ($gastosPorCategoria as $categoria)
            <tr>
                <td>{{ $categoria->categoria }}</td>
                <td class="numero">{{ number_format($categoria->cantidad, 0, '.', '') }}</td>
                <td class="moneda">{{ number_format($categoria->total, 2, '.', '') }}</td>
                <td></td>
            </tr>
        @empty
            <tr>
                <td colspan="4" style="text-align: center;">
                    No hay gastos registrados.
                </td>
            </tr>
        @endforelse
    </table>
</body>
</html>