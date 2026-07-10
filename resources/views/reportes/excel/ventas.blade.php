<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">

    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
            font-size: 12px;
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
    </style>
</head>

<body>
    <table>
        <tr>
            <td colspan="15" class="titulo">
                REPORTE DE VENTAS
            </td>
        </tr>

        <tr>
            <td colspan="3" class="subtitulo">Desde</td>
            <td colspan="3">{{ $fechaDesde }}</td>
            <td colspan="3" class="subtitulo">Hasta</td>
            <td colspan="3">{{ $fechaHasta }}</td>
            <td colspan="3" class="subtitulo">Generado: {{ $generado }}</td>
        </tr>

        <tr>
            <td colspan="15"></td>
        </tr>

        <tr>
            <th style="width: 90px;">Fecha</th>
            <th style="width: 80px;">Hora</th>
            <th style="width: 110px;">Número</th>
            <th style="width: 180px;">Cliente</th>
            <th style="width: 120px;">Método pago</th>
            <th style="width: 100px;">Estado</th>
            <th style="width: 100px;">Tipo</th>
            <th style="width: 100px;">Código</th>
            <th style="width: 260px;">Descripción</th>
            <th style="width: 90px;">Cantidad</th>
            <th style="width: 120px;">Precio unitario</th>
            <th style="width: 120px;">Costo unitario</th>
            <th style="width: 120px;">Descuento</th>
            <th style="width: 120px;">Total</th>
            <th style="width: 130px;">Utilidad estimada</th>
        </tr>

        @forelse ($detalles as $detalle)
            <tr>
                <td class="fecha">{{ $detalle->fecha }}</td>
                <td class="texto">{{ $detalle->hora }}</td>
                <td class="texto">{{ $detalle->numero }}</td>
                <td class="wrap">{{ $detalle->cliente ?: 'Consumidor final' }}</td>
                <td>{{ $detalle->metodo_pago }}</td>
                <td>{{ $detalle->estado }}</td>
                <td>{{ $detalle->tipo_item }}</td>
                <td class="texto">{{ $detalle->codigo }}</td>
                <td class="wrap">{{ $detalle->descripcion }}</td>
                <td class="numero">{{ number_format($detalle->cantidad, 2, '.', '') }}</td>
                <td class="moneda">{{ number_format($detalle->precio_unitario, 2, '.', '') }}</td>
                <td class="moneda4">{{ number_format($detalle->costo_unitario, 4, '.', '') }}</td>
                <td class="moneda">{{ number_format($detalle->descuento, 2, '.', '') }}</td>
                <td class="moneda">{{ number_format($detalle->total, 2, '.', '') }}</td>
                <td class="moneda">{{ number_format($detalle->utilidad, 2, '.', '') }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="15" style="text-align: center;">
                    No hay ventas registradas con los filtros seleccionados.
                </td>
            </tr>
        @endforelse
    </table>
</body>
</html>