<?php

namespace App\Http\Livewire\Insumos;

use App\Models\Insumo;
use App\Models\MovimientoInventario;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\LoteInsumo;
use App\Models\MovimientoInventarioLote;
use App\Models\Catalogo;

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

    public $categorias = [];
    public $unidadesCompra = [];
    public $unidadesConsumo = [];

    public $tiposMovimiento = [
        'Entrada compra',
        'Entrada ajuste',
        'Salida venta',
        'Salida produccion',
        'Salida daño',
        'Salida prueba',
        'Salida ajuste',
        'Devolucion',
    ];

    public function mount()
    {
        $this->categorias = Catalogo::opciones('categoria_insumo')->pluck('nombre')->toArray();
        $this->unidadesCompra = Catalogo::opciones('unidad_compra')->pluck('nombre')->toArray();
        $this->unidadesConsumo = Catalogo::opciones('unidad_consumo')->pluck('nombre')->toArray();

        $this->categoria = $this->categorias[0] ?? 'Papel';
        $this->unidad_compra = $this->unidadesCompra[0] ?? 'Resma';
        $this->unidad_consumo = $this->unidadesConsumo[0] ?? 'Hoja';
    }

    protected function rules()
    {
        return [
            'codigo' => [
                'required',
                'max:30',
                Rule::unique('insumos', 'codigo')->ignore($this->insumo_id),
            ],
            'nombre' => 'required|min:3|max:150',
            'categoria' => 'required|max:50',
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

        DB::transaction(function () {
            $stockInicial = (float) $this->stock_actual;

            $insumo = Insumo::create([
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

                // En PEPS el stock se controla por lotes.
                // Por eso primero se crea en 0.
                'stock_actual' => 0,
                'stock_minimo' => $this->stock_minimo,

                'descripcion' => $this->descripcion,
                'activo' => $this->activo,
            ]);

            if ($stockInicial > 0) {
                $this->registrarEntradaLote(
                    $insumo,
                    'Entrada ajuste',
                    $stockInicial,
                    $this->costo_unitario_base,
                    'Inventario inicial',
                    'Registro inicial del insumo'
                );
            }
        });

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

            // No actualizamos stock_actual aquí.
            // El stock se modifica por movimientos PEPS.
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
            'movimiento_cantidad' => 'required|numeric|min:0.01',
            'movimiento_costo_unitario' => 'required|numeric|min:0',
            'movimiento_referencia' => 'nullable|max:100',
            'movimiento_observacion' => 'nullable|max:500',
        ]);

        $insumo = Insumo::findOrFail($this->movimiento_insumo_id);

        $cantidad = (float) $this->movimiento_cantidad;
        $costoUnitario = (float) $this->movimiento_costo_unitario;

        if ($this->esEntrada($this->movimiento_tipo) && $costoUnitario <= 0) {
            $this->addError('movimiento_costo_unitario', 'Para una entrada debe ingresar un costo unitario mayor que cero.');
            return;
        }

        if ($this->esSalida($this->movimiento_tipo)) {
            $stockDisponiblePeps = LoteInsumo::where('insumo_id', $insumo->id)
                ->where('activo', true)
                ->where('cantidad_disponible', '>', 0)
                ->sum('cantidad_disponible');

            if ($cantidad > $stockDisponiblePeps) {
                $this->addError('movimiento_cantidad', 'No hay suficiente stock disponible en lotes PEPS.');
                return;
            }
        }

        DB::transaction(function () use ($insumo, $cantidad, $costoUnitario) {
            if ($this->esEntrada($this->movimiento_tipo)) {
                $this->registrarEntradaLote(
                    $insumo,
                    $this->movimiento_tipo,
                    $cantidad,
                    $costoUnitario,
                    $this->movimiento_referencia,
                    $this->movimiento_observacion
                );
            }

            if ($this->esSalida($this->movimiento_tipo)) {
                $movimiento = MovimientoInventario::create([
                    'insumo_id' => $insumo->id,
                    'tipo_movimiento' => $this->movimiento_tipo,
                    'cantidad' => $cantidad,
                    'costo_unitario' => 0,
                    'total' => 0,
                    'referencia' => $this->movimiento_referencia,
                    'observacion' => $this->movimiento_observacion,
                ]);

                $totalSalida = $this->descontarPorPeps($insumo, $cantidad, $movimiento->id);

                $movimiento->update([
                    'costo_unitario' => round($totalSalida / $cantidad, 4),
                    'total' => round($totalSalida, 2),
                ]);

                $this->actualizarCostoActualPeps($insumo);
            }
        });

        $this->resetMovimiento();

        $this->dispatchBrowserEvent('close-movimiento-modal');

        session()->flash('message', 'Movimiento de inventario registrado correctamente.');
    }

    private function registrarEntradaLote($insumo, $tipoMovimiento, $cantidad, $costoUnitario, $referencia = null, $observacion = null)
    {
        $total = round($cantidad * $costoUnitario, 2);

        $movimiento = MovimientoInventario::create([
            'insumo_id' => $insumo->id,
            'tipo_movimiento' => $tipoMovimiento,
            'cantidad' => $cantidad,
            'costo_unitario' => $costoUnitario,
            'total' => $total,
            'referencia' => $referencia,
            'observacion' => $observacion,
        ]);

        $lote = LoteInsumo::create([
            'insumo_id' => $insumo->id,
            'codigo_lote' => 'LOT-' . $insumo->id . '-' . now()->format('YmdHis'),
            'fecha_entrada' => now()->format('Y-m-d'),
            'cantidad_inicial' => $cantidad,
            'cantidad_disponible' => $cantidad,
            'costo_unitario' => $costoUnitario,
            'total' => $total,
            'referencia' => $referencia,
            'observacion' => $observacion,
            'activo' => true,
        ]);

        MovimientoInventarioLote::create([
            'movimiento_inventario_id' => $movimiento->id,
            'lote_insumo_id' => $lote->id,
            'cantidad' => $cantidad,
            'costo_unitario' => $costoUnitario,
            'total' => $total,
        ]);

        $this->actualizarCostoActualPeps($insumo);
    }

    private function descontarPorPeps($insumo, $cantidadSalida, $movimientoId)
    {
        $cantidadPendiente = $cantidadSalida;
        $totalSalida = 0;

        $lotes = LoteInsumo::where('insumo_id', $insumo->id)
            ->where('activo', true)
            ->where('cantidad_disponible', '>', 0)
            ->orderBy('fecha_entrada')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($lotes as $lote) {
            if ($cantidadPendiente <= 0) {
                break;
            }

            $cantidadDisponible = (float) $lote->cantidad_disponible;
            $cantidadTomada = min($cantidadPendiente, $cantidadDisponible);

            $totalDetalle = round($cantidadTomada * (float) $lote->costo_unitario, 2);

            MovimientoInventarioLote::create([
                'movimiento_inventario_id' => $movimientoId,
                'lote_insumo_id' => $lote->id,
                'cantidad' => $cantidadTomada,
                'costo_unitario' => $lote->costo_unitario,
                'total' => $totalDetalle,
            ]);

            $nuevaCantidadDisponible = round($cantidadDisponible - $cantidadTomada, 2);

            $lote->update([
                'cantidad_disponible' => $nuevaCantidadDisponible,
                'activo' => $nuevaCantidadDisponible > 0,
            ]);

            $totalSalida += $totalDetalle;
            $cantidadPendiente = round($cantidadPendiente - $cantidadTomada, 2);
        }

        return round($totalSalida, 2);
    }

    private function actualizarCostoActualPeps($insumo)
    {
        $stockActual = LoteInsumo::where('insumo_id', $insumo->id)
            ->where('activo', true)
            ->sum('cantidad_disponible');

        $proximoLote = LoteInsumo::where('insumo_id', $insumo->id)
            ->where('activo', true)
            ->where('cantidad_disponible', '>', 0)
            ->orderBy('fecha_entrada')
            ->orderBy('id')
            ->first();

        $costoBase = $proximoLote
            ? (float) $proximoLote->costo_unitario
            : (float) $insumo->costo_unitario_base;

        $merma = (float) $insumo->porcentaje_merma;

        if ($merma > 0 && $merma < 100) {
            $costoReal = $costoBase / (1 - ($merma / 100));
        } else {
            $costoReal = $costoBase;
        }

        $insumo->update([
            'stock_actual' => round($stockActual, 2),
            'costo_unitario_base' => round($costoBase, 4),
            'costo_unitario_real' => round($costoReal, 4),
        ]);
    }
    
    private function actualizarCostoPromedio($insumo, $cantidadEntrada, $costoUnitarioEntrada)
    {
        $stockAnterior = (float) $insumo->stock_actual;
        $costoAnterior = (float) $insumo->costo_unitario_base;

        $valorAnterior = $stockAnterior * $costoAnterior;
        $valorEntrada = $cantidadEntrada * $costoUnitarioEntrada;

        $nuevoStock = $stockAnterior + $cantidadEntrada;

        if ($nuevoStock > 0) {
            $nuevoCostoBase = ($valorAnterior + $valorEntrada) / $nuevoStock;
        } else {
            $nuevoCostoBase = 0;
        }

        $merma = (float) $insumo->porcentaje_merma;

        if ($merma > 0 && $merma < 100) {
            $nuevoCostoReal = $nuevoCostoBase / (1 - ($merma / 100));
        } else {
            $nuevoCostoReal = $nuevoCostoBase;
        }

        $insumo->update([
            'stock_actual' => round($nuevoStock, 2),
            'costo_unitario_base' => round($nuevoCostoBase, 4),
            'costo_unitario_real' => round($nuevoCostoReal, 4),
        ]);
    }

    private function esEntrada($tipo)
    {
        return in_array($tipo, [
            'Entrada compra',
            'Entrada ajuste',
            'Devolucion',
        ]);
    }

    private function esSalida($tipoMovimiento)
    {
        return in_array($tipoMovimiento, [
            'Salida venta',
            'Salida produccion',
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
        $this->categoria = $this->categorias[0] ?? 'Papel';

        $this->unidad_compra = $this->unidadesCompra[0] ?? 'Resma';
        $this->cantidad_por_compra = 0;
        $this->unidad_consumo = $this->unidadesConsumo[0] ?? 'Hoja';

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
