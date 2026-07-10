<?php

namespace App\Http\Livewire\Servicios;

use App\Models\Servicio;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Catalogo;

class ServicioIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 10;
    public $filtroEstado = 'activos';
    public $filtroTipo = 'todos';

    public $servicio_id;

    public $codigo;
    public $nombre;
    public $tipo_servicio = 'Impresion';
    public $tamano_papel = 'Carta';
    public $color = 'Blanco y negro';
    public $caras = 'Una cara';
    public $unidad_cobro = 'Pagina';
    public $costo_unitario = 0;
    public $precio_unitario = 0;
    public $descripcion;
    public $activo = true;

    public $modalTitle = 'Nuevo servicio';

    public $tiposServicio = [];
    public $tamanosPapel = [];
    public $colores = [];
    public $carasOpciones = [];
    public $unidadesCobro = [];

    public function mount()
    {
        $this->tiposServicio = Catalogo::opciones('tipo_servicio')->pluck('nombre')->toArray();
        $this->tamanosPapel = Catalogo::opciones('tamano_papel')->pluck('nombre')->toArray();
        $this->colores = Catalogo::opciones('color_servicio')->pluck('nombre')->toArray();
        $this->carasOpciones = Catalogo::opciones('caras_servicio')->pluck('nombre')->toArray();
        $this->unidadesCobro = Catalogo::opciones('unidad_cobro')->pluck('nombre')->toArray();

        $this->tipo_servicio = $this->tiposServicio[0] ?? 'Impresion';
        $this->tamano_papel = $this->tamanosPapel[0] ?? 'Carta';
        $this->color = $this->colores[0] ?? 'Blanco y negro';
        $this->caras = $this->carasOpciones[0] ?? 'Una cara';
        $this->unidad_cobro = $this->unidadesCobro[0] ?? 'Pagina';
    }

    protected function rules()
    {
        return [
            'codigo' => [
                'required',
                'max:20',
                Rule::unique('servicios', 'codigo')->ignore($this->servicio_id),
            ],
            'nombre' => 'required|min:3|max:150',
            'tipo_servicio' => 'required|max:50',
            'tamano_papel' => 'required|max:50',
            'color' => 'required|max:50',
            'caras' => 'required|max:50',
            'unidad_cobro' => 'required|max:50',
            'costo_unitario' => 'required|numeric|min:0',
            'precio_unitario' => 'required|numeric|min:0',
            'descripcion' => 'nullable|max:500',
            'activo' => 'boolean',
        ];
    }

    protected $messages = [
        'codigo.required' => 'El código del servicio es obligatorio.',
        'codigo.unique' => 'Este código ya está registrado.',
        'nombre.required' => 'El nombre del servicio es obligatorio.',
        'nombre.min' => 'El nombre debe tener al menos 3 caracteres.',
        'costo_unitario.required' => 'El costo unitario es obligatorio.',
        'costo_unitario.numeric' => 'El costo unitario debe ser numérico.',
        'precio_unitario.required' => 'El precio unitario es obligatorio.',
        'precio_unitario.numeric' => 'El precio unitario debe ser numérico.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function updatingFiltroTipo()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->resetInput();

        $this->modalTitle = 'Nuevo servicio';

        $this->dispatchBrowserEvent('open-servicio-modal');
    }

    public function store()
    {
        $this->validate();

        Servicio::create([
            'codigo' => strtoupper(trim($this->codigo)),
            'nombre' => trim($this->nombre),
            'tipo_servicio' => $this->tipo_servicio,
            'tamano_papel' => $this->tamano_papel,
            'color' => $this->color,
            'caras' => $this->caras,
            'unidad_cobro' => $this->unidad_cobro,
            'costo_unitario' => $this->costo_unitario,
            'precio_unitario' => $this->precio_unitario,
            'descripcion' => $this->descripcion,
            'activo' => $this->activo,
        ]);

        $this->resetInput();

        $this->dispatchBrowserEvent('close-servicio-modal');

        session()->flash('message', 'Servicio registrado correctamente.');
    }

    public function edit($id)
    {
        $servicio = Servicio::findOrFail($id);

        $this->servicio_id = $servicio->id;

        $this->codigo = $servicio->codigo;
        $this->nombre = $servicio->nombre;
        $this->tipo_servicio = $servicio->tipo_servicio;
        $this->tamano_papel = $servicio->tamano_papel;
        $this->color = $servicio->color;
        $this->caras = $servicio->caras;
        $this->unidad_cobro = $servicio->unidad_cobro;
        $this->costo_unitario = $servicio->costo_unitario;
        $this->precio_unitario = $servicio->precio_unitario;
        $this->descripcion = $servicio->descripcion;
        $this->activo = $servicio->activo;

        $this->modalTitle = 'Editar servicio';

        $this->dispatchBrowserEvent('open-servicio-modal');
    }

    public function update()
    {
        $this->validate();

        $servicio = Servicio::findOrFail($this->servicio_id);

        $servicio->update([
            'codigo' => strtoupper(trim($this->codigo)),
            'nombre' => trim($this->nombre),
            'tipo_servicio' => $this->tipo_servicio,
            'tamano_papel' => $this->tamano_papel,
            'color' => $this->color,
            'caras' => $this->caras,
            'unidad_cobro' => $this->unidad_cobro,
            'costo_unitario' => $this->costo_unitario,
            'precio_unitario' => $this->precio_unitario,
            'descripcion' => $this->descripcion,
            'activo' => $this->activo,
        ]);

        $this->resetInput();

        $this->dispatchBrowserEvent('close-servicio-modal');

        session()->flash('message', 'Servicio actualizado correctamente.');
    }

    public function cambiarEstado($id)
    {
        $servicio = Servicio::findOrFail($id);

        $servicio->update([
            'activo' => !$servicio->activo,
        ]);

        session()->flash('message', 'Estado del servicio actualizado correctamente.');
    }

    private function resetInput()
    {
        $this->servicio_id = null;

        $this->codigo = '';
        $this->nombre = '';
        $this->tipo_servicio = $this->tiposServicio[0] ?? 'Impresion';
        $this->tamano_papel = $this->tamanosPapel[0] ?? 'Carta';
        $this->color = $this->colores[0] ?? 'Blanco y negro';
        $this->caras = $this->carasOpciones[0] ?? 'Una cara';
        $this->unidad_cobro = $this->unidadesCobro[0] ?? 'Pagina';
        $this->costo_unitario = 0;
        $this->precio_unitario = 0;
        $this->descripcion = '';
        $this->activo = true;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $servicios = Servicio::query()
            ->where(function ($query) {
                $query->where('codigo', 'like', '%' . $this->search . '%')
                    ->orWhere('nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('tipo_servicio', 'like', '%' . $this->search . '%')
                    ->orWhere('tamano_papel', 'like', '%' . $this->search . '%')
                    ->orWhere('color', 'like', '%' . $this->search . '%');
            })
            ->when($this->filtroEstado === 'activos', function ($query) {
                $query->where('activo', true);
            })
            ->when($this->filtroEstado === 'inactivos', function ($query) {
                $query->where('activo', false);
            })
            ->when($this->filtroTipo !== 'todos', function ($query) {
                $query->where('tipo_servicio', $this->filtroTipo);
            })
            ->orderBy('tipo_servicio')
            ->orderBy('nombre')
            ->paginate($this->perPage);

        return view('livewire.servicios.servicio-index', [
            'servicios' => $servicios,
        ]);
    }
}
