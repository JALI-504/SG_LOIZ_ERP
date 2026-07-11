<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte financiero</title>

    <style>
    body {
        font-family: Arial, sans-serif;
        font-size: 12pt;
    }

    table {
        border-collapse: collapse;
        width: 100%;
        font-family: Arial, sans-serif;
        font-size: 12pt;
    }

    th, td {
        border: 1px solid #000;
        padding: 8px;
        font-family: Arial, sans-serif;
        font-size: 12pt;
        vertical-align: middle;
    }

    th {
        background-color: #d9eaf7;
        font-weight: bold;
        text-align: center;
        font-size: 12pt;
    }

    .titulo {
        font-size: 18pt;
        font-weight: bold;
        text-align: center;
        background-color: #1f4e78;
        color: #ffffff;
    }

    .subtitulo {
        font-size: 13pt;
        text-align: center;
        background-color: #d9eaf7;
    }

    .seccion {
        background-color: #b4c6e7;
        font-weight: bold;
        text-align: left;
        font-size: 13pt;
    }

    .text-right {
        text-align: right;
    }

    .text-center {
        text-align: center;
    }

    .positivo {
        color: #008000;
        font-weight: bold;
    }

    .negativo {
        color: #c00000;
        font-weight: bold;
    }
</style>
</head>

<body>
    <table>
        <tr>
            <td colspan="3" class="titulo">
                REPORTE FINANCIERO GENERAL
            </td>
        </tr>

        <tr>
            <td colspan="3" class="subtitulo">
                Desde {{ $fechaDesde }} hasta {{ $fechaHasta }}
            </td>
        </tr>

        <tr>
            <td colspan="3"></td>
        </tr>

        <tr>
            <td colspan="3" class="seccion">
                RESUMEN FINANCIERO
            </td>
        </tr>

        <tr>
            <th>Concepto</th>
            <th>Cantidad</th>
            <th>Total</th>
        </tr>

        <tr>
            <td>Ventas registradas</td>
            <td class="text-center">{{ number_format($cantidadVentas, 0) }}</td>
            <td class="text-right">L {{ number_format($totalVentas, 2) }}</td>
        </tr>

        <tr>
            <td>Descuentos en ventas</td>
            <td></td>
            <td class="text-right">L {{ number_format($totalDescuentosVentas, 2) }}</td>
        </tr>

        <tr>
            <td>Costo estimado de ventas</td>
            <td></td>
            <td class="text-right">L {{ number_format($costoVentas, 2) }}</td>
        </tr>

        <tr>
            <td><strong>Utilidad bruta estimada</strong></td>
            <td></td>
            <td class="text-right">
                <strong>L {{ number_format($utilidadBruta, 2) }}</strong>
            </td>
        </tr>

        <tr>
            <td>Gastos registrados</td>
            <td class="text-center">{{ number_format($cantidadGastos, 0) }}</td>
            <td class="text-right">L {{ number_format($totalGastos, 2) }}</td>
        </tr>

        <tr>
            <td><strong>Utilidad neta estimada</strong></td>
            <td></td>
            <td class="text-right {{ $utilidadNetaEstimada >= 0 ? 'positivo' : 'negativo' }}">
                L {{ number_format($utilidadNetaEstimada, 2) }}
            </td>
        </tr>

        <tr>
            <td>Compras registradas</td>
            <td class="text-center">{{ number_format($cantidadCompras, 0) }}</td>
            <td class="text-right">L {{ number_format($totalCompras, 2) }}</td>
        </tr>

        <tr>
            <td>Cuentas por cobrar pendientes</td>
            <td></td>
            <td class="text-right">L {{ number_format($cuentasPorCobrar, 2) }}</td>
        </tr>

        <tr>
            <td>Cuentas por pagar pendientes</td>
            <td></td>
            <td class="text-right">L {{ number_format($cuentasPorPagar, 2) }}</td>
        </tr>

        <tr>
            <td colspan="3"></td>
        </tr>

        <tr>
            <td colspan="3" class="seccion">
                VENTAS POR MÉTODO DE PAGO
            </td>
        </tr>

        <tr>
            <th>Método de pago</th>
            <th>Cantidad</th>
            <th>Total</th>
        </tr>

        @forelse ($ventasPorMetodo as $metodo)
            <tr>
                <td>{{ $metodo->metodo_pago }}</td>
                <td class="text-center">{{ number_format($metodo->cantidad, 0) }}</td>
                <td class="text-right">L {{ number_format($metodo->total, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center">
                    No hay ventas en el período seleccionado.
                </td>
            </tr>
        @endforelse

        <tr>
            <td colspan="3"></td>
        </tr>

        <tr>
            <td colspan="3" class="seccion">
                GASTOS POR CATEGORÍA
            </td>
        </tr>

        <tr>
            <th>Categoría</th>
            <th>Cantidad</th>
            <th>Total</th>
        </tr>

        @forelse ($gastosPorCategoria as $gasto)
            <tr>
                <td>{{ $gasto->categoria }}</td>
                <td class="text-center">{{ number_format($gasto->cantidad, 0) }}</td>
                <td class="text-right">L {{ number_format($gasto->total, 2) }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="3" class="text-center">
                    No hay gastos en el período seleccionado.
                </td>
            </tr>
        @endforelse
    </table>
</body>
</html>