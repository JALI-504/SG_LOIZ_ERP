<?php

namespace App\Http\Livewire\Bancos;

use App\Models\BitacoraSistema;
use App\Models\CuentaBancaria;
use App\Models\MovimientoBancario;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class MovimientoBancarioIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 10;

    public $cuenta_bancaria_id = '';
    public $fecha;
    public $tipo = 'Entrada';
    public $categoria = 'Depósito';
    public $referencia;
    public $descripcion;
    public $monto = 0;
    public $observacion;

    public $mostrarFormulario = false;

    public $mostrarModalAnular = false;
    public $movimientoAnularId = null;
    public $movimientoAnularCodigo = null;
    public $motivo_anulacion;

    protected function rules()
    {
        return [
            'cuenta_bancaria_id' => 'required|exists:cuentas_bancarias,id',
            'fecha' => 'required|date',
            'tipo' => 'required|in:Entrada,Salida',
            'categoria' => 'required|string|max:80',
            'referencia' => 'nullable|string|max:150',
            'descripcion' => 'nullable|string',
            'monto' => 'required|numeric|min:0.01',
            'observacion' => 'nullable|string',
        ];
    }

    protected $messages = [
        'cuenta_bancaria_id.required' => 'Debe seleccionar una cuenta bancaria.',
        'cuenta_bancaria_id.exists' => 'La cuenta bancaria seleccionada no existe.',
        'fecha.required' => 'Debe ingresar la fecha del movimiento.',
        'tipo.required' => 'Debe seleccionar el tipo de movimiento.',
        'tipo.in' => 'El tipo de movimiento no es válido.',
        'categoria.required' => 'Debe seleccionar la categoría.',
        'monto.required' => 'Debe ingresar el monto.',
        'monto.numeric' => 'El monto debe ser numérico.',
        'monto.min' => 'El monto debe ser mayor a cero.',
    ];

    public function mount()
    {
        if (!auth()->user()->can('ver movimientos bancarios')) {
            abort(403, 'No tiene permiso para ver movimientos bancarios.');
        }

        $this->fecha = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updatedTipo()
    {
        if ($this->tipo === 'Entrada') {
            $this->categoria = 'Depósito';
        } else {
            $this->categoria = 'Retiro';
        }
    }

    public function abrirFormulario()
    {
        if (!auth()->user()->can('crear movimientos bancarios')) {
            abort(403, 'No tiene permiso para crear movimientos bancarios.');
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
        $this->cuenta_bancaria_id = '';
        $this->fecha = now()->format('Y-m-d');
        $this->tipo = 'Entrada';
        $this->categoria = 'Depósito';
        $this->referencia = null;
        $this->descripcion = null;
        $this->monto = 0;
        $this->observacion = null;

        $this->resetValidation();
    }

    public function registrarMovimiento()
    {
        if (!auth()->user()->can('crear movimientos bancarios')) {
            abort(403, 'No tiene permiso para crear movimientos bancarios.');
        }

        $this->validate();

        DB::transaction(function () {
            $cuenta = CuentaBancaria::where('activo', true)
                ->lockForUpdate()
                ->findOrFail($this->cuenta_bancaria_id);

            $saldoAnterior = (float) $cuenta->saldo_actual;
            $monto = (float) $this->monto;

            if ($this->tipo === 'Entrada') {
                $saldoNuevo = $saldoAnterior + $monto;
            } else {
                $saldoNuevo = $saldoAnterior - $monto;
            }

            $movimiento = MovimientoBancario::create([
                'cuenta_bancaria_id' => $cuenta->id,
                'fecha' => $this->fecha,
                'hora' => now()->format('H:i:s'),
                'tipo' => $this->tipo,
                'categoria' => $this->categoria,
                'referencia' => $this->referencia ? trim($this->referencia) : null,
                'descripcion' => $this->descripcion ? trim($this->descripcion) : null,
                'monto' => $monto,
                'saldo_anterior' => $saldoAnterior,
                'saldo_nuevo' => $saldoNuevo,
                'origen' => 'Manual',
                'origen_id' => null,
                'estado' => 'Activo',
                'observacion' => $this->observacion,
                'user_id' => auth()->id(),
            ]);

            $datosAnterioresCuenta = $cuenta->toArray();

            $cuenta->update([
                'saldo_actual' => $saldoNuevo,
            ]);

            BitacoraSistema::registrar(
                'Movimientos bancarios',
                'Registrar',
                'Registró el movimiento bancario ' . $movimiento->codigo . ' en la cuenta ' . $cuenta->codigo . '.',
                MovimientoBancario::class,
                $movimiento->id,
                null,
                $movimiento->fresh()->load(['cuentaBancaria', 'usuario'])->toArray()
            );

            BitacoraSistema::registrar(
                'Cuentas bancarias',
                'Actualizar',
                'Actualizó el saldo de la cuenta bancaria ' . $cuenta->codigo . ' mediante el movimiento ' . $movimiento->codigo . '.',
                CuentaBancaria::class,
                $cuenta->id,
                $datosAnterioresCuenta,
                $cuenta->fresh()->load('usuario')->toArray()
            );
        });

        $this->cerrarFormulario();

        session()->flash('message', 'Movimiento bancario registrado correctamente.');
    }

    public function abrirAnular($id)
    {
        if (!auth()->user()->can('anular movimientos bancarios')) {
            abort(403, 'No tiene permiso para anular movimientos bancarios.');
        }

        $movimiento = MovimientoBancario::findOrFail($id);

        if ($movimiento->estado === 'Anulado') {
            session()->flash('error', 'Este movimiento ya fue anulado.');
            return;
        }

        $ultimoMovimientoActivo = MovimientoBancario::where('cuenta_bancaria_id', $movimiento->cuenta_bancaria_id)
            ->where('estado', 'Activo')
            ->orderByDesc('id')
            ->first();

        if (!$ultimoMovimientoActivo || $ultimoMovimientoActivo->id !== $movimiento->id) {
            session()->flash('error', 'Solo puede anular el último movimiento activo de la cuenta bancaria para mantener el saldo correcto.');
            return;
        }

        $this->movimientoAnularId = $movimiento->id;
        $this->movimientoAnularCodigo = $movimiento->codigo;
        $this->motivo_anulacion = null;
        $this->mostrarModalAnular = true;
    }

    public function cerrarModalAnular()
    {
        $this->mostrarModalAnular = false;
        $this->movimientoAnularId = null;
        $this->movimientoAnularCodigo = null;
        $this->motivo_anulacion = null;

        $this->resetValidation();
    }

    public function confirmarAnular()
    {
        if (!auth()->user()->can('anular movimientos bancarios')) {
            abort(403, 'No tiene permiso para anular movimientos bancarios.');
        }

        $this->validate([
            'motivo_anulacion' => 'required|string|max:500',
        ], [
            'motivo_anulacion.required' => 'Debe ingresar el motivo de anulación.',
        ]);

        DB::transaction(function () {
            $movimiento = MovimientoBancario::with('cuentaBancaria')
                ->lockForUpdate()
                ->findOrFail($this->movimientoAnularId);

            if ($movimiento->estado === 'Anulado') {
                throw new \Exception('Este movimiento ya fue anulado.');
            }

            $ultimoMovimientoActivo = MovimientoBancario::where('cuenta_bancaria_id', $movimiento->cuenta_bancaria_id)
                ->where('estado', 'Activo')
                ->orderByDesc('id')
                ->lockForUpdate()
                ->first();

            if (!$ultimoMovimientoActivo || $ultimoMovimientoActivo->id !== $movimiento->id) {
                throw new \Exception('Solo puede anular el último movimiento activo de la cuenta bancaria.');
            }

            $cuenta = CuentaBancaria::lockForUpdate()
                ->findOrFail($movimiento->cuenta_bancaria_id);

            $datosAnterioresMovimiento = $movimiento->toArray();
            $datosAnterioresCuenta = $cuenta->toArray();

            $saldoActual = (float) $cuenta->saldo_actual;
            $monto = (float) $movimiento->monto;

            if ($movimiento->tipo === 'Entrada') {
                $saldoNuevo = $saldoActual - $monto;
            } else {
                $saldoNuevo = $saldoActual + $monto;
            }

            $movimiento->update([
                'estado' => 'Anulado',
                'fecha_anulacion' => now(),
                'anulado_por' => auth()->id(),
                'motivo_anulacion' => $this->motivo_anulacion,
            ]);

            $cuenta->update([
                'saldo_actual' => $saldoNuevo,
            ]);

            BitacoraSistema::registrar(
                'Movimientos bancarios',
                'Anular',
                'Anuló el movimiento bancario ' . $movimiento->codigo . '.',
                MovimientoBancario::class,
                $movimiento->id,
                $datosAnterioresMovimiento,
                $movimiento->fresh()->load(['cuentaBancaria', 'usuario', 'usuarioAnulacion'])->toArray()
            );

            BitacoraSistema::registrar(
                'Cuentas bancarias',
                'Actualizar',
                'Actualizó el saldo de la cuenta bancaria ' . $cuenta->codigo . ' por anulación del movimiento ' . $movimiento->codigo . '.',
                CuentaBancaria::class,
                $cuenta->id,
                $datosAnterioresCuenta,
                $cuenta->fresh()->load('usuario')->toArray()
            );
        });

        $this->cerrarModalAnular();

        session()->flash('message', 'Movimiento bancario anulado correctamente.');
    }

    public function render()
    {
        if (!auth()->user()->can('ver movimientos bancarios')) {
            abort(403, 'No tiene permiso para ver movimientos bancarios.');
        }

        $cuentas = CuentaBancaria::where('activo', true)
            ->orderBy('banco')
            ->orderBy('nombre_cuenta')
            ->get();

        $movimientos = MovimientoBancario::with(['cuentaBancaria', 'usuario', 'usuarioAnulacion'])
            ->where(function ($query) {
                $query->where('codigo', 'like', '%' . $this->search . '%')
                    ->orWhere('tipo', 'like', '%' . $this->search . '%')
                    ->orWhere('categoria', 'like', '%' . $this->search . '%')
                    ->orWhere('referencia', 'like', '%' . $this->search . '%')
                    ->orWhere('descripcion', 'like', '%' . $this->search . '%')
                    ->orWhereHas('cuentaBancaria', function ($q) {
                        $q->where('codigo', 'like', '%' . $this->search . '%')
                            ->orWhere('banco', 'like', '%' . $this->search . '%')
                            ->orWhere('nombre_cuenta', 'like', '%' . $this->search . '%')
                            ->orWhere('numero_cuenta', 'like', '%' . $this->search . '%');
                    });
            })
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.bancos.movimiento-bancario-index', [
            'cuentas' => $cuentas,
            'movimientos' => $movimientos,
        ]);
    }
}
