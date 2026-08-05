<?php

namespace App\Http\Livewire\Servicios;

use App\Models\Insumo;
use App\Models\Servicio;
use App\Models\ServicioInsumo;
use App\Models\Catalogo;
use App\Models\BitacoraSistema;
use Illuminate\Validation\Rule;
use Livewire\Component;
class ServicioInsumos extends Component
{
    public $servicio;
    public $servicio_id;

    public $receta_id;
    public $insumo_id;
    public $cantidad_por_unidad = 1;

    public $search = '';
    public $filtroCategoria = 'todas';

    public $modalTitle = 'Agregar insumo al servicio';

    public $categorias = [];

    public function mount($servicioId)
    {
        if (!auth()->user()->can('editar servicios')) {
            abort(403, 'No tiene permiso para editar insumos del servicio.');
        }

        $this->servicio = Servicio::findOrFail($servicioId);
        $this->servicio_id = $this->servicio->id;

        $this->categorias = Catalogo::opciones('categoria_insumo')->pluck('nombre')->toArray();
    }

    protected function rules()
    {
        return [
            'insumo_id' => [
                'required',
                'exists:insumos,id',
                Rule::unique('servicio_insumos', 'insumo_id')
                    ->where(function ($query) {
                        return $query->where('servicio_id', $this->servicio_id);
                    })
                    ->ignore($this->receta_id),
            ],

            'cantidad_por_unidad' => 'required|numeric|min:0.01',
        ];
    }

    protected $messages = [
        'insumo_id.required' => 'Debe seleccionar un insumo.',
        'insumo_id.exists' => 'El insumo seleccionado no existe.',
        'insumo_id.unique' => 'Este insumo ya está asignado a este servicio.',

        'cantidad_por_unidad.required' => 'Debe ingresar la cantidad por unidad.',
        'cantidad_por_unidad.numeric' => 'La cantidad debe ser numérica.',
        'cantidad_por_unidad.min' => 'La cantidad debe ser mayor que cero.',
    ];

    public function store()
    {
        if (!auth()->user()->can('editar servicios')) {
            abort(403, 'No tiene permiso para agregar insumos al servicio.');
        }

        $this->validate();

        $insumo = Insumo::findOrFail($this->insumo_id);

        $receta = ServicioInsumo::create([
            'servicio_id' => $this->servicio_id,
            'insumo_id' => $this->insumo_id,
            'cantidad_por_unidad' => $this->cantidad_por_unidad,
        ]);

        $this->actualizarCostoServicio();

        BitacoraSistema::registrar(
            'Recetas de servicios',
            'Registrar',
            'Agregó el insumo ' . $insumo->nombre . ' al servicio ' . $this->servicio->nombre . '. Cantidad por unidad: ' . $this->cantidad_por_unidad . '.',
            ServicioInsumo::class,
            $receta->id,
            null,
            $receta->fresh()->load(['servicio', 'insumo'])->toArray()
        );

        $this->resetInput();

        session()->flash('message', 'Insumo agregado correctamente al servicio.');
    }

    public function edit($id)
    {
        if (!auth()->user()->can('editar servicios')) {
            abort(403, 'No tiene permiso para editar insumos del servicio.');
        }

        $receta = ServicioInsumo::where('servicio_id', $this->servicio_id)
            ->findOrFail($id);

        $this->receta_id = $receta->id;
        $this->insumo_id = $receta->insumo_id;
        $this->cantidad_por_unidad = $receta->cantidad_por_unidad;

        $this->modalTitle = 'Editar insumo del servicio';
    }

