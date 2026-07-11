<?php

namespace App\Http\Livewire\Gastos;

use App\Models\Catalogo;
use App\Models\Gasto;
use Livewire\Component;
use Livewire\WithPagination;

class GastoIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $fechaDesde;
    public $fechaHasta;
    public $filtroCategoria = 'todos';
    public $filtroEstado = 'todos';
    public $perPage = 10;

    public $gasto_id;
    public $fecha;
    public $categoria;
    public $descripcion;
    public $monto;
    public $metodo_pago = 'Efectivo';
    public $referencia;
    public $proveedor;
    public $observacion;
    public $estado = 'Registrado';

    public $mostrarModal = false;

    public $categorias = [];
    public $metodosPago = [];

    public function mount()
    {
        $this->categorias = Catalogo::opciones('categoria_gasto')
            ->pluck('nombre')
            ->toArray();

        $this->metodosPago = Catalogo::opciones('metodo_pago')
            ->pluck('nombre')
            ->toArray();

        $this->categoria = $this->categorias[0] ?? 'Otros gastos';
        $this->metodo_pago = $this->metodosPago[0] ?? 'Efectivo';
        $this->fecha = now()->format('Y-m-d');
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

    public function updatingFiltroCategoria()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    protected function rules()
    {
        return [
            'fecha' => 'required|date',
            'categoria' => 'required|max:100',
            'descripcion' => 'required|min:3|max:200',
            'monto' => 'required|numeric|min:0.01',
            'metodo_pago' => 'required|max:50',
            'referencia' => 'nullable|max:100',
            'proveedor' => 'nullable|max:150',
            'observacion' => 'nullable|max:500',
            'estado' => 'required|max:30',
        ];
    }

    private function queryGastos()
    {
        return Gasto::query()
            ->when($this->search, function ($query) {
                $search = '%' . $this->search . '%';

                $query->where(function ($q) use ($search) {
                    $q->where('descripcion', 'like', $search)
                        ->orWhere('categoria', 'like', $search)
                        ->orWhere('metodo_pago', 'like', $search)
                        ->orWhere('referencia', 'like', $search)
                        ->orWhere('proveedor', 'like', $search)
                        ->orWhere('observacion', 'like', $search);
                });
            })
            ->when($this->fechaDesde, function ($query) {
                $query->whereDate('fecha', '>=', $this->fechaDesde);
            })
            ->when($this->fechaHasta, function ($query) {
                $query->whereDate('fecha', '<=', $this->fechaHasta);
            })
            ->when($this->filtroCategoria !== 'todos', function ($query) {
                $query->where('categoria', $this->filtroCategoria);
            })
            ->when($this->filtroEstado !== 'todos', function ($query) {
                $query->where('estado', $this->filtroEstado);
            });
    }

    public function crear()
    {
        $this->resetFormulario();

        $this->gasto_id = null;
        $this->estado = 'Registrado';
        $this->mostrarModal = true;
    }

    public function cerrarModal()
    {
        $this->resetFormulario();

        $this->mostrarModal = false;
    }

    public function store()
    {
        $this->validate();

        Gasto::create([
            'fecha' => $this->fecha,
            'categoria' => $this->categoria,
            'descripcion' => $this->descripcion,
            'monto' => $this->monto,
            'metodo_pago' => $this->metodo_pago,
            'referencia' => $this->referencia,
            'proveedor' => $this->proveedor,
            'observacion' => $this->observacion,
            'estado' => 'Registrado',
        ]);

        $this->resetFormulario();

        $this->mostrarModal = false;

        session()->flash('message', 'Gasto registrado correctamente.');
    }

    public function editar($gastoId)
    {
        $gasto = Gasto::findOrFail($gastoId);

        $this->gasto_id = $gasto->id;
        $this->fecha = $gasto->fecha;
        $this->categoria = $gasto->categoria;
        $this->descripcion = $gasto->descripcion;
        $this->monto = $gasto->monto;
        $this->metodo_pago = $gasto->metodo_pago;
        $this->referencia = $gasto->referencia;
        $this->proveedor = $gasto->proveedor;
        $this->observacion = $gasto->observacion;
        $this->estado = $gasto->estado;

        $this->mostrarModal = true;
    }

    public function update()
    {
        $this->validate();

        $gasto = Gasto::findOrFail($this->gasto_id);

        if ($gasto->estado === 'Anulado') {
            session()->flash('error', 'No se puede modificar un gasto anulado.');
            return;
        }

        $gasto->update([
            'fecha' => $this->fecha,
            'categoria' => $this->categoria,
            'descripcion' => $this->descripcion,
            'monto' => $this->monto,
            'metodo_pago' => $this->metodo_pago,
            'referencia' => $this->referencia,
            'proveedor' => $this->proveedor,
            'observacion' => $this->observacion,
            'estado' => 'Registrado',
        ]);

        $this->resetFormulario();

        $this->mostrarModal = false;

        session()->flash('message', 'Gasto actualizado correctamente.');
    }

    public function anular($gastoId)
    {
        $gasto = Gasto::findOrFail($gastoId);

        if ($gasto->estado === 'Anulado') {
            session()->flash('error', 'Este gasto ya está anulado.');
            return;
        }

        $observacionAnterior = $gasto->observacion ? $gasto->observacion . "\n" : '';

        $gasto->update([
            'estado' => 'Anulado',
            'observacion' => $observacionAnterior . 'Gasto anulado el ' . now()->format('d/m/Y H:i'),
        ]);

        $this->resetFormulario();

        session()->flash('message', 'Gasto anulado correctamente.');
    }

    public function reactivar($gastoId)
    {
        $gasto = Gasto::findOrFail($gastoId);

        if ($gasto->estado === 'Registrado') {
            session()->flash('error', 'Este gasto ya está registrado.');
            return;
        }

        $observacionAnterior = $gasto->observacion ? $gasto->observacion . "\n" : '';

        $gasto->update([
            'estado' => 'Registrado',
            'observacion' => $observacionAnterior . 'Gasto reactivado el ' . now()->format('d/m/Y H:i'),
        ]);

        $this->resetFormulario();

        session()->flash('message', 'Gasto reactivado correctamente.');
    }

    public function limpiarFiltros()
    {
        $this->search = '';
        $this->fechaDesde = null;
        $this->fechaHasta = null;
        $this->filtroCategoria = 'todos';
        $this->filtroEstado = 'todos';
        $this->perPage = 10;

        $this->resetPage();
    }

    private function resetFormulario()
    {
        $this->gasto_id = null;
        $this->fecha = now()->format('Y-m-d');
        $this->categoria = $this->categorias[0] ?? 'Otros gastos';
        $this->descripcion = null;
        $this->monto = null;
        $this->metodo_pago = $this->metodosPago[0] ?? 'Efectivo';
        $this->referencia = null;
        $this->proveedor = null;
        $this->observacion = null;
        $this->estado = 'Registrado';

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $query = $this->queryGastos();

        $totalRegistros = (clone $query)->count();

        $totalGastos = (clone $query)
            ->where('estado', 'Registrado')
            ->sum('monto');

        $totalAnulados = (clone $query)
            ->where('estado', 'Anulado')
            ->sum('monto');

        $cantidadAnulados = (clone $query)
            ->where('estado', 'Anulado')
            ->count();

        $gastos = $query
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.gastos.gasto-index', [
            'gastos' => $gastos,
            'totalRegistros' => $totalRegistros,
            'totalGastos' => $totalGastos,
            'totalAnulados' => $totalAnulados,
            'cantidadAnulados' => $cantidadAnulados,
        ]);
    }

    public function guardar()
    {
        if ($this->gasto_id) {
            return $this->update();
        }

        return $this->store();
    }
}
