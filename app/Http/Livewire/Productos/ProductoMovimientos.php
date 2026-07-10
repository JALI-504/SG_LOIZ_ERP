<?php

namespace App\Http\Livewire\Productos;

use App\Models\LoteProducto;
use App\Models\MovimientoProducto;
use App\Models\MovimientoProductoLote;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\LoteInsumo;
use App\Models\MovimientoInventario;
use App\Models\MovimientoInventarioLote;

class ProductoMovimientos extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $producto;
    public $producto_id;

    public $movimiento_tipo = 'Entrada compra';
    public $movimiento_cantidad = 1;
    public $movimiento_costo_unitario = 0;
    public $movimiento_referencia;
    public $movimiento_observacion;

    public $tiposMovimiento = [
        'Entrada compra',
        'Entrada produccion',
        'Entrada ajuste',
        'Salida venta',
        'Salida daño',
        'Salida ajuste',
        'Devolucion',
    ];

    public function mount($productoId)
    {
        $this->producto = Producto::findOrFail($productoId);
        $this->producto_id = $this->producto->id;

        if ($this->producto->usa_receta) {
            $this->movimiento_tipo = 'Entrada produccion';
            $this->movimiento_costo_unitario = $this->producto->costo_unitario;
        } else {
            $this->movimiento_tipo = 'Entrada compra';
            $this->movimiento_costo_unitario = $this->producto->costo_compra ?: $this->producto->costo_unitario;
        }
    }

    protected function rules()
    {
        return [
            'movimiento_tipo' => 'required',
            'movimiento_cantidad' => 'required|numeric|min:0.01',
            'movimiento_costo_unitario' => 'required|numeric|min:0',
            'movimiento_referencia' => 'nullable|max:100',
            'movimiento_observacion' => 'nullable|max:500',
        ];
    }

    protected $messages = [
        'movimiento_tipo.required' => 'Debe seleccionar el tipo de movimiento.',
        'movimiento_cantidad.required' => 'Debe ingresar la cantidad.',
        'movimiento_cantidad.numeric' => 'La cantidad debe ser numérica.',
        'movimiento_cantidad.min' => 'La cantidad debe ser mayor que cero.',
        'movimiento_costo_unitario.required' => 'Debe ingresar el costo unitario.',
        'movimiento_costo_unitario.numeric' => 'El costo unitario debe ser numérico.',
        'movimiento_referencia.max' => 'La referencia no debe superar los 100 caracteres.',
        'movimiento_observacion.max' => 'La observación no debe superar los 500 caracteres.',
    ];

    public function updatedMovimientoTipo()
    {
        if ($this->movimiento_tipo === 'Entrada produccion') {
            $this->movimiento_costo_unitario = $this->producto->costo_unitario;
        }

        if ($this->movimiento_tipo === 'Entrada compra') {
            $this->movimiento_costo_unitario = $this->producto->costo_compra ?: $this->producto->costo_unitario;
        }

        if ($this->esSalida($this->movimiento_tipo)) {
            $this->movimiento_costo_unitario = 0;
        }
    }

    public function storeMovimiento()
    {
        $this->validate();

        $producto = Producto::findOrFail($this->producto_id);

        if (!$producto->maneja_inventario) {
            session()->flash('error', 'Este producto no maneja inventario.');
            return;
        }

        $cantidad = (float) $this->movimiento_cantidad;
        $costoUnitario = (float) $this->movimiento_costo_unitario;

        if ($this->movimiento_tipo === 'Entrada produccion') {
            $costoUnitario = 0;
        }

        if (
            $this->esEntrada($this->movimiento_tipo) &&
            $this->movimiento_tipo !== 'Entrada produccion' &&
            $costoUnitario <= 0
        ) {
            $this->addError('movimiento_costo_unitario', 'Para una entrada debe ingresar un costo unitario mayor que cero.');
            return;
        }

        if ($this->esSalida($this->movimiento_tipo)) {
            $stockDisponiblePeps = LoteProducto::where('producto_id', $producto->id)
                ->where('activo', true)
                ->where('cantidad_disponible', '>', 0)
                ->sum('cantidad_disponible');

            if ($cantidad > $stockDisponiblePeps) {
                $this->addError('movimiento_cantidad', 'No hay suficiente stock disponible en lotes PEPS.');
                return;
            }
        }

        try {
            DB::transaction(function () use ($producto, $cantidad, $costoUnitario) {

                if ($this->movimiento_tipo === 'Entrada produccion') {
                    $this->validarProduccionConReceta($producto, $cantidad);

                    $totalCostoProduccion = $this->consumirInsumosPorProduccion(
                        $producto,
                        $cantidad,
                        $this->movimiento_referencia,
                        $this->movimiento_observacion
                    );

                    $costoUnitarioProduccion = round($totalCostoProduccion / $cantidad, 4);

                    $this->registrarEntradaLote(
                        $producto,
                        $this->movimiento_tipo,
                        $cantidad,
                        $costoUnitarioProduccion,
                        $this->movimiento_referencia,
                        $this->movimiento_observacion
                    );

                    return;
                }

                if ($this->esEntrada($this->movimiento_tipo)) {
                    $this->registrarEntradaLote(
                        $producto,
                        $this->movimiento_tipo,
                        $cantidad,
                        $costoUnitario,
                        $this->movimiento_referencia,
                        $this->movimiento_observacion
                    );
                }

                if ($this->esSalida($this->movimiento_tipo)) {
                    $movimiento = MovimientoProducto::create([
                        'producto_id' => $producto->id,
                        'tipo_movimiento' => $this->movimiento_tipo,
                        'cantidad' => $cantidad,
                        'costo_unitario' => 0,
                        'total' => 0,
                        'referencia' => $this->movimiento_referencia,
                        'observacion' => $this->movimiento_observacion,
                    ]);

                    $totalSalida = $this->descontarPorPeps($producto, $cantidad, $movimiento->id);

                    $movimiento->update([
                        'costo_unitario' => round($totalSalida / $cantidad, 4),
                        'total' => round($totalSalida, 2),
                    ]);

                    $this->actualizarCostoActualPeps($producto);
                }
            });
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
            return;
        }

        $this->producto = Producto::findOrFail($this->producto_id);

        $this->resetMovimiento();

        session()->flash('message', 'Movimiento de producto registrado correctamente.');
    }

    private function registrarEntradaLote($producto, $tipoMovimiento, $cantidad, $costoUnitario, $referencia = null, $observacion = null)
    {
        $total = round($cantidad * $costoUnitario, 2);

        $movimiento = MovimientoProducto::create([
            'producto_id' => $producto->id,
            'tipo_movimiento' => $tipoMovimiento,
            'cantidad' => $cantidad,
            'costo_unitario' => $costoUnitario,
            'total' => $total,
            'referencia' => $referencia,
            'observacion' => $observacion,
        ]);

        $lote = LoteProducto::create([
            'producto_id' => $producto->id,
            'codigo_lote' => 'PROD-' . $producto->id . '-' . now()->format('YmdHis'),
            'fecha_entrada' => now()->format('Y-m-d'),
            'cantidad_inicial' => $cantidad,
            'cantidad_disponible' => $cantidad,
            'costo_unitario' => $costoUnitario,
            'total' => $total,
            'referencia' => $referencia,
            'observacion' => $observacion,
            'activo' => true,
        ]);

        MovimientoProductoLote::create([
            'movimiento_producto_id' => $movimiento->id,
            'lote_producto_id' => $lote->id,
            'cantidad' => $cantidad,
            'costo_unitario' => $costoUnitario,
            'total' => $total,
        ]);

        $this->actualizarCostoActualPeps($producto);
    }

    private function descontarPorPeps($producto, $cantidadSalida, $movimientoId)
    {
        $cantidadPendiente = $cantidadSalida;
        $totalSalida = 0;

        $lotes = LoteProducto::where('producto_id', $producto->id)
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

            MovimientoProductoLote::create([
                'movimiento_producto_id' => $movimientoId,
                'lote_producto_id' => $lote->id,
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

    private function actualizarCostoActualPeps($producto)
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

    private function esEntrada($tipoMovimiento)
    {
        return in_array($tipoMovimiento, [
            'Entrada compra',
            'Entrada produccion',
            'Entrada ajuste',
            'Devolucion',
        ]);
    }

    private function esSalida($tipoMovimiento)
    {
        return in_array($tipoMovimiento, [
            'Salida venta',
            'Salida daño',
            'Salida ajuste',
        ]);
    }

    private function resetMovimiento()
    {
        $this->movimiento_cantidad = 1;
        $this->movimiento_referencia = '';
        $this->movimiento_observacion = '';

        if ($this->producto->usa_receta) {
            $this->movimiento_tipo = 'Entrada produccion';
            $this->movimiento_costo_unitario = $this->producto->costo_unitario;
        } else {
            $this->movimiento_tipo = 'Entrada compra';
            $this->movimiento_costo_unitario = $this->producto->costo_compra ?: $this->producto->costo_unitario;
        }

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $movimientos = MovimientoProducto::with('detalleLotes.lote')
            ->where('producto_id', $this->producto_id)
            ->orderByDesc('id')
            ->paginate(10);

        $lotes = LoteProducto::where('producto_id', $this->producto_id)
            ->orderBy('fecha_entrada')
            ->orderBy('id')
            ->get();

        return view('livewire.productos.producto-movimientos', [
            'movimientos' => $movimientos,
            'lotes' => $lotes,
        ]);
    }

    private function validarProduccionConReceta($producto, $cantidadProducto)
    {
        if (!$producto->usa_receta) {
            throw new \Exception('Este producto no usa receta de insumos.');
        }

        $recetas = $producto->recetas()->with('insumo')->get();

        if ($recetas->isEmpty()) {
            throw new \Exception('Este producto no tiene insumos asignados en su receta.');
        }

        foreach ($recetas as $receta) {
            $cantidadNecesaria = (float) $receta->cantidad_por_unidad * $cantidadProducto;

            $stockDisponible = LoteInsumo::where('insumo_id', $receta->insumo_id)
                ->where('activo', true)
                ->where('cantidad_disponible', '>', 0)
                ->sum('cantidad_disponible');

            if ($cantidadNecesaria > $stockDisponible) {
                throw new \Exception(
                    'No hay suficiente stock del insumo: ' .
                        $receta->insumo->nombre .
                        '. Necesario: ' . number_format($cantidadNecesaria, 2) .
                        '. Disponible: ' . number_format($stockDisponible, 2)
                );
            }
        }
    }

    private function consumirInsumosPorProduccion($producto, $cantidadProducto, $referencia = null, $observacion = null)
    {
        $recetas = $producto->recetas()->with('insumo')->get();

        $totalCostoProduccion = 0;

        foreach ($recetas as $receta) {
            $insumo = $receta->insumo;
            $cantidadInsumo = (float) $receta->cantidad_por_unidad * $cantidadProducto;

            $movimiento = MovimientoInventario::create([
                'insumo_id' => $insumo->id,
                'tipo_movimiento' => 'Salida produccion',
                'cantidad' => $cantidadInsumo,
                'costo_unitario' => 0,
                'total' => 0,
                'referencia' => $referencia ?: 'Producción ' . $producto->codigo,
                'observacion' => 'Consumo por producción de ' . $producto->nombre . '. ' . ($observacion ?? ''),
            ]);

            $totalSalida = $this->descontarInsumoPorPeps($insumo, $cantidadInsumo, $movimiento->id);

            $movimiento->update([
                'costo_unitario' => round($totalSalida / $cantidadInsumo, 4),
                'total' => round($totalSalida, 2),
            ]);

            $this->actualizarCostoActualPepsInsumo($insumo);

            $totalCostoProduccion += $totalSalida;
        }

        return round($totalCostoProduccion, 2);
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
}
