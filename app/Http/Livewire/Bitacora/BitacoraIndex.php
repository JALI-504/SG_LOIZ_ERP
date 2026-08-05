<?php

namespace App\Http\Livewire\Bitacora;

use App\Models\BitacoraSistema;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class BitacoraIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 15;

    public $fechaDesde;
    public $fechaHasta;
    public $usuario_id = 'todos';
    public $modulo = 'todos';
    public $accion = 'todos';

    public $mostrarModalDetalle = false;
    public $bitacoraDetalle = null;

    public function mount()
    {
        if (!auth()->user()->can('ver bitacora')) {
            abort(403, 'No tiene permiso para ver la bitácora.');
        }

        $this->fechaDesde = now()->subDays(7)->format('Y-m-d');
        $this->fechaHasta = now()->format('Y-m-d');
    }

    public function updatingSearch()
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

    public function updatingUsuarioId()
    {
        $this->resetPage();
    }

    public function updatingModulo()
    {
        $this->resetPage();
    }

    public function updatingAccion()
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
        $this->fechaDesde = now()->subDays(7)->format('Y-m-d');
        $this->fechaHasta = now()->format('Y-m-d');
        $this->usuario_id = 'todos';
        $this->modulo = 'todos';
        $this->accion = 'todos';

        $this->resetPage();
    }

    public function verDetalle($id)
    {
        if (!auth()->user()->can('ver bitacora')) {
            abort(403, 'No tiene permiso para ver la bitácora.');
        }

        $this->bitacoraDetalle = BitacoraSistema::with('usuario')->findOrFail($id);
        $this->mostrarModalDetalle = true;
    }

    public function cerrarModalDetalle()
    {
        $this->mostrarModalDetalle = false;
        $this->bitacoraDetalle = null;
    }

    public function render()
    {
        if (!auth()->user()->can('ver bitacora')) {
            abort(403, 'No tiene permiso para ver la bitácora.');
        }

        $usuarios = User::orderBy('name')->get();

        $modulos = BitacoraSistema::query()
            ->select('modulo')
            ->distinct()
            ->orderBy('modulo')
            ->pluck('modulo');

        $acciones = BitacoraSistema::query()
            ->select('accion')
            ->distinct()
            ->orderBy('accion')
            ->pluck('accion');

        $bitacoras = BitacoraSistema::with('usuario')
            ->where(function ($query) {
                $query->where('modulo', 'like', '%' . $this->search . '%')
                    ->orWhere('accion', 'like', '%' . $this->search . '%')
                    ->orWhere('descripcion', 'like', '%' . $this->search . '%')
                    ->orWhere('modelo', 'like', '%' . $this->search . '%')
                    ->orWhere('ip', 'like', '%' . $this->search . '%');
            })
            ->when($this->fechaDesde, function ($query) {
                $query->whereDate('fecha', '>=', $this->fechaDesde);
            })
            ->when($this->fechaHasta, function ($query) {
                $query->whereDate('fecha', '<=', $this->fechaHasta);
            })
            ->when($this->usuario_id !== 'todos', function ($query) {
                $query->where('user_id', $this->usuario_id);
            })
            ->when($this->modulo !== 'todos', function ($query) {
                $query->where('modulo', $this->modulo);
            })
            ->when($this->accion !== 'todos', function ($query) {
                $query->where('accion', $this->accion);
            })
            ->orderByDesc('fecha')
            ->orderByDesc('hora')
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.bitacora.bitacora-index', [
            'bitacoras' => $bitacoras,
            'usuarios' => $usuarios,
            'modulos' => $modulos,
            'acciones' => $acciones,
        ]);
    }
}
