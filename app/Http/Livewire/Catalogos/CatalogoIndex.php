<?php

namespace App\Http\Livewire\Catalogos;

use App\Models\Catalogo;
use App\Models\TipoCatalogo;
use App\Models\BitacoraSistema;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

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

    private function autorizarVerConfiguracion()
    {
        if (
            !auth()->user()->can('ver configuracion') &&
            !auth()->user()->can('editar configuracion')
        ) {
            abort(403, 'No tiene permiso para ver catálogos.');
        }
    }

    private function autorizarEditarConfiguracion()
    {
        if (!auth()->user()->can('editar configuracion')) {
            abort(403, 'No tiene permiso para editar catálogos.');
        }
    }

    public function mount()
    {
        $this->autorizarVerConfiguracion();

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
        $this->autorizarEditarConfiguracion();

        $this->resetInput();

        $this->modalTitle = 'Nuevo catálogo';

        $this->dispatchBrowserEvent('open-catalogo-modal');
    }

    public function store()
    {
        $this->autorizarEditarConfiguracion();

        $this->validate();

        $catalogo = Catalogo::create([
            'tipo' => $this->tipo,
            'nombre' => trim($this->nombre),
            'descripcion' => $this->descripcion,
            'orden' => $this->orden,
            'activo' => $this->activo,
        ]);

        BitacoraSistema::registrar(
            'Catálogos',
            'Registrar',
            'Registró la opción de catálogo ' . $catalogo->nombre . ' en el tipo ' . $catalogo->tipo . '.',
            Catalogo::class,
            $catalogo->id,
            null,
            $catalogo->toArray()
        );

        $this->resetInput();

        $this->dispatchBrowserEvent('close-catalogo-modal');

        session()->flash('message', 'Catálogo registrado correctamente.');
    }

    public function edit($id)
    {
        $this->autorizarEditarConfiguracion();

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
        $this->autorizarEditarConfiguracion();

        $this->validate();

        $catalogo = Catalogo::findOrFail($this->catalogo_id);

        $datosAnteriores = $catalogo->toArray();

        $catalogo->update([
            'tipo' => $this->tipo,
            'nombre' => trim($this->nombre),
            'descripcion' => $this->descripcion,
            'orden' => $this->orden,
            'activo' => $this->activo,
        ]);

        BitacoraSistema::registrar(
            'Catálogos',
            'Actualizar',
            'Actualizó la opción de catálogo ' . $catalogo->fresh()->nombre . ' del tipo ' . $catalogo->fresh()->tipo . '.',
            Catalogo::class,
            $catalogo->id,
            $datosAnteriores,
            $catalogo->fresh()->toArray()
        );

        $this->resetInput();

        $this->dispatchBrowserEvent('close-catalogo-modal');

        session()->flash('message', 'Catálogo actualizado correctamente.');
    }

    public function cambiarEstado($id)
    {
        $this->autorizarEditarConfiguracion();

        $catalogo = Catalogo::findOrFail($id);

        $datosAnteriores = $catalogo->toArray();

        $estadoAnterior = $catalogo->activo ? 'Activo' : 'Inactivo';

        $catalogo->update([
            'activo' => !$catalogo->activo,
        ]);

        $catalogoActualizado = $catalogo->fresh();

        $estadoNuevo = $catalogoActualizado->activo ? 'Activo' : 'Inactivo';

        BitacoraSistema::registrar(
            'Catálogos',
            'Actualizar',
            'Cambió el estado de la opción de catálogo ' . $catalogoActualizado->nombre . ' de ' . $estadoAnterior . ' a ' . $estadoNuevo . '.',
            Catalogo::class,
            $catalogoActualizado->id,
            $datosAnteriores,
            $catalogoActualizado->toArray()
        );

        session()->flash('message', 'Estado del catálogo actualizado correctamente.');
    }

    private function resetInput()
    {
        $this->catalogo_id = null;

        $this->tipo = array_key_first($this->tiposCatalogo) ?? 'categoria_insumo';
        $this->nombre = '';
        $this->descripcion = '';
        $this->orden = 0;
        $this->activo = true;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $this->autorizarEditarConfiguracion();

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
