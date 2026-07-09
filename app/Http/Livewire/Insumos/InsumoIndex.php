<?php

namespace App\Http\Livewire\Insumos;

use App\Models\Insumo;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class InsumoIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 10;
    public $filtroEstado = 'activos';
    public $filtroCategoria = 'todas';

    public $insumo_id;

    public $codigo;
    public $nombre;
    public $categoria = 'Papel';

    public $unidad_compra = 'Resma';
    public $cantidad_por_compra = 0;
    public $unidad_consumo = 'Hoja';

    public $ancho_cm;
    public $largo_cm;
    public $espesor_mm;

    public $costo_compra = 0;
    public $costo_unitario_base = 0;
    public $porcentaje_merma = 0;
    public $costo_unitario_real = 0;

    public $stock_actual = 0;
    public $stock_minimo = 0;

    public $descripcion;
    public $activo = true;

    public $modalTitle = 'Nuevo insumo';

    public $movimiento_insumo_id;
    public $movimiento_tipo = 'Entrada compra';
    public $movimiento_cantidad = 0;
    public $movimiento_costo_unitario = 0;
    public $movimiento_total = 0;
    public $movimiento_referencia;
    public $movimiento_observacion;

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

    public $tiposMovimiento = [
        'Entrada compra',
        'Entrada ajuste',
        'Salida venta',
        'Salida daño',
        'Salida prueba',
        'Salida ajuste',
        'Devolucion',
    ];

    protected function rules()
    {
        return [
            'codigo' => [
                'required',
                'max:30',
                Rule::unique('insumos', 'codigo')->ignore($this->insumo_id),
            ],
            'nombre' => 'required|min:3|max:150',
            'categoria' => 'required',
            'unidad_compra' => 'required|max:50',
            'cantidad_por_compra' => 'required|numeric|min:0.0001',
            'unidad_consumo' => 'required|max:50',

            'ancho_cm' => 'nullable|numeric|min:0',
            'largo_cm' => 'nullable|numeric|min:0',
            'espesor_mm' => 'nullable|numeric|min:0',

            'costo_compra' => 'required|numeric|min:0',
            'porcentaje_merma' => 'required|numeric|min:0|max:99.99',

            'stock_actual' => 'required|numeric|min:0',
            'stock_minimo' => 'required|numeric|min:0',

            'descripcion' => 'nullable|max:500',
            'activo' => 'boolean',
        ];
    }

    protected $messages = [
        'codigo.required' => 'El código del insumo es obligatorio.',
        'codigo.unique' => 'Este código ya está registrado.',
        'nombre.required' => 'El nombre del insumo es obligatorio.',
        'cantidad_por_compra.min' => 'La cantidad por compra debe ser mayor que cero.',
        'costo_compra.required' => 'El costo de compra es obligatorio.',
        'porcentaje_merma.max' => 'La merma no puede ser igual o mayor al 100%.',
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

    public function updated($propertyName)
    {
        if (in_array($propertyName, [
            'costo_compra',
            'cantidad_por_compra',
            'porcentaje_merma',
            'movimiento_cantidad',
            'movimiento_costo_unitario',
        ])) {
            $this->calcularCostosFormulario();
            $this->calcularTotalMovimiento();
        }
    }

    public function create()
    {
        $this->resetInput();

        $this->modalTitle = 'Nuevo insumo';

        $this->dispatchBrowserEvent('open-insumo-modal');
    }

    public function store()
    {
        $this->calcularCostosFormulario();

        $this->validate();

        Insumo::create([
            'codigo' => strtoupper(trim($this->codigo)),
            'nombre' => trim($this->nombre),
            'categoria' => $this->categoria,

            'unidad_compra' => trim($this->unidad_compra),
            'cantidad_por_compra' => $this->cantidad_por_compra,
            'unidad_consumo' => trim($this->unidad_consumo),

            'ancho_cm' => $this->ancho_cm,
            'largo_cm' => $this->largo_cm,
            'espesor_mm' => $this->espesor_mm,

            'costo_compra' => $this->costo_compra,
            'costo_unitario_base' => $this->costo_unitario_base,
            'porcentaje_merma' => $this->porcentaje_merma,
            'costo_unitario_real' => $this->costo_unitario_real,

            'stock_actual' => $this->stock_actual,
            'stock_minimo' => $this->stock_minimo,

            'descripcion' => $this->descripcion,
            'activo' => $this->activo,
        ]);

        $this->resetInput();

        $this->dispatchBrowserEvent('close-insumo-modal');

        session()->flash('message', 'Insumo registrado correctamente.');
    }

    public function edit($id)
    {
        $insumo = Insumo::findOrFail($id);

        $this->insumo_id = $insumo->id;

        $this->codigo = $insumo->codigo;
        $this->nombre = $insumo->nombre;
        $this->categoria = $insumo->categoria;

        $this->unidad_compra = $insumo->unidad_compra;
        $this->cantidad_por_compra = $insumo->cantidad_por_compra;
        $this->unidad_consumo = $insumo->unidad_consumo;

        $this->ancho_cm = $insumo->ancho_cm;
        $this->largo_cm = $insumo->largo_cm;
        $this->espesor_mm = $insumo->espesor_mm;

        $this->costo_compra = $insumo->costo_compra;
        $this->costo_unitario_base = $insumo->costo_unitario_base;
        $this->porcentaje_merma = $insumo->porcentaje_merma;
        $this->costo_unitario_real = $insumo->costo_unitario_real;

        $this->stock_actual = $insumo->stock_actual;
        $this->stock_minimo = $insumo->stock_minimo;

        $this->descripcion = $insumo->descripcion;
        $this->activo = $insumo->activo;

        $this->modalTitle = 'Editar insumo';

        $this->dispatchBrowserEvent('open-insumo-modal');
    }

    public function update()
    {
        $this->calcularCostosFormulario();

        $this->validate();

        $insumo = Insumo::findOrFail($this->insumo_id);

        $insumo->update([
            'codigo' => strtoupper(trim($this->codigo)),
            'nombre' => trim($this->nombre),
            'categoria' => $this->categoria,

            'unidad_compra' => trim($this->unidad_compra),
            'cantidad_por_compra' => $this->cantidad_por_compra,
            'unidad_consumo' => trim($this->unidad_consumo),

            'ancho_cm' => $this->ancho_cm,
            'largo_cm' => $this->largo_cm,
            'espesor_mm' => $this->espesor_mm,

            'costo_compra' => $this->costo_compra,
            'costo_unitario_base' => $this->costo_unitario_base,
            'porcentaje_merma' => $this->porcentaje_merma,
            'costo_unitario_real' => $this->costo_unitario_real,

            'stock_actual' => $this->stock_actual,
            'stock_minimo' => $this->stock_minimo,

            'descripcion' => $this->descripcion,
            'activo' => $this->activo,
        ]);

        $this->resetInput();

        $this->dispatchBrowserEvent('close-insumo-modal');

        session()->flash('message', 'Insumo actualizado correctamente.');
    }

    public function cambiarEstado($id)
    {
        $insumo = Insumo::findOrFail($id);

        $insumo->update([
            'activo' => !$insumo->activo,
        ]);

        session()->flash('message', 'Estado del insumo actualizado correctamente.');
    }

    public function abrirMovimiento($id)
    {
        $insumo = Insumo::findOrFail($id);

        $this->resetMovimiento();

        $this->movimiento_insumo_id = $insumo->id;

        // Redondeamos a 2 decimales para evitar error del navegador con step="0.01"
        $this->movimiento_costo_unitario = round($insumo->costo_unitario_real, 2);

        $this->calcularTotalMovimiento();

        $this->dispatchBrowserEvent('open-movimiento-modal');
    }

    public function storeMovimiento()
    {
        $this->validate([
            'movimiento_insumo_id' => 'required|exists:insumos,id',
            'movimiento_tipo' => 'required',
            'movimiento_cantidad' => 'required|numeric|min:0.0001',
            'movimiento_costo_unitario' => 'required|numeric|min:0',
            'movimiento_referencia' => 'nullable|max:100',
            'movimiento_observacion' => 'nullable|max:500',
        ]);

        $insumo = Insumo::findOrFail($this->movimiento_insumo_id);

        $cantidad = (float) $this->movimiento_cantidad;

        if ($this->esSalida($this->movimiento_tipo) && $cantidad > $insumo->stock_actual) {
            $this->addError('movimiento_cantidad', 'No hay suficiente stock para realizar esta salida.');
            return;
        }

        DB::transaction(function () use ($insumo, $cantidad) {
            $this->calcularTotalMovimiento();

            MovimientoInventario::create([
                'insumo_id' => $this->movimiento_insumo_id,
                'tipo_movimiento' => $this->movimiento_tipo,
                'cantidad' => $cantidad,
                'costo_unitario' => $this->movimiento_costo_unitario,
                'total' => $this->movimiento_total,
                'referencia' => $this->movimiento_referencia,
                'observacion' => $this->movimiento_observacion,
            ]);

            if ($this->esEntrada($this->movimiento_tipo)) {
                $insumo->stock_actual = $insumo->stock_actual + $cantidad;
            }

            if ($this->esSalida($this->movimiento_tipo)) {
                $insumo->stock_actual = $insumo->stock_actual - $cantidad;
            }

            $insumo->save();
        });

        $this->resetMovimiento();

        $this->dispatchBrowserEvent('close-movimiento-modal');

        session()->flash('message', 'Movimiento de inventario registrado correctamente.');
    }

    private function esEntrada($tipo)
    {
        return in_array($tipo, [
            'Entrada compra',
            'Entrada ajuste',
            'Devolucion',
        ]);
    }

    private function esSalida($tipo)
    {
        return in_array($tipo, [
            'Salida venta',
            'Salida daño',
            'Salida prueba',
            'Salida ajuste',
        ]);
    }

    private function calcularCostosFormulario()
    {
        $costoCompra = (float) $this->costo_compra;
        $cantidadCompra = (float) $this->cantidad_por_compra;
        $merma = (float) $this->porcentaje_merma;

        if ($cantidadCompra > 0) {
            $this->costo_unitario_base = round($costoCompra / $cantidadCompra, 4);
        } else {
            $this->costo_unitario_base = 0;
        }

        if ($merma > 0 && $merma < 100) {
            $this->costo_unitario_real = round($this->costo_unitario_base / (1 - ($merma / 100)), 4);
        } else {
            $this->costo_unitario_real = $this->costo_unitario_base;
        }
    }

    private function calcularTotalMovimiento()
    {
        $this->movimiento_total = round(
            (float) $this->movimiento_cantidad * (float) $this->movimiento_costo_unitario,
            2
        );
    }

    private function resetInput()
    {
        $this->insumo_id = null;

        $this->codigo = '';
        $this->nombre = '';
        $this->categoria = 'Papel';

        $this->unidad_compra = 'Resma';
        $this->cantidad_por_compra = 0;
        $this->unidad_consumo = 'Hoja';

        $this->ancho_cm = null;
        $this->largo_cm = null;
        $this->espesor_mm = null;

        $this->costo_compra = 0;
        $this->costo_unitario_base = 0;
        $this->porcentaje_merma = 0;
        $this->costo_unitario_real = 0;

        $this->stock_actual = 0;
        $this->stock_minimo = 0;

        $this->descripcion = '';
        $this->activo = true;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    private function resetMovimiento()
    {
        $this->movimiento_insumo_id = null;
        $this->movimiento_tipo = 'Entrada compra';
        $this->movimiento_cantidad = 0;
        $this->movimiento_costo_unitario = 0;
        $this->movimiento_total = 0;
        $this->movimiento_referencia = '';
        $this->movimiento_observacion = '';

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $insumos = Insumo::query()
            ->where(function ($query) {
                $query->where('codigo', 'like', '%' . $this->search . '%')
                    ->orWhere('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('categoria', 'like', '%' . $this->search . '%')
                    ->orWhere('unidad_compra', 'like', '%' . $this->search . '%')
                    ->orWhere('unidad_consumo', 'like', '%' . $this->search . '%');
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
            ->orderBy('categoria')
            ->orderBy('nombre')
            ->paginate($this->perPage);

        return view('livewire.insumos.insumo-index', [
            'insumos' => $insumos,
        ]);
    }
}
