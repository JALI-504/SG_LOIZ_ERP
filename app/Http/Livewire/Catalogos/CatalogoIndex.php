<?php

namespace App\Http\Livewire\Catalogos;

use App\Models\Catalogo;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\TipoCatalogo;

class CatalogoIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 10;

    public $catalogo_id;

    public $tipo = 'categoria_insumo';
    public $nombre;
    public $descripcion;
    public $orden = 0;
    public $activo = true;

    public $filtroTipo = 'todos';
    public $filtroEstado = 'activos';

    public $modalTitle = 'Nuevo catálogo';

    public $tiposCatalogo = [];

    public function mount()
    {
        $this->cargarTiposCatalogo();
    }

    private function cargarTiposCatalogo()
    {
        $this->tiposCatalogo = TipoCatalogo::opciones()
            ->pluck('nombre', 'codigo')
            ->toArray();

        if (count($this->tiposCatalogo) > 0) {
            $this->tipo = array_key_first($this->tiposCatalogo);
        } else {
            $this->tipo = 'categoria_insumo';
        }
    }

    protected function rules()
    {
        return [
            'tipo' => 'required|max:50',
            'nombre' => [
                'required',
                'min:2',
                'max:100',
                Rule::unique('catalogos', 'nombre')
                    ->where(function ($query) {
                        return $query->where('tipo', $this->tipo);
                    })
                    ->ignore($this->catalogo_id),
            ],
            'descripcion' => 'nullable|max:200',
            'orden' => 'required|integer|min:0',
            'activo' => 'boolean',
        ];
    }

    protected $messages = [
        'tipo.required' => 'Debe seleccionar el tipo de catálogo.',
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.unique' => 'Este nombre ya existe en este tipo de catálogo.',
        'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
        'orden.required' => 'El orden es obligatorio.',
        'orden.integer' => 'El orden debe ser un número entero.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroTipo()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetInput();

        $this->modalTitle = 'Nuevo catálogo';

        $this->dispatchBrowserEvent('open-catalogo-modal');
    }

    public function store()
    {
        $this->validate();

        Catalogo::create([
            'tipo' => $this->tipo,
            'nombre' => trim($this->nombre),
            'descripcion' => $this->descripcion,
            'orden' => $this->orden,
            'activo' => $this->activo,
        ]);

        $this->resetInput();

        $this->dispatchBrowserEvent('close-catalogo-modal');

        session()->flash('message', 'Catálogo registrado correctamente.');
    }

    public function edit($id)
    {
        $catalogo = Catalogo::findOrFail($id);

        $this->catalogo_id = $catalogo->id;
        $this->tipo = $catalogo->tipo;
        $this->nombre = $catalogo->nombre;
        $this->descripcion = $catalogo->descripcion;
        $this->orden = $catalogo->orden;
        $this->activo = $catalogo->activo;

        $this->modalTitle = 'Editar catálogo';

        $this->dispatchBrowserEvent('open-catalogo-modal');
    }

    public function update()
    {
        $this->validate();

        $catalogo = Catalogo::findOrFail($this->catalogo_id);

        $catalogo->update([
            'tipo' => $this->tipo,
            'nombre' => trim($this->nombre),
            'descripcion' => $this->descripcion,
            'orden' => $this->orden,
            'activo' => $this->activo,
        ]);

        $this->resetInput();

        $this->dispatchBrowserEvent('close-catalogo-modal');

        session()->flash('message', 'Catálogo actualizado correctamente.');
    }

    public function cambiarEstado($id)
    {
        $catalogo = Catalogo::findOrFail($id);

        $catalogo->update([
            'activo' => !$catalogo->activo,
        ]);

        session()->flash('message', 'Estado del catálogo actualizado correctamente.');
    }

    private function resetInput()
    {
        $this->catalogo_id = null;

        $this->tipo = array_key_first($this->tiposCatalogo) ?? 'categoria_insumo';        $this->nombre = '';
        $this->descripcion = '';
        $this->orden = 0;
        $this->activo = true;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $catalogos = Catalogo::query()
            ->where(function ($query) {
                $query->where('tipo', 'like', '%' . $this->search . '%')
                    ->orWhere('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('descripcion', 'like', '%' . $this->search . '%');
            })
            ->when($this->filtroTipo !== 'todos', function ($query) {
                $query->where('tipo', $this->filtroTipo);
            })
            ->when($this->filtroEstado === 'activos', function ($query) {
                $query->where('activo', true);
            })
            ->when($this->filtroEstado === 'inactivos', function ($query) {
                $query->where('activo', false);
            })
            ->orderBy('tipo')
            ->orderBy('orden')
            ->orderBy('nombre')
            ->paginate($this->perPage);

        return view('livewire.catalogos.catalogo-index', [
            'catalogos' => $catalogos,
        ]);
    }
}
