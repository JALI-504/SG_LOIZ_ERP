<?php

namespace App\Services;

use App\Models\BitacoraSistema;
use App\Models\CuentaBancaria;
use App\Models\MovimientoBancario;

class BancoMovimientoService
{
    public static function metodoRequiereBanco($metodoPago)
    {
        $metodoPago = strtolower(trim((string) $metodoPago));

        return strpos($metodoPago, 'transferencia') !== false ||
            strpos($metodoPago, 'tarjeta') !== false ||
            strpos($metodoPago, 'deposito') !== false ||
            strpos($metodoPago, 'depósito') !== false ||
            strpos($metodoPago, 'cheque') !== false;
    }

    public static function registrarMovimiento(
        $cuentaBancariaId,
        $tipo,
        $categoria,
        $monto,
        $referencia = null,
        $descripcion = null,
        $origen = 'Manual',
        $origenId = null,
        $observacion = null,
        $fecha = null
    ) {
        $cuenta = CuentaBancaria::where('activo', true)
            ->lockForUpdate()
            ->findOrFail($cuentaBancariaId);

        $saldoAnterior = (float) $cuenta->saldo_actual;
        $monto = (float) $monto;

        if ($tipo === 'Entrada') {
            $saldoNuevo = $saldoAnterior + $monto;
        } else {
            $saldoNuevo = $saldoAnterior - $monto;
        }

        $movimiento = MovimientoBancario::create([
            'cuenta_bancaria_id' => $cuenta->id,
            'fecha' => $fecha ?: now()->format('Y-m-d'),
            'hora' => now()->format('H:i:s'),
            'tipo' => $tipo,
            'categoria' => $categoria,
            'referencia' => $referencia,
            'descripcion' => $descripcion,
            'monto' => $monto,
            'saldo_anterior' => $saldoAnterior,
            'saldo_nuevo' => $saldoNuevo,
            'origen' => $origen,
            'origen_id' => $origenId,
            'estado' => 'Activo',
            'observacion' => $observacion,
            'user_id' => auth()->id(),
        ]);

        $datosAnterioresCuenta = $cuenta->toArray();

        $cuenta->update([
            'saldo_actual' => $saldoNuevo,
        ]);

        BitacoraSistema::registrar(
            'Movimientos bancarios',
            'Registrar',
            'Registró el movimiento bancario ' . $movimiento->codigo . ' desde ' . $origen . '.',
            MovimientoBancario::class,
            $movimiento->id,
            null,
            $movimiento->fresh()->load(['cuentaBancaria', 'usuario'])->toArray()
        );

        BitacoraSistema::registrar(
            'Cuentas bancarias',
            'Actualizar',
            'Actualizó el saldo de la cuenta bancaria ' . $cuenta->codigo . ' mediante el movimiento ' . $movimiento->codigo . '.',
            CuentaBancaria::class,
            $cuenta->id,
            $datosAnterioresCuenta,
            $cuenta->fresh()->load('usuario')->toArray()
        );

        return $movimiento;
    }

    public static function anularMovimiento($movimientoBancarioId, $motivo = null)
    {
        if (!$movimientoBancarioId) {
            return null;
        }

        $movimiento = MovimientoBancario::where('estado', 'Activo')
            ->lockForUpdate()
            ->find($movimientoBancarioId);

        if (!$movimiento) {
            return null;
        }

        $cuenta = CuentaBancaria::lockForUpdate()
            ->findOrFail($movimiento->cuenta_bancaria_id);

        $datosAnterioresMovimiento = $movimiento->toArray();
        $datosAnterioresCuenta = $cuenta->toArray();

        $saldoActual = (float) $cuenta->saldo_actual;
        $monto = (float) $movimiento->monto;

        if ($movimiento->tipo === 'Entrada') {
            $saldoNuevo = $saldoActual - $monto;
        } else {
            $saldoNuevo = $saldoActual + $monto;
        }

        $movimiento->update([
            'estado' => 'Anulado',
            'fecha_anulacion' => now(),
            'anulado_por' => auth()->id(),
            'motivo_anulacion' => $motivo ?: 'Movimiento bancario anulado desde documento origen.',
        ]);

        $cuenta->update([
            'saldo_actual' => $saldoNuevo,
        ]);

        BitacoraSistema::registrar(
            'Movimientos bancarios',
            'Anular',
            'Anuló el movimiento bancario ' . $movimiento->codigo . ' desde documento origen.',
            MovimientoBancario::class,
            $movimiento->id,
            $datosAnterioresMovimiento,
            $movimiento->fresh()->load(['cuentaBancaria', 'usuario', 'usuarioAnulacion'])->toArray()
        );

        BitacoraSistema::registrar(
            'Cuentas bancarias',
            'Actualizar',
            'Actualizó el saldo de la cuenta bancaria ' . $cuenta->codigo . ' por anulación del movimiento ' . $movimiento->codigo . '.',
            CuentaBancaria::class,
            $cuenta->id,
            $datosAnterioresCuenta,
            $cuenta->fresh()->load('usuario')->toArray()
        );

        return $movimiento;
    }
}
