<?php

namespace App\Http\Livewire\Productos;

use App\Models\Catalogo;
use App\Models\Producto;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class ProductoIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 10;
    public $filtroEstado = 'activos';
    public $filtroCategoria = 'todas';
    public $filtroTipo = 'todos';

    public $producto_id;

    public $codigo;
    public $codigo_barra;
    public $nombre;

    public $categoria = 'Personalizado';
    public $tipo_producto = 'Fabricado';
    public $unidad_venta = 'Unidad';

    public $maneja_inventario = true;
    public $usa_receta = false;

    public $ancho_cm;
    public $largo_cm;
    public $espesor_mm;

    public $stock_actual = 0;
    public $stock_minimo = 0;

    public $costo_compra = 0;
    public $costo_unitario = 0;
    public $precio_venta = 0;

    public $descripcion;
    public $activo = true;

    public $modalTitle = 'Nuevo producto';

    public $categorias = [];
    public $tiposProducto = [];
    public $unidadesVenta = [];

    public $tipo_impuesto = 'Gravado 15%';
    public $porcentaje_isv = 15;
    public $tiposImpuesto = [];

    public function mount()
    {
        $this->categorias = Catalogo::opciones('categoria_producto')->pluck('nombre')->toArray();
        $this->tiposProducto = Catalogo::opciones('tipo_producto')->pluck('nombre')->toArray();
        $this->unidadesVenta = Catalogo::opciones('unidad_venta')->pluck('nombre')->toArray();

        $this->categoria = $this->categorias[0] ?? 'Personalizado';
        $this->tipo_producto = $this->tiposProducto[0] ?? 'Fabricado';
        $this->unidad_venta = $this->unidadesVenta[0] ?? 'Unidad';

        $this->tiposImpuesto = Catalogo::opciones('tipo_impuesto')->pluck('nombre')->toArray();

        $this->tipo_impuesto = in_array('Gravado 15%', $this->tiposImpuesto)
            ? 'Gravado 15%'
            : ($this->tiposImpuesto[0] ?? 'Gravado 15%');

        $this->porcentaje_isv = $this->obtenerPorcentajeIsv();
    }

    protected function rules()
    {
        return [
            'codigo' => [
                'nullable',
                'max:30',
                Rule::unique('productos', 'codigo')->ignore($this->producto_id),
            ],

            'codigo_barra' => [
                'nullable',
                'max:100',
                Rule::unique('productos', 'codigo_barra')->ignore($this->producto_id),
            ],

            'nombre' => 'required|min:3|max:150',

            'categoria' => 'required|max:50',
            'tipo_producto' => 'required|max:50',
            'unidad_venta' => 'required|max:50',

            'maneja_inventario' => 'boolean',
            'usa_receta' => 'boolean',

            'ancho_cm' => 'nullable|numeric|min:0',
            'largo_cm' => 'nullable|numeric|min:0',
            'espesor_mm' => 'nullable|numeric|min:0',

            'stock_actual' => 'required|numeric|min:0',
            'stock_minimo' => 'required|numeric|min:0',

            'costo_compra' => 'required|numeric|min:0',
            'costo_unitario' => 'required|numeric|min:0',
            'precio_venta' => 'required|numeric|min:0',

            'descripcion' => 'nullable|max:500',
            'activo' => 'boolean',

            'tipo_impuesto' => 'required|max:50',
            'porcentaje_isv' => 'required|numeric|min:0|max:100',
        ];
    }

    protected $messages = [
        // 'codigo.required' => 'El código del producto es obligatorio.',
        'codigo.unique' => 'Este código ya está registrado.',
        'codigo_barra.unique' => 'Este código de barra ya está registrado.',
        'nombre.required' => 'El nombre del producto es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
        'costo_compra.required' => 'El costo de compra es obligatorio.',
        'costo_unitario.required' => 'El costo unitario es obligatorio.',
        'precio_venta.required' => 'El precio de venta es obligatorio.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function updatingFiltroCategoria()
    {
        $this->resetPage();
    }

    public function updatingFiltroTipo()
    {
        $this->resetPage();
    }

    public function updated($propertyName)
    {
        if (in_array($propertyName, [
            'costo_compra',
            'usa_receta',
            'maneja_inventario',
        ])) {
            $this->ajustarCostosYStock();
        }
    }

    public function create()
    {
        $this->resetInput();

        $this->modalTitle = 'Nuevo producto';

        $this->dispatchBrowserEvent('open-producto-modal');
    }

    public function store()
    {
        $this->ajustarCostosYStock();

        $this->validate();

        Producto::create([
            'codigo' => $this->codigo ? strtoupper(trim($this->codigo)) : null,
            'codigo_barra' => $this->codigo_barra ? trim($this->codigo_barra) : null,
            'nombre' => trim($this->nombre),

            'categoria' => $this->categoria,
            'tipo_producto' => $this->tipo_producto,
            'unidad_venta' => $this->unidad_venta,

            'maneja_inventario' => $this->maneja_inventario,
            'usa_receta' => $this->usa_receta,

            'ancho_cm' => $this->ancho_cm,
            'largo_cm' => $this->largo_cm,
            'espesor_mm' => $this->espesor_mm,

            // 'stock_actual' => $this->stock_actual,
            'stock_minimo' => $this->stock_minimo,

            'costo_compra' => $this->costo_compra,
            'costo_unitario' => $this->costo_unitario,
            'precio_venta' => $this->precio_venta,

            'descripcion' => $this->descripcion,
            'activo' => $this->activo,

            'tipo_impuesto' => $this->tipo_impuesto,
            'porcentaje_isv' => $this->obtenerPorcentajeIsv(),
        ]);

        $this->resetInput();

        $this->dispatchBrowserEvent('close-producto-modal');

        session()->flash('message', 'Producto registrado correctamente.');
    }

    public function edit($id)
    {
        $producto = Producto::findOrFail($id);

        $this->producto_id = $producto->id;

        $this->codigo = $producto->codigo;
        $this->codigo_barra = $producto->codigo_barra;
        $this->nombre = $producto->nombre;

        $this->categoria = $producto->categoria;
        $this->tipo_producto = $producto->tipo_producto;
        $this->unidad_venta = $producto->unidad_venta;

        $this->maneja_inventario = $producto->maneja_inventario;
        $this->usa_receta = $producto->usa_receta;

        $this->ancho_cm = $producto->ancho_cm;
        $this->largo_cm = $producto->largo_cm;
        $this->espesor_mm = $producto->espesor_mm;

        $this->stock_actual = $producto->stock_actual;
        $this->stock_minimo = $producto->stock_minimo;

        $this->costo_compra = $producto->costo_compra;
        $this->costo_unitario = $producto->costo_unitario;
        $this->precio_venta = $producto->precio_venta;

        $this->descripcion = $producto->descripcion;
        $this->activo = $producto->activo;

        $this->modalTitle = 'Editar producto';

        $this->tipo_impuesto = $producto->tipo_impuesto ?? 'Gravado 15%';
        $this->porcentaje_isv = $producto->porcentaje_isv ?? 15;

        $this->dispatchBrowserEvent('open-producto-modal');
    }

    public function update()
    {
        $this->ajustarCostosYStock();

        $this->validate();

        $producto = Producto::findOrFail($this->producto_id);

        $producto->update([
            'codigo' => $this->codigo ? strtoupper(trim($this->codigo)) : $producto->codigo,
            'codigo_barra' => $this->codigo_barra ? trim($this->codigo_barra) : null,
            'nombre' => trim($this->nombre),

            'categoria' => $this->categoria,
            'tipo_producto' => $this->tipo_producto,
            'unidad_venta' => $this->unidad_venta,

            'maneja_inventario' => $this->maneja_inventario,
            'usa_receta' => $this->usa_receta,

            'ancho_cm' => $this->ancho_cm,
            'largo_cm' => $this->largo_cm,
            'espesor_mm' => $this->espesor_mm,

            // No actualizar stock_actual desde edición.
            // El stock se modifica únicamente desde movimientos.
            // 'stock_actual' => 0,
            // 'stock_actual' => $this->stock_actual,
            'stock_minimo' => $this->stock_minimo,

            'costo_compra' => $this->costo_compra,
            'costo_unitario' => $this->costo_unitario,
            'precio_venta' => $this->precio_venta,

            'descripcion' => $this->descripcion,
            'activo' => $this->activo,

            'tipo_impuesto' => $this->tipo_impuesto,
            'porcentaje_isv' => $this->obtenerPorcentajeIsv(),
        ]);

        $this->resetInput();

        $this->dispatchBrowserEvent('close-producto-modal');

        session()->flash('message', 'Producto actualizado correctamente.');
    }

    public function cambiarEstado($id)
    {
        $producto = Producto::findOrFail($id);

        $producto->update([
            'activo' => !$producto->activo,
        ]);

        session()->flash('message', 'Estado del producto actualizado correctamente.');
    }

    private function ajustarCostosYStock()
    {
        if (!$this->usa_receta) {
            $this->costo_unitario = $this->costo_compra;
        }

        if (!$this->maneja_inventario) {
            $this->stock_actual = 0;
            $this->stock_minimo = 0;
        }
    }

    private function resetInput()
    {
        $this->producto_id = null;

        $this->codigo = '';
        $this->codigo_barra = '';
        $this->nombre = '';

        $this->categoria = $this->categorias[0] ?? 'Personalizado';
        $this->tipo_producto = $this->tiposProducto[0] ?? 'Fabricado';
        $this->unidad_venta = $this->unidadesVenta[0] ?? 'Unidad';

        $this->maneja_inventario = true;
        $this->usa_receta = false;

        $this->ancho_cm = null;
        $this->largo_cm = null;
        $this->espesor_mm = null;

        $this->stock_actual = 0;
        $this->stock_minimo = 0;

        $this->costo_compra = 0;
        $this->costo_unitario = 0;
        $this->precio_venta = 0;

        $this->descripcion = '';
        $this->activo = true;

        $this->tipo_impuesto = in_array('Gravado 15%', $this->tiposImpuesto)
            ? 'Gravado 15%'
            : ($this->tiposImpuesto[0] ?? 'Gravado 15%');

        $this->porcentaje_isv = $this->obtenerPorcentajeIsv();

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function updatedTipoImpuesto()
    {
        $this->porcentaje_isv = $this->obtenerPorcentajeIsv();
    }

    private function obtenerPorcentajeIsv()
    {
        if ($this->tipo_impuesto === 'Gravado 15%') {
            return 15;
        }

        return 0;
    }

    public function render()
    {
        $productos = Producto::query()
            ->where(function ($query) {
                $query->where('codigo', 'like', '%' . $this->search . '%')
                    ->orWhere('codigo_barra', 'like', '%' . $this->search . '%')
                    ->orWhere('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('categoria', 'like', '%' . $this->search . '%')
                    ->orWhere('tipo_producto', 'like', '%' . $this->search . '%');
            })
            ->when($this->filtroEstado === 'activos', function ($query) {
                $query->where('activo', true);
            })
            ->when($this->filtroEstado === 'inactivos', function ($query) {
                $query->where('activo', false);
            })
            ->when($this->filtroCategoria !== 'todas', function ($query) {
                $query->where('categoria', $this->filtroCategoria);
            })
            ->when($this->filtroTipo !== 'todos', function ($query) {
                $query->where('tipo_producto', $this->filtroTipo);
            })
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->paginate($this->perPage);

        return view('livewire.productos.producto-index', [
            'productos' => $productos,
        ]);

    }
}
