<?php

namespace App\Http\Controllers;

use App\Models\Catalogo;
use App\Models\Compra;
use App\Models\CuentaBancaria;
use App\Models\PagoCompra;
use App\Models\BitacoraSistema;
use App\Services\BancoMovimientoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CuentaPorPagarController extends Controller
{
    public function index(Request $request)
    {
        $query = Compra::with(['proveedor', 'pagos.cuentaBancaria', 'pagos.movimientoBancario'])
            ->where('estado', '!=', 'Anulada')
            ->where('saldo_pendiente', '>', 0)
            ->when($request->search, function ($query) use ($request) {
                $search = '%' . $request->search . '%';

                $query->where(function ($q) use ($search) {
                    $q->where('numero', 'like', $search)
                        ->orWhere('numero_comprobante', 'like', $search)
                        ->orWhere('metodo_pago', 'like', $search)
                        ->orWhereHas('proveedor', function ($proveedorQuery) use ($search) {
                            $proveedorQuery->where('nombre_comercial', 'like', $search)
                                ->orWhere('nombre_legal', 'like', $search)
                                ->orWhere('rtn', 'like', $search)
                                ->orWhere('telefono', 'like', $search);
                        });
                });
            })
            ->when($request->fecha_desde, function ($query) use ($request) {
                $query->whereDate('fecha', '>=', $request->fecha_desde);
            })
            ->when($request->fecha_hasta, function ($query) use ($request) {
                $query->whereDate('fecha', '<=', $request->fecha_hasta);
            });

        $totalCuentas = (clone $query)->count();
        $totalComprado = (clone $query)->sum('total');
        $totalPagado = (clone $query)->sum('monto_pagado');
        $totalPendiente = (clone $query)->sum('saldo_pendiente');

        $compras = $query
            ->orderBy('fecha')
            ->orderBy('id')
            ->paginate(10)
            ->appends($request->query());

        $metodosPago = Catalogo::opciones('metodo_pago')
            ->pluck('nombre')
            ->toArray();

        $cuentasBancarias = CuentaBancaria::where('activo', true)
            ->orderBy('banco')
            ->orderBy('nombre_cuenta')
            ->get();

        return view('compras.cuentas-por-pagar.index', [
            'compras' => $compras,
            'metodosPago' => $metodosPago,
            'cuentasBancarias' => $cuentasBancarias,
            'totalCuentas' => $totalCuentas,
            'totalComprado' => $totalComprado,
            'totalPagado' => $totalPagado,
            'totalPendiente' => $totalPendiente,
        ]);
    }

    public function pagar(Request $request, Compra $compra)
    {
        if (!auth()->user()->can('registrar pagos proveedores')) {
            abort(403, 'No tiene permiso para registrar pagos a proveedores.');
        }

        if ($compra->estado === 'Anulada') {
            return redirect()
                ->route('compras.cuentas-por-pagar')
                ->with('error', 'No se puede registrar pago a una compra anulada.');
        }

        if ($compra->saldo_pendiente <= 0) {
            return redirect()
                ->route('compras.cuentas-por-pagar')
                ->with('error', 'Esta compra ya está pagada.');
        }

        $request->validate([
            'monto' => 'required|numeric|min:0.01|max:' . $compra->saldo_pendiente,
            'metodo_pago' => 'required|max:50',
            'cuenta_bancaria_id' => 'nullable|exists:cuentas_bancarias,id',
            'referencia' => 'nullable|max:100',
            'observacion' => 'nullable|max:500',
        ]);

        try {
            DB::transaction(function () use ($request, $compra) {
                $datosAnterioresCompra = $compra->toArray();

                $requiereBanco = BancoMovimientoService::metodoRequiereBanco($request->metodo_pago);

                if ($requiereBanco && !$request->cuenta_bancaria_id) {
                    throw new \Exception('Debe seleccionar una cuenta bancaria para este método de pago.');
                }

                $pago = PagoCompra::create([
                    'compra_id' => $compra->id,
                    'monto' => $request->monto,
                    'metodo_pago' => $request->metodo_pago,
                    'cuenta_bancaria_id' => $requiereBanco ? $request->cuenta_bancaria_id : null,
                    'movimiento_bancario_id' => null,
                    'referencia' => $request->referencia,
                    'observacion' => $request->observacion,
                    'estado' => 'Activo',
                ]);

                if ($requiereBanco) {
                    $movimientoBancario = BancoMovimientoService::registrarMovimiento(
                        $request->cuenta_bancaria_id,
                        'Salida',
                        'Pago proveedor',
                        $request->monto,
                        $request->referencia,
                        'Pago registrado a la compra ' . ($compra->numero ?? 'N/D'),
                        'Pago proveedor',
                        $pago->id,
                        'Movimiento bancario generado automáticamente desde cuentas por pagar.',
                        now()->format('Y-m-d')
                    );

                    $pago->update([
                        'movimiento_bancario_id' => $movimientoBancario->id,
                    ]);
                }

                $totalPagosRegistrados = PagoCompra::where('compra_id', $compra->id)
                    ->where('estado', 'Activo')
                    ->sum('monto');

                $nuevoMontoPagado = (float) $totalPagosRegistrados;

                if ($nuevoMontoPagado > (float) $compra->total) {
                    $nuevoMontoPagado = (float) $compra->total;
                }

                $nuevoSaldo = (float) $compra->total - $nuevoMontoPagado;

                if ($nuevoSaldo < 0) {
                    $nuevoSaldo = 0;
                }

                $compra->monto_pagado = $nuevoMontoPagado;
                $compra->saldo_pendiente = $nuevoSaldo;
                $compra->estado = 'Registrada';
                $compra->save();

                BitacoraSistema::registrar(
                    'Pagos proveedores',
                    'Registrar',
                    'Registró pago a proveedor por L ' . number_format($pago->monto, 2) . ' en la compra ' . ($compra->numero ?? 'N/D') . '.',
                    PagoCompra::class,
                    $pago->id,
                    null,
                    $pago->fresh()->load(['compra', 'cuentaBancaria', 'movimientoBancario'])->toArray()
                );

                BitacoraSistema::registrar(
                    'Compras',
                    'Actualizar',
                    'Actualizó el saldo de la compra ' . ($compra->numero ?? 'N/D') . ' después de registrar un pago a proveedor.',
                    Compra::class,
                    $compra->id,
                    $datosAnterioresCompra,
                    $compra->fresh()->toArray()
                );
            });

            return redirect()
                ->route('compras.cuentas-por-pagar')
                ->with('message', 'Pago registrado correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->route('compras.cuentas-por-pagar')
                ->with('error', $e->getMessage());
        }
    }

    public function anularPago(Request $request, PagoCompra $pago)
    {
        if (!auth()->user()->can('anular pagos proveedores')) {
            abort(403, 'No tiene permiso para anular pagos a proveedores.');
        }

        if ($pago->estado === 'Anulado') {
            return redirect()
                ->back()
                ->with('error', 'Este pago ya está anulado.');
        }

        $request->validate([
            'observacion_anulacion' => 'nullable|max:500',
        ]);

        try {
            DB::transaction(function () use ($request, $pago) {
                $pago->load('compra');

                $compra = $pago->compra;

                if (!$compra) {
                    throw new \Exception('No se encontró la compra asociada a este pago.');
                }

                if ($compra->estado === 'Anulada') {
                    throw new \Exception('No se puede anular un pago de una compra anulada.');
                }

                $datosAnterioresPago = $pago->toArray();
                $datosAnterioresCompra = $compra->toArray();

                if ($pago->movimiento_bancario_id) {
                    BancoMovimientoService::anularMovimiento(
                        $pago->movimiento_bancario_id,
                        'Movimiento bancario anulado por anulación del pago a proveedor.'
                    );
                }

                $pago->update([
                    'estado' => 'Anulado',
                    'fecha_anulacion' => now(),
                    'observacion_anulacion' => $request->observacion_anulacion,
                ]);

                $totalPagosActivos = PagoCompra::where('compra_id', $compra->id)
                    ->where('estado', 'Activo')
                    ->sum('monto');

                $nuevoMontoPagado = (float) $totalPagosActivos;

                if ($nuevoMontoPagado > (float) $compra->total) {
                    $nuevoMontoPagado = (float) $compra->total;
                }

                $nuevoSaldo = (float) $compra->total - $nuevoMontoPagado;

                if ($nuevoSaldo < 0) {
                    $nuevoSaldo = 0;
                }

                $compra->monto_pagado = $nuevoMontoPagado;
                $compra->saldo_pendiente = $nuevoSaldo;
                $compra->estado = 'Registrada';
                $compra->save();

                BitacoraSistema::registrar(
                    'Pagos proveedores',
                    'Anular',
                    'Anuló pago a proveedor por L ' . number_format($pago->monto, 2) . ' de la compra ' . ($compra->numero ?? 'N/D') . '.',
                    PagoCompra::class,
                    $pago->id,
                    $datosAnterioresPago,
                    $pago->fresh()->load(['compra', 'cuentaBancaria', 'movimientoBancario'])->toArray()
                );

                BitacoraSistema::registrar(
                    'Compras',
                    'Actualizar',
                    'Actualizó el saldo de la compra ' . ($compra->numero ?? 'N/D') . ' después de anular un pago a proveedor.',
                    Compra::class,
                    $compra->id,
                    $datosAnterioresCompra,
                    $compra->fresh()->toArray()
                );
            });

            return redirect()
                ->back()
                ->with('message', 'Pago anulado correctamente y saldo recalculado.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->with('error', $e->getMessage());
        }
    }
}
