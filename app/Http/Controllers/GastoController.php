<?php

namespace App\Http\Controllers;

use App\Models\Catalogo;
use App\Models\Gasto;
use Illuminate\Http\Request;

class GastoController extends Controller
{
    public function create()
    {
        if (!auth()->user()->can('crear gastos')) {
            abort(403, 'No tiene permiso para crear gastos.');
        }

        $categorias = Catalogo::opciones('categoria_gasto')->pluck('nombre')->toArray();
        $metodosPago = Catalogo::opciones('metodo_pago')->pluck('nombre')->toArray();

        return view('gastos.form', [
            'gasto' => null,
            'categorias' => $categorias,
            'metodosPago' => $metodosPago,
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
            'referencia' => 'nullable|max:100',
            'proveedor' => 'nullable|max:150',
            'observacion' => 'nullable|max:500',
        ]);

        Gasto::create([
            'fecha' => $request->fecha,
            'categoria' => $request->categoria,
            'descripcion' => $request->descripcion,
            'monto' => $request->monto,
            'metodo_pago' => $request->metodo_pago,
            'referencia' => $request->referencia,
            'proveedor' => $request->proveedor,
            'observacion' => $request->observacion,
            'estado' => 'Registrado',
        ]);

        return redirect()
            ->route('gastos.index')
            ->with('message', 'Gasto registrado correctamente.');
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

        return view('gastos.form', [
            'gasto' => $gasto,
            'categorias' => $categorias,
            'metodosPago' => $metodosPago,
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
            'referencia' => 'nullable|max:100',
            'proveedor' => 'nullable|max:150',
            'observacion' => 'nullable|max:500',
        ]);

        $gasto->update([
            'fecha' => $request->fecha,
            'categoria' => $request->categoria,
            'descripcion' => $request->descripcion,
            'monto' => $request->monto,
            'metodo_pago' => $request->metodo_pago,
            'referencia' => $request->referencia,
            'proveedor' => $request->proveedor,
            'observacion' => $request->observacion,
            'estado' => 'Registrado',
        ]);

        return redirect()
            ->route('gastos.index')
            ->with('message', 'Gasto actualizado correctamente.');
    }
}
