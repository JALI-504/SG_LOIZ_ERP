<?php

namespace App\Http\Livewire\Servicios;

use App\Models\Insumo;
use App\Models\Servicio;
use App\Models\ServicioInsumo;
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

    public $categorias = [
        'Papel',
        'Tinta',
        'Toner',
        'Madera',
        'Acrilico',
        'Cuero',
        'Metal',
        'Herraje',
        'Empaque',
        'Adhesivo',
        'Laser',
        'Herramienta',
        'Otro',
    ];

    public function mount($servicioId)
    {
        $this->servicio = Servicio::findOrFail($servicioId);
        $this->servicio_id = $this->servicio->id;
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
        $this->validate();

        ServicioInsumo::create([
            'servicio_id' => $this->servicio_id,
            'insumo_id' => $this->insumo_id,
            'cantidad_por_unidad' => $this->cantidad_por_unidad,
        ]);

        $this->actualizarCostoServicio();

        $this->resetInput();

        session()->flash('message', 'Insumo agregado correctamente al servicio.');
    }

    public function edit($id)
    {
        $receta = ServicioInsumo::where('servicio_id', $this->servicio_id)
            ->findOrFail($id);

        $this->receta_id = $receta->id;
        $this->insumo_id = $receta->insumo_id;
        $this->cantidad_por_unidad = $receta->cantidad_por_unidad;

        $this->modalTitle = 'Editar insumo del servicio';
    }

    public function update()
    {
        $this->validate();

        $receta = ServicioInsumo::where('servicio_id', $this->servicio_id)
            ->findOrFail($this->receta_id);

        $receta->update([
            'insumo_id' => $this->insumo_id,
            'cantidad_por_unidad' => $this->cantidad_por_unidad,
        ]);

        $this->actualizarCostoServicio();

        $this->resetInput();

        session()->flash('message', 'Insumo actualizado correctamente.');
    }

    public function delete($id)
    {
        $receta = ServicioInsumo::where('servicio_id', $this->servicio_id)
            ->findOrFail($id);

        $receta->delete();

        $this->actualizarCostoServicio();

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
