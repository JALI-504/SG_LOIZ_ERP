<?php

namespace App\Http\Livewire\Bancos;

use App\Models\BitacoraSistema;
use App\Models\CuentaBancaria;
use Livewire\Component;
use Livewire\WithPagination;

class CuentaBancariaIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 10;

    public $cuenta_bancaria_id = null;

    public $banco;
    public $nombre_cuenta;
    public $numero_cuenta;
    public $tipo_cuenta = 'Cuenta de ahorro';
    public $moneda = 'HNL';
    public $saldo_inicial = 0;
    public $observacion;

    public $modoEdicion = false;
    public $mostrarFormulario = false;

    protected function rules()
    {
        return [
            'banco' => 'required|string|max:150',
            'nombre_cuenta' => 'required|string|max:150',
            'numero_cuenta' => 'nullable|string|max:100',
            'tipo_cuenta' => 'nullable|string|max:50',
            'moneda' => 'required|string|max:10',
            'saldo_inicial' => 'required|numeric|min:0',
            'observacion' => 'nullable|string',
        ];
    }

    protected $messages = [
        'banco.required' => 'Debe ingresar el nombre del banco.',
        'nombre_cuenta.required' => 'Debe ingresar el nombre de la cuenta.',
        'moneda.required' => 'Debe seleccionar la moneda.',
        'saldo_inicial.required' => 'Debe ingresar el saldo inicial.',
        'saldo_inicial.numeric' => 'El saldo inicial debe ser numérico.',
        'saldo_inicial.min' => 'El saldo inicial no puede ser negativo.',
    ];

    public function mount()
    {
        if (!auth()->user()->can('ver cuentas bancarias')) {
            abort(403, 'No tiene permiso para ver cuentas bancarias.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function abrirFormulario()
    {
        if (!auth()->user()->can('crear cuentas bancarias')) {
            abort(403, 'No tiene permiso para crear cuentas bancarias.');
        }

        $this->resetFormulario();
        $this->mostrarFormulario = true;
    }

    public function cerrarFormulario()
    {
        $this->resetFormulario();
        $this->mostrarFormulario = false;
    }

    private function resetFormulario()
    {
        $this->cuenta_bancaria_id = null;

        $this->banco = null;
        $this->nombre_cuenta = null;
        $this->numero_cuenta = null;
        $this->tipo_cuenta = 'Cuenta de ahorro';
        $this->moneda = 'HNL';
        $this->saldo_inicial = 0;
        $this->observacion = null;

        $this->modoEdicion = false;

        $this->resetValidation();
    }

    public function guardar()
    {
        if ($this->modoEdicion) {
            $this->actualizar();
        } else {
            $this->store();
        }
    }

    public function store()
    {
        if (!auth()->user()->can('crear cuentas bancarias')) {
            abort(403, 'No tiene permiso para crear cuentas bancarias.');
        }

        $this->validate();

        $cuenta = CuentaBancaria::create([
            'banco' => trim($this->banco),
            'nombre_cuenta' => trim($this->nombre_cuenta),
            'numero_cuenta' => $this->numero_cuenta ? trim($this->numero_cuenta) : null,
            'tipo_cuenta' => $this->tipo_cuenta,
            'moneda' => $this->moneda,
            'saldo_inicial' => $this->saldo_inicial,
            'saldo_actual' => $this->saldo_inicial,
            'activo' => true,
            'observacion' => $this->observacion,
            'user_id' => auth()->id(),
        ]);

        BitacoraSistema::registrar(
            'Cuentas bancarias',
            'Registrar',
            'Registró la cuenta bancaria ' . $cuenta->codigo . ' - ' . $cuenta->banco . '.',
            CuentaBancaria::class,
            $cuenta->id,
            null,
            $cuenta->fresh()->load('usuario')->toArray()
        );

        $this->cerrarFormulario();

        session()->flash('message', 'Cuenta bancaria registrada correctamente.');
    }

    public function editar($id)
    {
        if (!auth()->user()->can('editar cuentas bancarias')) {
            abort(403, 'No tiene permiso para editar cuentas bancarias.');
        }

        $cuenta = CuentaBancaria::findOrFail($id);

        $this->cuenta_bancaria_id = $cuenta->id;
        $this->banco = $cuenta->banco;
        $this->nombre_cuenta = $cuenta->nombre_cuenta;
        $this->numero_cuenta = $cuenta->numero_cuenta;
        $this->tipo_cuenta = $cuenta->tipo_cuenta;
        $this->moneda = $cuenta->moneda;
        $this->saldo_inicial = $cuenta->saldo_inicial;
        $this->observacion = $cuenta->observacion;

        $this->modoEdicion = true;
        $this->mostrarFormulario = true;
    }

    public function actualizar()
    {
        if (!auth()->user()->can('editar cuentas bancarias')) {
            abort(403, 'No tiene permiso para editar cuentas bancarias.');
        }

        $this->validate();

        $cuenta = CuentaBancaria::findOrFail($this->cuenta_bancaria_id);

        $datosAnteriores = $cuenta->toArray();

        $diferenciaSaldoInicial = (float) $this->saldo_inicial - (float) $cuenta->saldo_inicial;

        $cuenta->update([
            'banco' => trim($this->banco),
            'nombre_cuenta' => trim($this->nombre_cuenta),
            'numero_cuenta' => $this->numero_cuenta ? trim($this->numero_cuenta) : null,
            'tipo_cuenta' => $this->tipo_cuenta,
            'moneda' => $this->moneda,
            'saldo_inicial' => $this->saldo_inicial,
            'saldo_actual' => (float) $cuenta->saldo_actual + $diferenciaSaldoInicial,
            'observacion' => $this->observacion,
        ]);

        BitacoraSistema::registrar(
            'Cuentas bancarias',
            'Actualizar',
            'Actualizó la cuenta bancaria ' . $cuenta->codigo . ' - ' . $cuenta->banco . '.',
            CuentaBancaria::class,
            $cuenta->id,
            $datosAnteriores,
            $cuenta->fresh()->load('usuario')->toArray()
        );

        $this->cerrarFormulario();

        session()->flash('message', 'Cuenta bancaria actualizada correctamente.');
    }

    public function cambiarEstado($id)
    {
        if (!auth()->user()->can('eliminar cuentas bancarias')) {
            abort(403, 'No tiene permiso para activar o desactivar cuentas bancarias.');
        }

        $cuenta = CuentaBancaria::findOrFail($id);

        $datosAnteriores = $cuenta->toArray();

        $cuenta->update([
            'activo' => !$cuenta->activo,
        ]);

        BitacoraSistema::registrar(
            'Cuentas bancarias',
            'Actualizar',
            ($cuenta->activo ? 'Reactivó' : 'Desactivó') . ' la cuenta bancaria ' . $cuenta->codigo . ' - ' . $cuenta->banco . '.',
            CuentaBancaria::class,
            $cuenta->id,
            $datosAnteriores,
            $cuenta->fresh()->load('usuario')->toArray()
        );

        session()->flash('message', 'Estado de cuenta bancaria actualizado correctamente.');
    }

    public function render()
    {
        if (!auth()->user()->can('ver cuentas bancarias')) {
            abort(403, 'No tiene permiso para ver cuentas bancarias.');
        }

        $cuentas = CuentaBancaria::with('usuario')
            ->where(function ($query) {
                $query->where('codigo', 'like', '%' . $this->search . '%')
                    ->orWhere('banco', 'like', '%' . $this->search . '%')
                    ->orWhere('nombre_cuenta', 'like', '%' . $this->search . '%')
                    ->orWhere('numero_cuenta', 'like', '%' . $this->search . '%')
                    ->orWhere('moneda', 'like', '%' . $this->search . '%');
            })
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.bancos.cuenta-bancaria-index', [
            'cuentas' => $cuentas,
        ]);
    }
}
