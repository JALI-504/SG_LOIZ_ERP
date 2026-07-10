<?php

namespace App\Http\Livewire\Insumos;

use App\Models\Insumo;
use App\Models\LoteInsumo;
use App\Models\MovimientoInventario;
use Livewire\Component;
use Livewire\WithPagination;

class InsumoMovimientos extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $insumo;
    public $insumo_id;

    public $filtroTipo = 'todos';
    public $perPage = 10;

    public $tiposEntrada = [
        'Entrada compra',
        'Entrada ajuste',
        'Devolucion',
    ];

    public $tiposSalida = [
        'Salida venta',
        'Salida produccion',
        'Salida daño',
        'Salida prueba',
        'Salida ajuste',
    ];

    public function mount($insumoId)
    {
        $this->insumo = Insumo::findOrFail($insumoId);
        $this->insumo_id = $this->insumo->id;
    }

    public function updatingFiltroTipo()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function render()
    {
        $movimientos = MovimientoInventario::with('detalleLotes.lote')
            ->where('insumo_id', $this->insumo_id)
            ->when($this->filtroTipo === 'entradas', function ($query) {
                $query->whereIn('tipo_movimiento', $this->tiposEntrada);
            })
            ->when($this->filtroTipo === 'salidas', function ($query) {
                $query->whereIn('tipo_movimiento', $this->tiposSalida);
            })
            ->orderByDesc('id')
            ->paginate($this->perPage);

        $lotes = LoteInsumo::where('insumo_id', $this->insumo_id)
            ->orderBy('fecha_entrada')
            ->orderBy('id')
            ->get();

        $totalDisponibleLotes = LoteInsumo::where('insumo_id', $this->insumo_id)
            ->where('activo', true)
            ->sum('cantidad_disponible');

        $valorInventario = LoteInsumo::where('insumo_id', $this->insumo_id)
            ->where('activo', true)
            ->sum(\DB::raw('cantidad_disponible * costo_unitario'));

        return view('livewire.insumos.insumo-movimientos', [
            'movimientos' => $movimientos,
            'lotes' => $lotes,
            'totalDisponibleLotes' => $totalDisponibleLotes,
            'valorInventario' => $valorInventario,
        ]);
    }
}
