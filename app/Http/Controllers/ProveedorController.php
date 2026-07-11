<?php

namespace App\Http\Controllers;

use App\Models\Catalogo;
use App\Models\Proveedor;
use Illuminate\Http\Request;

class ProveedorController extends Controller
{
    public function index(Request $request)
    {
        $tiposProveedor = Catalogo::opciones('tipo_proveedor')
            ->pluck('nombre')
            ->toArray();

        $query = Proveedor::query()
            ->when($request->search, function ($query) use ($request) {
                $search = '%' . $request->search . '%';

                $query->where(function ($q) use ($search) {
                    $q->where('codigo', 'like', $search)
                        ->orWhere('nombre_comercial', 'like', $search)
                        ->orWhere('nombre_legal', 'like', $search)
                        ->orWhere('rtn', 'like', $search)
                        ->orWhere('dni', 'like', $search)
                        ->orWhere('telefono', 'like', $search)
                        ->orWhere('whatsapp', 'like', $search)
                        ->orWhere('correo', 'like', $search)
                        ->orWhere('persona_contacto', 'like', $search);
                });
            })
            ->when($request->tipo_proveedor && $request->tipo_proveedor !== 'todos', function ($query) use ($request) {
                $query->where('tipo_proveedor', $request->tipo_proveedor);
            })
            ->when($request->estado && $request->estado !== 'todos', function ($query) use ($request) {
                if ($request->estado === 'activo') {
                    $query->where('activo', true);
                }

                if ($request->estado === 'inactivo') {
                    $query->where('activo', false);
                }
            });

        $totalProveedores = (clone $query)->count();
        $totalActivos = Proveedor::where('activo', true)->count();
        $totalInactivos = Proveedor::where('activo', false)->count();

        $proveedores = $query
            ->orderByDesc('id')
            ->paginate(10)
            ->appends($request->query());

        return view('proveedores.index', [
            'proveedores' => $proveedores,
            'tiposProveedor' => $tiposProveedor,
            'totalProveedores' => $totalProveedores,
            'totalActivos' => $totalActivos,
            'totalInactivos' => $totalInactivos,
        ]);
    }

    public function create()
    {
        $tiposProveedor = Catalogo::opciones('tipo_proveedor')
            ->pluck('nombre')
            ->toArray();

        return view('proveedores.form', [
            'proveedor' => null,
            'tiposProveedor' => $tiposProveedor,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nombre_comercial' => 'required|min:3|max:150',
            'nombre_legal' => 'nullable|max:150',
            'tipo_proveedor' => 'required|max:80',
            'rtn' => 'nullable|max:30',
            'dni' => 'nullable|max:30',
            'telefono' => 'nullable|max:30',
            'whatsapp' => 'nullable|max:30',
            'correo' => 'nullable|email|max:100',
            'persona_contacto' => 'nullable|max:150',
            'telefono_contacto' => 'nullable|max:30',
            'direccion' => 'nullable|max:1000',
            'observacion' => 'nullable|max:1000',
        ]);

        Proveedor::create([
            'nombre_comercial' => $request->nombre_comercial,
            'nombre_legal' => $request->nombre_legal,
            'tipo_proveedor' => $request->tipo_proveedor,
            'rtn' => $request->rtn,
            'dni' => $request->dni,
            'telefono' => $request->telefono,
            'whatsapp' => $request->whatsapp,
            'correo' => $request->correo,
            'persona_contacto' => $request->persona_contacto,
            'telefono_contacto' => $request->telefono_contacto,
            'direccion' => $request->direccion,
            'observacion' => $request->observacion,
            'activo' => true,
        ]);

        return redirect()
            ->route('proveedores.index')
            ->with('message', 'Proveedor registrado correctamente.');
    }

    public function edit(Proveedor $proveedor)
    {
        $tiposProveedor = Catalogo::opciones('tipo_proveedor')
            ->pluck('nombre')
            ->toArray();

        return view('proveedores.form', [
            'proveedor' => $proveedor,
            'tiposProveedor' => $tiposProveedor,
        ]);
    }

    public function update(Request $request, Proveedor $proveedor)
    {
        $request->validate([
            'nombre_comercial' => 'required|min:3|max:150',
            'nombre_legal' => 'nullable|max:150',
            'tipo_proveedor' => 'required|max:80',
            'rtn' => 'nullable|max:30',
            'dni' => 'nullable|max:30',
            'telefono' => 'nullable|max:30',
            'whatsapp' => 'nullable|max:30',
            'correo' => 'nullable|email|max:100',
            'persona_contacto' => 'nullable|max:150',
            'telefono_contacto' => 'nullable|max:30',
            'direccion' => 'nullable|max:1000',
            'observacion' => 'nullable|max:1000',
        ]);

        $proveedor->update([
            'nombre_comercial' => $request->nombre_comercial,
            'nombre_legal' => $request->nombre_legal,
            'tipo_proveedor' => $request->tipo_proveedor,
            'rtn' => $request->rtn,
            'dni' => $request->dni,
            'telefono' => $request->telefono,
            'whatsapp' => $request->whatsapp,
            'correo' => $request->correo,
            'persona_contacto' => $request->persona_contacto,
            'telefono_contacto' => $request->telefono_contacto,
            'direccion' => $request->direccion,
            'observacion' => $request->observacion,
        ]);

        return redirect()
            ->route('proveedores.index')
            ->with('message', 'Proveedor actualizado correctamente.');
    }

    public function cambiarEstado(Proveedor $proveedor)
    {
        $proveedor->update([
            'activo' => !$proveedor->activo,
        ]);

        $mensaje = $proveedor->activo
            ? 'Proveedor reactivado correctamente.'
            : 'Proveedor desactivado correctamente.';

        return redirect()
            ->route('proveedores.index')
            ->with('message', $mensaje);
    }
}