    public function update()
    {
        if (!auth()->user()->can('editar servicios')) {
            abort(403, 'No tiene permiso para actualizar insumos del servicio.');
        }

        $this->validate();

        $receta = ServicioInsumo::with(['servicio', 'insumo'])
            ->where('servicio_id', $this->servicio_id)
            ->findOrFail($this->receta_id);

        $datosAnteriores = $receta->toArray();

        $insumoAnterior = optional($receta->insumo)->nombre ?? 'N/D';
        $cantidadAnterior = $receta->cantidad_por_unidad;

        $receta->update([
            'insumo_id' => $this->insumo_id,
            'cantidad_por_unidad' => $this->cantidad_por_unidad,
        ]);

        $this->actualizarCostoServicio();

        $recetaActualizada = $receta->fresh()->load(['servicio', 'insumo']);

        $insumoNuevo = optional($recetaActualizada->insumo)->nombre ?? 'N/D';

        BitacoraSistema::registrar(
            'Recetas de servicios',
            'Actualizar',
            'Actualizó la receta del servicio ' . $this->servicio->nombre . '. Insumo anterior: ' . $insumoAnterior . ', cantidad anterior: ' . $cantidadAnterior . '. Nuevo insumo: ' . $insumoNuevo . ', nueva cantidad: ' . $this->cantidad_por_unidad . '.',
            ServicioInsumo::class,
            $recetaActualizada->id,
            $datosAnteriores,
            $recetaActualizada->toArray()
        );

        $this->resetInput();

        session()->flash('message', 'Insumo actualizado correctamente.');
    }

    public function delete($id)
    {
        if (!auth()->user()->can('editar servicios')) {
            abort(403, 'No tiene permiso para eliminar insumos del servicio.');
        }

        $receta = ServicioInsumo::with(['servicio', 'insumo'])
            ->where('servicio_id', $this->servicio_id)
            ->findOrFail($id);

        $datosAnteriores = $receta->toArray();

        $recetaId = $receta->id;
        $insumoNombre = optional($receta->insumo)->nombre ?? 'N/D';
        $cantidad = $receta->cantidad_por_unidad;

        $receta->delete();

        $this->actualizarCostoServicio();

        BitacoraSistema::registrar(
            'Recetas de servicios',
            'Eliminar',
            'Eliminó el insumo ' . $insumoNombre . ' del servicio ' . $this->servicio->nombre . '. Cantidad por unidad eliminada: ' . $cantidad . '.',
            ServicioInsumo::class,
            $recetaId,
            $datosAnteriores,
            null
        );

        session()->flash('message', 'Insumo eliminado del servicio.');
    }

    public function cancelar()
    {
        $this->resetInput();
    }

    private function actualizarCostoServicio()
    {
        $recetas = ServicioInsumo::with('insumo')
            ->where('servicio_id', $this->servicio_id)
            ->get();

        $costoTotal = $recetas->sum(function ($receta) {
            return $receta->cantidad_por_unidad * $receta->insumo->costo_unitario_real;
        });

        $servicio = Servicio::findOrFail($this->servicio_id);

        $servicio->update([
            'costo_unitario' => round($costoTotal, 2),
        ]);

        $this->servicio = $servicio->fresh();
    }

    private function resetInput()
    {
        $this->receta_id = null;
        $this->insumo_id = null;
        $this->cantidad_por_unidad = 1;
        $this->modalTitle = 'Agregar insumo al servicio';

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $insumos = Insumo::query()
            ->where('activo', true)
            ->where(function ($query) {
                $query->where('codigo', 'like', '%' . $this->search . '%')
                    ->orWhere('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('categoria', 'like', '%' . $this->search . '%');
            })
            ->when($this->filtroCategoria !== 'todas', function ($query) {
                $query->where('categoria', $this->filtroCategoria);
            })
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->get();

        $recetas = ServicioInsumo::with('insumo')
            ->where('servicio_id', $this->servicio_id)
            ->get();

        $costoTotal = $recetas->sum(function ($receta) {
            return $receta->cantidad_por_unidad * $receta->insumo->costo_unitario_real;
        });

        $precioVenta = $this->servicio->precio_unitario;

        $utilidad = $precioVenta - $costoTotal;

        $margen = $precioVenta > 0
            ? ($utilidad / $precioVenta) * 100
            : 0;

        return view('livewire.servicios.servicio-insumos', [
            'insumos' => $insumos,
            'recetas' => $recetas,
            'costoTotal' => $costoTotal,
            'precioVenta' => $precioVenta,
            'utilidad' => $utilidad,
            'margen' => $margen,
        ]);
    }

}
