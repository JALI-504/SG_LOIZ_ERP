<?php

namespace App\Http\Livewire\Catalogos;

use App\Models\TipoCatalogo;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class TipoCatalogoIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 10;
    public $filtroEstado = 'activos';

    public $tipo_catalogo_id;

    public $codigo;
    public $nombre;
    public $descripcion;
    public $orden = 0;
    public $activo = true;

    public $modalTitle = 'Nuevo tipo de catálogo';

    protected function rules()
    {
        $rules = [
            'nombre' => 'required|min:2|max:100',
            'descripcion' => 'nullable|max:200',
            'orden' => 'required|integer|min:0',
            'activo' => 'boolean',
        ];

        if (!$this->tipo_catalogo_id) {
            $rules['codigo'] = [
                'required',
                'min:2',
                'max:50',
                'regex:/^[a-z0-9_]+$/',
                Rule::unique('tipos_catalogo', 'codigo'),
            ];
        }

        return $rules;
    }

    protected $messages = [
        'codigo.required' => 'El código es obligatorio.',
        'codigo.unique' => 'Este código ya está registrado.',
        'codigo.regex' => 'El código solo puede llevar minúsculas, números y guion bajo. Ejemplo: marca_insumo.',
        'nombre.required' => 'El nombre es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 2 caracteres.',
        'orden.required' => 'El orden es obligatorio.',
        'orden.integer' => 'El orden debe ser un número entero.',
    ];

    public function updatingSearch()
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

        $this->modalTitle = 'Nuevo tipo de catálogo';

        $this->dispatchBrowserEvent('open-tipo-catalogo-modal');
    }

    public function store()
    {
        $this->codigo = strtolower(trim($this->codigo));

        $this->validate();

        TipoCatalogo::create([
            'codigo' => $this->codigo,
            'nombre' => trim($this->nombre),
            'descripcion' => $this->descripcion,
            'orden' => $this->orden,
            'activo' => $this->activo,
        ]);

        $this->resetInput();

        $this->dispatchBrowserEvent('close-tipo-catalogo-modal');

        session()->flash('message', 'Tipo de catálogo registrado correctamente.');
    }

    public function edit($id)
    {
        $tipoCatalogo = TipoCatalogo::findOrFail($id);

        $this->tipo_catalogo_id = $tipoCatalogo->id;

        $this->codigo = $tipoCatalogo->codigo;
        $this->nombre = $tipoCatalogo->nombre;
        $this->descripcion = $tipoCatalogo->descripcion;
        $this->orden = $tipoCatalogo->orden;
        $this->activo = $tipoCatalogo->activo;

        $this->modalTitle = 'Editar tipo de catálogo';

        $this->dispatchBrowserEvent('open-tipo-catalogo-modal');
    }

    public function update()
    {
        $this->validate();

        $tipoCatalogo = TipoCatalogo::findOrFail($this->tipo_catalogo_id);

        $tipoCatalogo->update([
            // El código no se actualiza para no romper catálogos ya asociados.
            'nombre' => trim($this->nombre),
            'descripcion' => $this->descripcion,
            'orden' => $this->orden,
            'activo' => $this->activo,
        ]);

        $this->resetInput();

        $this->dispatchBrowserEvent('close-tipo-catalogo-modal');

        session()->flash('message', 'Tipo de catálogo actualizado correctamente.');
    }

    public function cambiarEstado($id)
    {
        $tipoCatalogo = TipoCatalogo::findOrFail($id);

        $tipoCatalogo->update([
            'activo' => !$tipoCatalogo->activo,
        ]);

        session()->flash('message', 'Estado del tipo de catálogo actualizado correctamente.');
    }

    private function resetInput()
    {
        $this->tipo_catalogo_id = null;

        $this->codigo = '';
        $this->nombre = '';
        $this->descripcion = '';
        $this->orden = 0;
        $this->activo = true;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $tiposCatalogo = TipoCatalogo::query()
            ->withCount('catalogos')
            ->where(function ($query) {
                $query->where('codigo', 'like', '%' . $this->search . '%')
                    ->orWhere('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('descripcion', 'like', '%' . $this->search . '%');
            })
            ->when($this->filtroEstado === 'activos', function ($query) {
                $query->where('activo', true);
            })
            ->when($this->filtroEstado === 'inactivos', function ($query) {
                $query->where('activo', false);
            })
            ->orderBy('orden')
            ->orderBy('nombre')
            ->paginate($this->perPage);

        return view('livewire.catalogos.tipo-catalogo-index', [
            'tiposCatalogo' => $tiposCatalogo,
        ]);
    }
}
