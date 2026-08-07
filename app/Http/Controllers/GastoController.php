<?php

namespace App\Http\Controllers;

use App\Models\Catalogo;
use App\Models\CuentaBancaria;
use App\Models\Gasto;
use App\Models\BitacoraSistema;
use App\Services\BancoMovimientoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GastoController extends Controller
{
    public function create()
    {
        if (!auth()->user()->can('crear gastos')) {
            abort(403, 'No tiene permiso para crear gastos.');
        }

        $categorias = Catalogo::opciones('categoria_gasto')->pluck('nombre')->toArray();
        $metodosPago = Catalogo::opciones('metodo_pago')->pluck('nombre')->toArray();

        $cuentasBancarias = CuentaBancaria::where('activo', true)
            ->orderBy('banco')
            ->orderBy('nombre_cuenta')
            ->get();

        return view('gastos.form', [
            'gasto' => null,
            'categorias' => $categorias,
            'metodosPago' => $metodosPago,
            'cuentasBancarias' => $cuentasBancarias,
        ]);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->can('crear gastos')) {
            abort(403, 'No tiene permiso para registrar gastos.');
        }

        $request->validate([
            'fecha' => 'required|date',
            'categoria' => 'required|max:100',
            'descripcion' => 'required|min:3|max:200',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|max:50',
            'cuenta_bancaria_id' => 'nullable|exists:cuentas_bancarias,id',
            'referencia' => 'nullable|max:100',
            'proveedor' => 'nullable|max:150',
            'observacion' => 'nullable|max:500',
        ]);

        try {
            DB::transaction(function () use ($request) {
                $requiereBanco = BancoMovimientoService::metodoRequiereBanco($request->metodo_pago);

                if ($requiereBanco && !$request->cuenta_bancaria_id) {
                    throw new \Exception('Debe seleccionar una cuenta bancaria para este método de pago.');
                }

                $gasto = Gasto::create([
                    'fecha' => $request->fecha,
                    'categoria' => $request->categoria,
                    'descripcion' => $request->descripcion,
                    'monto' => $request->monto,
                    'metodo_pago' => $request->metodo_pago,
                    'cuenta_bancaria_id' => $requiereBanco ? $request->cuenta_bancaria_id : null,
                    'movimiento_bancario_id' => null,
                    'referencia' => $request->referencia,
                    'proveedor' => $request->proveedor,
                    'observacion' => $request->observacion,
                    'estado' => 'Registrado',
                ]);

                if ($requiereBanco) {
                    $movimientoBancario = BancoMovimientoService::registrarMovimiento(
                        $request->cuenta_bancaria_id,
                        'Salida',
                        'Gasto',
                        $request->monto,
                        $request->referencia,
                        'Gasto registrado: ' . $request->descripcion,
                        'Gasto',
                        $gasto->id,
                        'Movimiento bancario generado automáticamente desde gastos.',
                        $request->fecha
                    );

                    $gasto->update([
                        'movimiento_bancario_id' => $movimientoBancario->id,
                    ]);
                }

                BitacoraSistema::registrar(
                    'Gastos',
                    'Registrar',
                    'Registró el gasto #' . $gasto->id . ' por L ' . number_format($gasto->monto, 2) . ' en la categoría ' . $gasto->categoria . '.',
                    Gasto::class,
                    $gasto->id,
                    null,
                    $gasto->fresh()->toArray()
                );
            });

            return redirect()
                ->route('gastos.index')
                ->with('message', 'Gasto registrado correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    public function edit(Gasto $gasto)
    {
        if (!auth()->user()->can('editar gastos')) {
            abort(403, 'No tiene permiso para editar gastos.');
        }

        if ($gasto->estado === 'Anulado') {
            return redirect()
                ->route('gastos.index')
                ->with('error', 'No se puede editar un gasto anulado.');
        }

        $categorias = Catalogo::opciones('categoria_gasto')->pluck('nombre')->toArray();
        $metodosPago = Catalogo::opciones('metodo_pago')->pluck('nombre')->toArray();

        $cuentasBancarias = CuentaBancaria::where('activo', true)
            ->orderBy('banco')
            ->orderBy('nombre_cuenta')
            ->get();

        return view('gastos.form', [
            'gasto' => $gasto,
            'categorias' => $categorias,
            'metodosPago' => $metodosPago,
            'cuentasBancarias' => $cuentasBancarias,
        ]);
    }

    public function update(Request $request, Gasto $gasto)
    {
        if (!auth()->user()->can('editar gastos')) {
            abort(403, 'No tiene permiso para actualizar gastos.');
        }

        if ($gasto->estado === 'Anulado') {
            return redirect()
                ->route('gastos.index')
                ->with('error', 'No se puede modificar un gasto anulado.');
        }

        $request->validate([
            'fecha' => 'required|date',
            'categoria' => 'required|max:100',
            'descripcion' => 'required|min:3|max:200',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|max:50',
            'cuenta_bancaria_id' => 'nullable|exists:cuentas_bancarias,id',
            'referencia' => 'nullable|max:100',
            'proveedor' => 'nullable|max:150',
            'observacion' => 'nullable|max:500',
        ]);

        try {
            DB::transaction(function () use ($request, $gasto) {
                $requiereBanco = BancoMovimientoService::metodoRequiereBanco($request->metodo_pago);

                if ($requiereBanco && !$request->cuenta_bancaria_id) {
                    throw new \Exception('Debe seleccionar una cuenta bancaria para este método de pago.');
                }

                $datosAnteriores = $gasto->toArray();

                if ($gasto->movimiento_bancario_id) {
                    BancoMovimientoService::anularMovimiento(
                        $gasto->movimiento_bancario_id,
                        'Movimiento bancario anulado por actualización del gasto.'
                    );
                }

                $gasto->update([
                    'fecha' => $request->fecha,
                    'categoria' => $request->categoria,
                    'descripcion' => $request->descripcion,
                    'monto' => $request->monto,
                    'metodo_pago' => $request->metodo_pago,
                    'cuenta_bancaria_id' => $requiereBanco ? $request->cuenta_bancaria_id : null,
                    'movimiento_bancario_id' => null,
                    'referencia' => $request->referencia,
                    'proveedor' => $request->proveedor,
                    'observacion' => $request->observacion,
                    'estado' => 'Registrado',
                ]);

                if ($requiereBanco) {
                    $movimientoBancario = BancoMovimientoService::registrarMovimiento(
                        $request->cuenta_bancaria_id,
                        'Salida',
                        'Gasto',
                        $request->monto,
                        $request->referencia,
                        'Gasto actualizado: ' . $request->descripcion,
                        'Gasto',
                        $gasto->id,
                        'Movimiento bancario generado automáticamente por actualización de gasto.',
                        $request->fecha
                    );

                    $gasto->update([
                        'movimiento_bancario_id' => $movimientoBancario->id,
                    ]);
                }

                BitacoraSistema::registrar(
                    'Gastos',
                    'Actualizar',
                    'Actualizó el gasto #' . $gasto->id . ' por L ' . number_format($gasto->monto, 2) . '.',
                    Gasto::class,
                    $gasto->id,
                    $datosAnteriores,
                    $gasto->fresh()->toArray()
                );
            });

            return redirect()
                ->route('gastos.index')
                ->with('message', 'Gasto actualizado correctamente.');
        } catch (\Exception $e) {
            return redirect()
                ->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }
}
