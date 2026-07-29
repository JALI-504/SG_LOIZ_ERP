<?php

namespace App\Http\Livewire\Produccion;

use App\Models\Insumo;
use App\Models\LoteInsumo;
use App\Models\LoteProducto;
use App\Models\MovimientoInventario;
use App\Models\MovimientoInventarioLote;
use App\Models\MovimientoProducto;
use App\Models\MovimientoProductoLote;
use App\Models\Produccion;
use App\Models\ProduccionInsumo;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ProduccionIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $producto_id;
    public $cantidad = 1;
    public $fecha;
    public $observacion;

    public $recetaCalculada = [];

    public $search = '';
    public $perPage = 10;

    protected $messages = [
        'producto_id.required' => 'Debe seleccionar un producto.',
        'producto_id.exists' => 'El producto seleccionado no existe.',
        'cantidad.required' => 'Debe ingresar la cantidad a producir.',
        'cantidad.numeric' => 'La cantidad debe ser numérica.',
        'cantidad.min' => 'La cantidad debe ser mayor que cero.',
        'fecha.required' => 'Debe seleccionar la fecha.',
        'fecha.date' => 'La fecha no es válida.',
        'observacion.max' => 'La observación no debe superar los 500 caracteres.',
    ];

    public function mount()
    {
        if (!auth()->user()->can('ver produccion')) {
            abort(403, 'No tiene permiso para ver producción.');
        }

        $this->fecha = now()->format('Y-m-d');
    }

    protected function rules()
    {
        return [
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|numeric|min:0.01',
            'fecha' => 'required|date',
            'observacion' => 'nullable|max:500',
        ];
    }

    public function updatedProductoId()
    {
        $this->cargarRecetaCalculada();
    }

    public function updatedCantidad()
    {
        $this->cargarRecetaCalculada();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function registrarProduccion()
    {
        if (!auth()->user()->can('crear produccion')) {
            abort(403, 'No tiene permiso para registrar producción.');
        }

        $this->validate();

        $producto = Producto::with('recetas.insumo')->findOrFail($this->producto_id);
        $cantidadProducto = (float) $this->cantidad;

        if (!$producto->maneja_inventario) {
            session()->flash('error', 'Este producto no maneja inventario.');
            return;
        }

        if (!$producto->usa_receta) {
            session()->flash('error', 'Este producto no usa receta de insumos.');
            return;
        }

        if ($producto->recetas->isEmpty()) {
            session()->flash('error', 'Este producto no tiene insumos asignados en su receta.');
            return;
        }

        try {
            DB::transaction(function () use ($producto, $cantidadProducto) {
                $this->validarStockReceta($producto, $cantidadProducto);

                $produccion = Produccion::create([
                    'fecha' => $this->fecha,
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidadProducto,
                    'costo_total' => 0,
                    'costo_unitario' => 0,
                    'estado' => 'Registrada',
                    'observacion' => $this->observacion,
                    'user_id' => auth()->id(),
                ]);

                $referencia = 'Producción ' . $produccion->codigo;

                $totalCostoProduccion = 0;

                foreach ($producto->recetas as $receta) {
                    $insumo = $receta->insumo;
                    $cantidadInsumo = round((float) $receta->cantidad_por_unidad * $cantidadProducto, 4);

                    $movimientoInsumo = MovimientoInventario::create([
                        'insumo_id' => $insumo->id,
                        'tipo_movimiento' => 'Salida produccion',
                        'cantidad' => $cantidadInsumo,
                        'costo_unitario' => 0,
                        'total' => 0,
                        'referencia' => $referencia,
                        'observacion' => 'Consumo por producción de ' . $producto->nombre . '. ' . ($this->observacion ?? ''),
                    ]);

                    $totalSalidaInsumo = $this->descontarInsumoPorPeps(
                        $insumo,
                        $cantidadInsumo,
                        $movimientoInsumo->id
                    );

                    $costoUnitarioInsumo = $cantidadInsumo > 0
                        ? round($totalSalidaInsumo / $cantidadInsumo, 4)
                        : 0;

                    $movimientoInsumo->update([
                        'costo_unitario' => $costoUnitarioInsumo,
                        'total' => round($totalSalidaInsumo, 2),
                    ]);

                    ProduccionInsumo::create([
                        'produccion_id' => $produccion->id,
                        'insumo_id' => $insumo->id,
                        'movimiento_inventario_id' => $movimientoInsumo->id,
                        'cantidad_por_unidad' => $receta->cantidad_por_unidad,
                        'cantidad_total' => $cantidadInsumo,
                        'costo_unitario' => $costoUnitarioInsumo,
                        'costo_total' => round($totalSalidaInsumo, 2),
                    ]);

                    $this->actualizarCostoActualPepsInsumo($insumo);

                    $totalCostoProduccion += $totalSalidaInsumo;
                }

                $costoUnitarioProduccion = round($totalCostoProduccion / $cantidadProducto, 4);
                $totalCostoProduccion = round($totalCostoProduccion, 2);

                $movimientoProducto = MovimientoProducto::create([
                    'producto_id' => $producto->id,
                    'tipo_movimiento' => 'Entrada produccion',
                    'cantidad' => $cantidadProducto,
                    'costo_unitario' => $costoUnitarioProduccion,
                    'total' => $totalCostoProduccion,
                    'referencia' => $referencia,
                    'observacion' => $this->observacion,
                ]);

                $loteProducto = LoteProducto::create([
                    'producto_id' => $producto->id,
                    'codigo_lote' => 'PROD-' . $producto->id . '-' . $produccion->id . '-' . now()->format('YmdHis'),
                    'fecha_entrada' => $this->fecha,
                    'cantidad_inicial' => $cantidadProducto,
                    'cantidad_disponible' => $cantidadProducto,
                    'costo_unitario' => $costoUnitarioProduccion,
                    'total' => $totalCostoProduccion,
                    'referencia' => $referencia,
                    'observacion' => $this->observacion,
                    'activo' => true,
                ]);

                MovimientoProductoLote::create([
                    'movimiento_producto_id' => $movimientoProducto->id,
                    'lote_producto_id' => $loteProducto->id,
                    'cantidad' => $cantidadProducto,
                    'costo_unitario' => $costoUnitarioProduccion,
                    'total' => $totalCostoProduccion,
                ]);

                $produccion->update([
                    'costo_total' => $totalCostoProduccion,
                    'costo_unitario' => $costoUnitarioProduccion,
                    'movimiento_producto_id' => $movimientoProducto->id,
                ]);

                $this->actualizarCostoActualPepsProducto($producto);
            });
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        $this->resetFormulario();

        session()->flash('message', 'Producción registrada correctamente.');
    }

    private function validarStockReceta($producto, $cantidadProducto)
    {
        foreach ($producto->recetas as $receta) {
            $insumo = $receta->insumo;
            $cantidadNecesaria = round((float) $receta->cantidad_por_unidad * $cantidadProducto, 4);

            $stockDisponible = LoteInsumo::where('insumo_id', $insumo->id)
                ->where('activo', true)
                ->where('cantidad_disponible', '>', 0)
                ->sum('cantidad_disponible');

            if ($cantidadNecesaria > $stockDisponible) {
                throw new \Exception(
                    'No hay suficiente stock del insumo: ' .
                        $insumo->nombre .
                        '. Necesario: ' . number_format($cantidadNecesaria, 2) .
                        '. Disponible: ' . number_format($stockDisponible, 2)
                );
            }
        }
    }

    private function descontarInsumoPorPeps($insumo, $cantidadSalida, $movimientoInventarioId)
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
                'movimiento_inventario_id' => $movimientoInventarioId,
                'lote_insumo_id' => $lote->id,
                'cantidad' => $cantidadTomada,
                'costo_unitario' => $lote->costo_unitario,
                'total' => $totalDetalle,
            ]);

            $nuevaCantidadDisponible = round($cantidadDisponible - $cantidadTomada, 4);

            $lote->update([
                'cantidad_disponible' => $nuevaCantidadDisponible,
                'activo' => $nuevaCantidadDisponible > 0,
            ]);

            $totalSalida += $totalDetalle;
            $cantidadPendiente = round($cantidadPendiente - $cantidadTomada, 4);
        }

        return round($totalSalida, 2);
    }

    private function actualizarCostoActualPepsInsumo($insumo)
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

    private function actualizarCostoActualPepsProducto($producto)
    {
        $stockActual = LoteProducto::where('producto_id', $producto->id)
            ->where('activo', true)
            ->sum('cantidad_disponible');

        $proximoLote = LoteProducto::where('producto_id', $producto->id)
            ->where('activo', true)
            ->where('cantidad_disponible', '>', 0)
            ->orderBy('fecha_entrada')
            ->orderBy('id')
            ->first();

        $costoActual = $proximoLote
            ? (float) $proximoLote->costo_unitario
            : (float) $producto->costo_unitario;

        $producto->update([
            'stock_actual' => round($stockActual, 2),
            'costo_unitario' => round($costoActual, 4),
        ]);
    }

    private function cargarRecetaCalculada()
    {
        $this->recetaCalculada = [];

        if (!$this->producto_id || !$this->cantidad || $this->cantidad <= 0) {
            return;
        }

        $producto = Producto::with('recetas.insumo')->find($this->producto_id);

        if (!$producto || !$producto->usa_receta) {
            return;
        }

        foreach ($producto->recetas as $receta) {
            $insumo = $receta->insumo;
            $cantidadNecesaria = round((float) $receta->cantidad_por_unidad * (float) $this->cantidad, 4);

            $stockDisponible = LoteInsumo::where('insumo_id', $insumo->id)
                ->where('activo', true)
                ->where('cantidad_disponible', '>', 0)
                ->sum('cantidad_disponible');

            $this->recetaCalculada[] = [
                'insumo' => $insumo->nombre,
                'unidad' => $insumo->unidad_consumo,
                'cantidad_por_unidad' => $receta->cantidad_por_unidad,
                'cantidad_necesaria' => $cantidadNecesaria,
                'stock_disponible' => round($stockDisponible, 4),
                'suficiente' => $cantidadNecesaria <= $stockDisponible,
            ];
        }
    }

    private function resetFormulario()
    {
        $this->producto_id = null;
        $this->cantidad = 1;
        $this->fecha = now()->format('Y-m-d');
        $this->observacion = '';
        $this->recetaCalculada = [];

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        if (!auth()->user()->can('ver produccion')) {
            abort(403, 'No tiene permiso para ver producción.');
        }

        $productos = Producto::where('activo', true)
            ->where('maneja_inventario', true)
            ->where('usa_receta', true)
            ->orderBy('nombre')
            ->get();

        $producciones = Produccion::with(['producto', 'usuario', 'insumos.insumo'])
            ->where(function ($query) {
                $query->where('codigo', 'like', '%' . $this->search . '%')
                    ->orWhereHas('producto', function ($q) {
                        $q->where('nombre', 'like', '%' . $this->search . '%');
                    });
            })
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.produccion.produccion-index', [
            'productos' => $productos,
            'producciones' => $producciones,
        ]);
    }
}
