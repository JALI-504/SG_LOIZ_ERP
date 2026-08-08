<?php

namespace App\Http\Livewire\ActivosFijos;

use App\Models\ActivoFijo;
use App\Models\CategoriaActivo;
use Livewire\Component;
use Livewire\WithPagination;

class ReporteActivoFijoIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $categoria_id = 'todos';
    public $estado = 'todos';
    public $fecha_desde;
    public $fecha_hasta;
    public $perPage = 10;

    public function mount()
    {
        if (!auth()->user()->can('ver activos fijos')) {
            abort(403, 'No tiene permiso para ver reportes de activos fijos.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoriaId()
    {
        $this->resetPage();
    }

    public function updatingEstado()
    {
        $this->resetPage();
    }

    public function updatingFechaDesde()
    {
        $this->resetPage();
    }

    public function updatingFechaHasta()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function limpiarFiltros()
    {
        $this->search = '';
        $this->categoria_id = 'todos';
        $this->estado = 'todos';
        $this->fecha_desde = null;
        $this->fecha_hasta = null;
        $this->perPage = 10;

        $this->resetPage();
    }

    private function queryActivos()
    {
        return ActivoFijo::with('categoriaActivo')
            ->when($this->search, function ($query) {
                $search = '%' . $this->search . '%';

                $query->where(function ($q) use ($search) {
                    $q->where('codigo', 'like', $search)
                        ->orWhere('nombre', 'like', $search)
                        ->orWhere('descripcion', 'like', $search)
                        ->orWhere('numero_serie', 'like', $search)
                        ->orWhere('marca', 'like', $search)
                        ->orWhere('modelo', 'like', $search)
                        ->orWhere('responsable', 'like', $search)
                        ->orWhere('ubicacion', 'like', $search)
                        ->orWhere('proveedor', 'like', $search)
                        ->orWhere('documento_compra', 'like', $search)
                        ->orWhere('documento_baja', 'like', $search)
                        ->orWhere('motivo_baja', 'like', $search);
                });
            })
            ->when($this->categoria_id !== 'todos', function ($query) {
                $query->where('categoria_activo_id', $this->categoria_id);
            })
            ->when($this->estado !== 'todos', function ($query) {
                $query->where('estado', $this->estado);
            })
            ->when($this->fecha_desde, function ($query) {
                $query->whereDate('fecha_compra', '>=', $this->fecha_desde);
            })
            ->when($this->fecha_hasta, function ($query) {
                $query->whereDate('fecha_compra', '<=', $this->fecha_hasta);
            });
    }

    public function render()
    {
        $query = $this->queryActivos();

        $totalActivos = (clone $query)->count();

        $totalActivosVigentes = (clone $query)
            ->whereNotIn('estado', ['Dado de baja', 'Vendido'])
            ->count();

        $totalBajas = (clone $query)
            ->where('estado', 'Dado de baja')
            ->count();

        $totalVendidos = (clone $query)
            ->where('estado', 'Vendido')
            ->count();

        $totalRobadosExtraviados = (clone $query)
            ->whereIn('tipo_baja', ['Robado', 'Extraviado'])
            ->count();

        $valorCompraTotal = (clone $query)
            ->whereNotIn('estado', ['Dado de baja', 'Vendido'])
            ->sum('valor_compra');

        $depreciacionAcumuladaTotal = (clone $query)
            ->whereNotIn('estado', ['Dado de baja', 'Vendido'])
            ->sum('depreciacion_acumulada');

        $valorLibrosTotal = (clone $query)
            ->whereNotIn('estado', ['Dado de baja', 'Vendido'])
            ->sum('valor_en_libros');

        $valorRecuperadoTotal = (clone $query)
            ->whereIn('estado', ['Dado de baja', 'Vendido'])
            ->sum('valor_recuperado');

        $activos = $query
            ->orderBy('categoria_activo_id')
            ->orderBy('codigo')
            ->paginate($this->perPage);

        $categorias = CategoriaActivo::orderBy('nombre')->get();

        $resumenCategorias = ActivoFijo::selectRaw('
                categoria_activo_id,
                COUNT(*) as cantidad,
                SUM(valor_compra) as valor_compra,
                SUM(depreciacion_acumulada) as depreciacion_acumulada,
                SUM(valor_en_libros) as valor_en_libros
            ')
            ->with('categoriaActivo')
            ->whereNotIn('estado', ['Dado de baja', 'Vendido'])
            ->groupBy('categoria_activo_id')
            ->orderBy('categoria_activo_id')
            ->get();

        return view('livewire.activos-fijos.reporte-activo-fijo-index', [
            'activos' => $activos,
            'categorias' => $categorias,
            'resumenCategorias' => $resumenCategorias,
            'totalActivos' => $totalActivos,
            'totalActivosVigentes' => $totalActivosVigentes,
            'totalBajas' => $totalBajas,
            'totalVendidos' => $totalVendidos,
            'totalRobadosExtraviados' => $totalRobadosExtraviados,
            'valorCompraTotal' => $valorCompraTotal,
            'depreciacionAcumuladaTotal' => $depreciacionAcumuladaTotal,
            'valorLibrosTotal' => $valorLibrosTotal,
            'valorRecuperadoTotal' => $valorRecuperadoTotal,
        ]);
    }
}
