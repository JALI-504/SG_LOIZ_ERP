<?php

namespace App\Http\Livewire\Productos;

use App\Models\Catalogo;
use App\Models\Insumo;
use App\Models\Producto;
use App\Models\ProductoInsumo;
use Illuminate\Validation\Rule;
use Livewire\Component;

class ProductoInsumos extends Component
{
    public $producto;
    public $producto_id;

    public $receta_id;
    public $insumo_id;
    public $cantidad_por_unidad = 1;

    public $search = '';
    public $filtroCategoria = 'todas';

    public $modalTitle = 'Agregar insumo al producto';

    public $categorias = [];

    public function mount($productoId)
    {
        $this->producto = Producto::findOrFail($productoId);
        $this->producto_id = $this->producto->id;

        $this->categorias = Catalogo::opciones('categoria_insumo')->pluck('nombre')->toArray();
    }

    protected function rules()
    {
        return [
            'insumo_id' => [
                'required',
                'exists:insumos,id',
                Rule::unique('producto_insumos', 'insumo_id')
                    ->where(function ($query) {
                        return $query->where('producto_id', $this->producto_id);
                    })
                    ->ignore($this->receta_id),
            ],
            'cantidad_por_unidad' => 'required|numeric|min:0.01',
        ];
    }

    protected $messages = [
        'insumo_id.required' => 'Debe seleccionar un insumo.',
        'insumo_id.exists' => 'El insumo seleccionado no existe.',
        'insumo_id.unique' => 'Este insumo ya está asignado a este producto.',
        'cantidad_por_unidad.required' => 'Debe ingresar la cantidad por unidad.',
        'cantidad_por_unidad.numeric' => 'La cantidad debe ser numérica.',
        'cantidad_por_unidad.min' => 'La cantidad debe ser mayor que cero.',
    ];

    public function store()
    {
        $this->validate();

        ProductoInsumo::create([
            'producto_id' => $this->producto_id,
            'insumo_id' => $this->insumo_id,
            'cantidad_por_unidad' => $this->cantidad_por_unidad,
        ]);

        $this->actualizarCostoProducto();

        $this->resetInput();

        session()->flash('message', 'Insumo agregado correctamente al producto.');
    }

    public function edit($id)
    {
        $receta = ProductoInsumo::where('producto_id', $this->producto_id)
            ->findOrFail($id);

        $this->receta_id = $receta->id;
        $this->insumo_id = $receta->insumo_id;
        $this->cantidad_por_unidad = $receta->cantidad_por_unidad;

        $this->modalTitle = 'Editar insumo del producto';
    }

    public function update()
    {
        $this->validate();

        $receta = ProductoInsumo::where('producto_id', $this->producto_id)
            ->findOrFail($this->receta_id);

        $receta->update([
            'insumo_id' => $this->insumo_id,
            'cantidad_por_unidad' => $this->cantidad_por_unidad,
        ]);

        $this->actualizarCostoProducto();

        $this->resetInput();

        session()->flash('message', 'Insumo actualizado correctamente.');
    }

    public function delete($id)
    {
        $receta = ProductoInsumo::where('producto_id', $this->producto_id)
            ->findOrFail($id);

        $receta->delete();

        $this->actualizarCostoProducto();

        session()->flash('message', 'Insumo eliminado del producto.');
    }

    public function cancelar()
    {
        $this->resetInput();
    }

    private function actualizarCostoProducto()
    {
        $recetas = ProductoInsumo::with('insumo')
            ->where('producto_id', $this->producto_id)
            ->get();

        $costoTotal = $recetas->sum(function ($receta) {
            return $receta->cantidad_por_unidad * $receta->insumo->costo_unitario_real;
        });

        $producto = Producto::findOrFail($this->producto_id);

        $producto->update([
            'costo_unitario' => round($costoTotal, 2),
        ]);

        $this->producto = $producto->fresh();
    }

    private function resetInput()
    {
        $this->receta_id = null;
        $this->insumo_id = null;
        $this->cantidad_por_unidad = 1;
        $this->modalTitle = 'Agregar insumo al producto';

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

        $recetas = ProductoInsumo::with('insumo')
            ->where('producto_id', $this->producto_id)
            ->get();

        $costoTotal = $recetas->sum(function ($receta) {
            return $receta->cantidad_por_unidad * $receta->insumo->costo_unitario_real;
        });

        $precioVenta = $this->producto->precio_venta;
        $utilidad = $precioVenta - $costoTotal;

        $margen = $precioVenta > 0
            ? ($utilidad / $precioVenta) * 100
            : 0;

        return view('livewire.productos.producto-insumos', [
            'insumos' => $insumos,
            'recetas' => $recetas,
            'costoTotal' => $costoTotal,
            'precioVenta' => $precioVenta,
            'utilidad' => $utilidad,
            'margen' => $margen,
        ]);
    }
}
