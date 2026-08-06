<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cierre de caja {{ $cierre->codigo }}</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #000;
            margin: 0;
            padding: 20px;
        }

        .documento {
            max-width: 850px;
            margin: 0 auto;
            border: 1px solid #333;
            padding: 20px;
        }

        .botones {
            max-width: 850px;
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

        .cierre-info {
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

        .caja-documento {
            border: 2px solid #333;
            padding: 10px;
            text-align: center;
        }

        .titulo {
            font-size: 22px;
            font-weight: bold;
            margin-bottom: 6px;
        }

        .numero {
            font-size: 17px;
            font-weight: bold;
        }

        .estado {
            display: inline-block;
            border: 1px solid #333;
            padding: 4px 8px;
            font-weight: bold;
            margin-top: 8px;
        }

        .anulado {
            border: 3px solid #000;
            padding: 8px;
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            margin-top: 12px;
        }

        .seccion {
            margin-top: 16px;
        }

        .seccion-titulo {
            font-weight: bold;
            font-size: 15px;
            border-bottom: 1px solid #333;
            padding-bottom: 4px;
            margin-bottom: 8px;
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

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-success {
            color: #008000;
        }

        .text-danger {
            color: #b00000;
        }

        .text-muted {
            color: #555;
        }

        .resumen-final {
            width: 380px;
            margin-left: auto;
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
    <div class="botones">
        {{-- <button onclick="window.print()" class="btn">
            Imprimir
        </button> --}}

        <button type="button" onclick="registrarImpresionCierre()" class="btn">
            Imprimir
        </button>

        <a href="{{ route('cierres-caja.index') }}" class="btn">
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

            <div class="cierre-info">
                <div class="caja-documento">
                    <div class="titulo">
                        CIERRE DE CAJA
                    </div>

                    <div class="numero">
                        No.: {{ $cierre->codigo }}
                    </div>

                    <div class="estado">
                        {{ $cierre->estado }}
                    </div>
                </div>

                @if ($cierre->estado === 'Anulado')
                    <div class="anulado">
                        CIERRE ANULADO
                    </div>
                @endif
            </div>
        </div>

        <div class="seccion">
            <table class="sin-borde">
                <tr>
                    <td>
                        <strong>Fecha:</strong>
                        {{ \Carbon\Carbon::parse($cierre->fecha)->format('d/m/Y') }}
                    </td>

                    <td>
                        <strong>Usuario:</strong>
                        {{ $cierre->usuario->name ?? 'Sistema' }}
                    </td>
                </tr>

                <tr>
                    <td>
                        <strong>Fecha de registro:</strong>
                        {{ $cierre->created_at ? \Carbon\Carbon::parse($cierre->created_at)->format('d/m/Y H:i') : 'No registrada' }}
                    </td>

                    <td>
                        <strong>Estado:</strong>
                        {{ $cierre->estado }}
                    </td>
                </tr>
            </table>
        </div>

        @if ($cierre->estado === 'Anulado')
            <div class="seccion">
                <div class="seccion-titulo">
                    Información de anulación
                </div>

                <table>
                    <tr>
                        <th>Fecha anulación</th>
                        <th>Anulado por</th>
                        <th>Motivo</th>
                    </tr>

                    <tr>
                        <td>
                            {{ $cierre->fecha_anulacion ? \Carbon\Carbon::parse($cierre->fecha_anulacion)->format('d/m/Y H:i') : 'No registrada' }}
                        </td>

                        <td>
                            {{ $cierre->usuarioAnulacion->name ?? 'Sistema' }}
                        </td>

                        <td>
                            {{ $cierre->motivo_anulacion ?: 'Sin motivo registrado' }}
                        </td>
                    </tr>
                </table>
            </div>
        @endif

        <div class="seccion">
            <div class="seccion-titulo">
                Ingresos del cierre
            </div>

            <table>
                <tr>
                    <th>Concepto</th>
                    <th width="25%">Monto</th>
                </tr>

                <tr>
                    <td>Monto inicial</td>
                    <td class="text-right">
                        L {{ number_format($cierre->monto_inicial, 2) }}
                    </td>
                </tr>

                <tr>
                    <td>Ventas / abonos en efectivo</td>
                    <td class="text-right">
                        L {{ number_format($cierre->ventas_efectivo, 2) }}
                    </td>
                </tr>

                <tr>
                    <td>Transferencias / depósitos</td>
                    <td class="text-right">
                        L {{ number_format($cierre->ventas_transferencia, 2) }}
                    </td>
                </tr>

                <tr>
                    <td>Pagos con tarjeta / POS</td>
                    <td class="text-right">
                        L {{ number_format($cierre->ventas_tarjeta, 2) }}
                    </td>
                </tr>

                <tr>
                    <td>Otros métodos de pago</td>
                    <td class="text-right">
                        L {{ number_format($cierre->ventas_otros, 2) }}
                    </td>
                </tr>

                <tr>
                    <td>Otros ingresos</td>
                    <td class="text-right">
                        L {{ number_format($cierre->otros_ingresos, 2) }}
                    </td>
                </tr>

                <tr>
                    <th>Total ingresos por ventas</th>
                    <th class="text-right">
                        L {{ number_format($cierre->total_ingresos_ventas, 2) }}
                    </th>
                </tr>

                <tr>
                    <th>Total ingresos</th>
                    <th class="text-right">
                        L {{ number_format($cierre->total_ingresos, 2) }}
                    </th>
                </tr>
            </table>
        </div>

        <div class="seccion">
            <div class="seccion-titulo">
                Egresos del cierre
            </div>

            <table>
                <tr>
                    <th>Concepto</th>
                    <th width="25%">Monto</th>
                </tr>

                <tr>
                    <td>Gastos registrados</td>
                    <td class="text-right">
                        L {{ number_format($cierre->gastos_registrados, 2) }}
                    </td>
                </tr>

                <tr>
                    <td>Pagos a proveedores</td>
                    <td class="text-right">
                        L {{ number_format($cierre->pagos_proveedores, 2) }}
                    </td>
                </tr>

                <tr>
                    <td>Otros egresos</td>
                    <td class="text-right">
                        L {{ number_format($cierre->otros_egresos, 2) }}
                    </td>
                </tr>

                <tr>
                    <th>Total egresos</th>
                    <th class="text-right">
                        L {{ number_format($cierre->total_egresos, 2) }}
                    </th>
                </tr>
            </table>
        </div>

        <div class="seccion">
            <div class="seccion-titulo">
                Cantidad de movimientos
            </div>

            <table>
                <tr>
                    <th>Pagos / abonos de ventas</th>
                    <th>Gastos registrados</th>
                    <th>Pagos a proveedores</th>
                </tr>

                <tr>
                    <td class="text-center">
                        {{ number_format($cierre->cantidad_pagos_ventas, 0) }}
                    </td>

                    <td class="text-center">
                        {{ number_format($cierre->cantidad_gastos, 0) }}
                    </td>

                    <td class="text-center">
                        {{ number_format($cierre->cantidad_pagos_proveedores, 0) }}
                    </td>
                </tr>
            </table>
        </div>

        <div class="seccion">
            <div class="seccion-titulo">
                Resultado final de caja
            </div>

            <table class="resumen-final">
                <tr>
                    <td><strong>Efectivo esperado:</strong></td>
                    <td class="text-right">
                        L {{ number_format($cierre->efectivo_esperado, 2) }}
                    </td>
                </tr>

                <tr>
                    <td><strong>Efectivo contado:</strong></td>
                    <td class="text-right">
                        L {{ number_format($cierre->efectivo_contado, 2) }}
                    </td>
                </tr>

                <tr>
                    <td><strong>Diferencia:</strong></td>
                    <td class="text-right">
                        @if ($cierre->diferencia > 0)
                            <strong class="text-success">
                                Sobrante: L {{ number_format($cierre->diferencia, 2) }}
                            </strong>
                        @elseif ($cierre->diferencia < 0)
                            <strong class="text-danger">
                                Faltante: L {{ number_format($cierre->diferencia, 2) }}
                            </strong>
                        @else
                            <strong>
                                Caja cuadrada: L {{ number_format($cierre->diferencia, 2) }}
                            </strong>
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        @if ($cierre->observacion)
            <div class="seccion">
                <div class="seccion-titulo">
                    Observación
                </div>

                {{ $cierre->observacion }}
            </div>
        @endif

        <div class="firmas">
            <div class="firma">
                <div class="linea">
                    Responsable de caja
                </div>
            </div>

            <div class="firma">
                <div class="linea">
                    Revisado / autorizado
                </div>
            </div>
        </div>
    </div>
    <script>
        function registrarImpresionCierre() {
            fetch("{{ route('cierres-caja.registrar-impresion', $cierre->id) }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                }
            }).finally(function () {
                window.print();
            });
        }
    </script>
</body>
</html>