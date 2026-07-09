<?php

namespace App\Http\Livewire\Clientes;

use App\Models\Cliente;
use App\Models\Departamento;
use App\Models\Municipio;
use Livewire\Component;
use Livewire\WithPagination;

class ClienteIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 10;

    public $filtroEstado = 'activos';

    public $cliente_id;

    public $primer_nombre;
    public $segundo_nombre;
    public $primer_apellido;
    public $segundo_apellido;

    public $codigo_pais = '+504';
    public $telefono;
    public $correo;
    public $dni;
    public $rtn;

    public $tipo_cliente = 'Natural';

    public $departamento_id;
    public $municipio_id;
    public $direccion_referencia;

    public $notas;
    public $activo = true;

    public $departamentos = [];
    public $municipios = [];

    public $modalTitle = 'Nuevo cliente';

    public function mount()
    {
        $this->departamentos = Departamento::where('activo', true)
            ->orderBy('nombre')
            ->get();

        $this->municipios = collect();
    }

    protected function rules()
    {
        return [
            'primer_nombre' => 'required|min:2|max:50',
            'segundo_nombre' => 'nullable|max:50',

            'primer_apellido' => 'required|min:2|max:50',
            'segundo_apellido' => 'nullable|max:50',

            'codigo_pais' => 'required|max:5',
            'telefono' => 'required|digits:8',

            'correo' => 'nullable|email|max:150',

            'dni' => 'required|digits:13|unique:clientes,dni,' . $this->cliente_id,
            'rtn' => 'nullable|digits:14',

            'tipo_cliente' => 'required',

            'departamento_id' => 'required|exists:departamentos,id',
            'municipio_id' => 'required|exists:municipios,id',

            'direccion_referencia' => 'required|min:5|max:500',
            'notas' => 'nullable|max:500',

            'activo' => 'boolean',
        ];
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function updatedDepartamentoId()
    {
        $this->municipio_id = null;
        $this->cargarMunicipios();
    }

    public function cargarMunicipios()
    {
        if ($this->departamento_id) {
            $this->municipios = Municipio::where('departamento_id', $this->departamento_id)
                ->where('activo', true)
                ->orderBy('nombre')
                ->get();
        } else {
            $this->municipios = collect();
        }
    }

    public function create()
    {
        $this->resetInput();

        $this->modalTitle = 'Nuevo cliente';

        $this->dispatchBrowserEvent('open-cliente-modal');
    }

    public function store()
    {
        $this->limpiarFormatos();

        $this->validate();

        Cliente::create([
            'primer_nombre' => $this->primer_nombre,
            'segundo_nombre' => $this->segundo_nombre,
            'primer_apellido' => $this->primer_apellido,
            'segundo_apellido' => $this->segundo_apellido,

            'codigo_pais' => $this->codigo_pais,
            'telefono' => $this->telefono,
            'correo' => $this->correo,
            'dni' => $this->dni,
            'rtn' => $this->rtn,

            'tipo_cliente' => $this->tipo_cliente,

            'departamento_id' => $this->departamento_id,
            'municipio_id' => $this->municipio_id,
            'direccion_referencia' => $this->direccion_referencia,

            'notas' => $this->notas,
            'activo' => $this->activo,
        ]);

        $this->resetInput();

        $this->dispatchBrowserEvent('close-cliente-modal');

        session()->flash('message', 'Cliente registrado correctamente.');
    }

    public function edit($id)
    {
        $cliente = Cliente::findOrFail($id);

        $this->cliente_id = $cliente->id;

        $this->primer_nombre = $cliente->primer_nombre;
        $this->segundo_nombre = $cliente->segundo_nombre;
        $this->primer_apellido = $cliente->primer_apellido;
        $this->segundo_apellido = $cliente->segundo_apellido;

        $this->codigo_pais = $cliente->codigo_pais;
        // $this->telefono = $cliente->telefono;
        $this->telefono = $cliente->telefono_formateado;
        $this->correo = $cliente->correo;
        // $this->dni = $cliente->dni;
        $this->dni = $cliente->dni_formateado;
        $this->rtn = $cliente->rtn;

        $this->tipo_cliente = $cliente->tipo_cliente;

        $this->departamento_id = $cliente->departamento_id;
        $this->cargarMunicipios();

        $this->municipio_id = $cliente->municipio_id;
        $this->direccion_referencia = $cliente->direccion_referencia;

        $this->notas = $cliente->notas;
        $this->activo = $cliente->activo;

        $this->modalTitle = 'Editar cliente';

        $this->dispatchBrowserEvent('open-cliente-modal');
    }

    public function update()
    {
        $this->limpiarFormatos();

        $this->validate();

        $cliente = Cliente::findOrFail($this->cliente_id);

        $cliente->update([
            'primer_nombre' => $this->primer_nombre,
            'segundo_nombre' => $this->segundo_nombre,
            'primer_apellido' => $this->primer_apellido,
            'segundo_apellido' => $this->segundo_apellido,

            'codigo_pais' => $this->codigo_pais,
            'telefono' => $this->telefono,
            'correo' => $this->correo,
            'dni' => $this->dni,
            'rtn' => $this->rtn,

            'tipo_cliente' => $this->tipo_cliente,

            'departamento_id' => $this->departamento_id,
            'municipio_id' => $this->municipio_id,
            'direccion_referencia' => $this->direccion_referencia,

            'notas' => $this->notas,
            'activo' => $this->activo,
        ]);

        $this->resetInput();

        $this->dispatchBrowserEvent('close-cliente-modal');

        session()->flash('message', 'Cliente actualizado correctamente.');
    }

    public function cambiarEstado($id)
    {
        $cliente = Cliente::findOrFail($id);

        $cliente->update([
            'activo' => !$cliente->activo,
        ]);

        session()->flash('message', 'Estado del cliente actualizado correctamente.');
    }

    private function limpiarFormatos()
    {
        $this->telefono = preg_replace('/\D/', '', $this->telefono);
        $this->dni = preg_replace('/\D/', '', $this->dni);

        if ($this->rtn) {
            $this->rtn = preg_replace('/\D/', '', $this->rtn);
        }
    }

    private function resetInput()
    {
        $this->cliente_id = null;

        $this->primer_nombre = '';
        $this->segundo_nombre = '';
        $this->primer_apellido = '';
        $this->segundo_apellido = '';

        $this->codigo_pais = '+504';
        $this->telefono = '';
        $this->correo = '';
        $this->dni = '';
        $this->rtn = '';

        $this->tipo_cliente = 'Natural';

        $this->departamento_id = null;
        $this->municipio_id = null;
        $this->direccion_referencia = '';

        $this->notas = '';
        $this->activo = true;

        $this->municipios = collect();

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $clientes = Cliente::with(['departamento', 'municipio'])
            ->where(function ($query) {
                $query->where('primer_nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('segundo_nombre', 'like', '%' . $this->search . '%')
                    ->orWhere('primer_apellido', 'like', '%' . $this->search . '%')
                    ->orWhere('segundo_apellido', 'like', '%' . $this->search . '%')
                    ->orWhere('telefono', 'like', '%' . $this->search . '%')
                    ->orWhere('dni', 'like', '%' . $this->search . '%')
                    ->orWhere('rtn', 'like', '%' . $this->search . '%')
                    ->orWhere('correo', 'like', '%' . $this->search . '%');
            })
            ->when($this->filtroEstado === 'activos', function ($query) {
                $query->where('activo', true);
            })
            ->when($this->filtroEstado === 'inactivos', function ($query) {
                $query->where('activo', false);
            })
            ->orderBy('id', 'desc')
            ->paginate($this->perPage);

        return view('livewire.clientes.cliente-index', [
            'clientes' => $clientes,
        ]);
    }
}
