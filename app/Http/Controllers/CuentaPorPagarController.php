<?php

namespace App\Http\Controllers;

use App\Models\Catalogo;
use App\Models\Compra;
use App\Models\PagoCompra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CuentaPorPagarController extends Controller
{
    public function index(Request $request)
    {
        $query = Compra::with(['proveedor', 'pagos'])
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

        return view('compras.cuentas-por-pagar.index', [
            'compras' => $compras,
            'metodosPago' => $metodosPago,
            'totalCuentas' => $totalCuentas,
            'totalComprado' => $totalComprado,
            'totalPagado' => $totalPagado,
            'totalPendiente' => $totalPendiente,
        ]);
    }

    public function pagar(Request $request, Compra $compra)
    {
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
            'referencia' => 'nullable|max:100',
            'observacion' => 'nullable|max:500',
        ]);

        try {
            DB::transaction(function () use ($request, $compra) {
                PagoCompra::create([
                    'compra_id' => $compra->id,
                    'monto' => $request->monto,
                    'metodo_pago' => $request->metodo_pago,
                    'referencia' => $request->referencia,
                    'observacion' => $request->observacion,
                ]);

                $nuevoMontoPagado = (float) $compra->monto_pagado + (float) $request->monto;
                $nuevoSaldo = (float) $compra->total - $nuevoMontoPagado;

                if ($nuevoSaldo < 0) {
                    $nuevoSaldo = 0;
                }

                $compra->monto_pagado = $nuevoMontoPagado;
                $compra->saldo_pendiente = $nuevoSaldo;
                $compra->save();
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
}
