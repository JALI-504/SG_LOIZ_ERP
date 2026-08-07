<?php

namespace App\Http\Livewire\Bancos;

use App\Models\BitacoraSistema;
use App\Models\ConciliacionBancaria;
use App\Models\CuentaBancaria;
use App\Models\MovimientoBancario;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class ConciliacionBancariaIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 10;

    public $cuenta_bancaria_id = '';
    public $fecha_inicio;
    public $fecha_fin;
    public $saldo_final_banco = 0;
    public $observacion;

    public $saldo_inicial_sistema = 0;
    public $total_entradas_sistema = 0;
    public $total_salidas_sistema = 0;
    public $saldo_final_sistema = 0;
    public $diferencia = 0;
    public $cantidad_movimientos = 0;

    public $mostrarFormulario = false;
    public $mostrarResultado = false;

    public $mostrarModalAnular = false;
    public $conciliacionAnularId = null;
    public $conciliacionAnularCodigo = null;
    public $motivo_anulacion;

    protected function rules()
    {
        return [
            'cuenta_bancaria_id' => 'required|exists:cuentas_bancarias,id',
            'fecha_inicio' => 'required|date',
            'fecha_fin' => 'required|date|after_or_equal:fecha_inicio',
            'saldo_final_banco' => 'required|numeric',
            'observacion' => 'nullable|string',
        ];
    }

    protected $messages = [
        'cuenta_bancaria_id.required' => 'Debe seleccionar una cuenta bancaria.',
        'cuenta_bancaria_id.exists' => 'La cuenta bancaria seleccionada no existe.',
        'fecha_inicio.required' => 'Debe ingresar la fecha inicial.',
        'fecha_fin.required' => 'Debe ingresar la fecha final.',
        'fecha_fin.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
        'saldo_final_banco.required' => 'Debe ingresar el saldo final según banco.',
        'saldo_final_banco.numeric' => 'El saldo final del banco debe ser numérico.',
    ];

    public function mount()
    {
        if (!auth()->user()->can('ver conciliaciones bancarias')) {
            abort(403, 'No tiene permiso para ver conciliaciones bancarias.');
        }

        $this->fecha_inicio = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_fin = now()->format('Y-m-d');
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
        if (!auth()->user()->can('crear conciliaciones bancarias')) {
            abort(403, 'No tiene permiso para crear conciliaciones bancarias.');
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
        $this->fecha_inicio = now()->startOfMonth()->format('Y-m-d');
        $this->fecha_fin = now()->format('Y-m-d');
        $this->saldo_final_banco = 0;
        $this->observacion = null;

        $this->saldo_inicial_sistema = 0;
        $this->total_entradas_sistema = 0;
        $this->total_salidas_sistema = 0;
        $this->saldo_final_sistema = 0;
        $this->diferencia = 0;
        $this->cantidad_movimientos = 0;

        $this->mostrarResultado = false;

        $this->resetValidation();
    }

    public function calcular()
    {
        if (!auth()->user()->can('crear conciliaciones bancarias')) {
            abort(403, 'No tiene permiso para crear conciliaciones bancarias.');
        }

        $this->validate();

        $resultado = $this->calcularDatosConciliacion();

        $this->saldo_inicial_sistema = $resultado['saldo_inicial_sistema'];
        $this->total_entradas_sistema = $resultado['total_entradas_sistema'];
        $this->total_salidas_sistema = $resultado['total_salidas_sistema'];
        $this->saldo_final_sistema = $resultado['saldo_final_sistema'];
        $this->diferencia = $resultado['diferencia'];
        $this->cantidad_movimientos = $resultado['cantidad_movimientos'];

        $this->mostrarResultado = true;
    }

    private function calcularDatosConciliacion()
    {
        $cuenta = CuentaBancaria::findOrFail($this->cuenta_bancaria_id);

        $entradasAntes = MovimientoBancario::query()
            ->where('cuenta_bancaria_id', $cuenta->id)
            ->where('estado', 'Activo')
            ->whereDate('fecha', '<', $this->fecha_inicio)
            ->where('tipo', 'Entrada')
            ->sum('monto');

        $salidasAntes = MovimientoBancario::query()
            ->where('cuenta_bancaria_id', $cuenta->id)
            ->where('estado', 'Activo')
            ->whereDate('fecha', '<', $this->fecha_inicio)
            ->where('tipo', 'Salida')
            ->sum('monto');

        $saldoInicialSistema = (float) $cuenta->saldo_inicial + (float) $entradasAntes - (float) $salidasAntes;

        $totalEntradasSistema = MovimientoBancario::query()
            ->where('cuenta_bancaria_id', $cuenta->id)
            ->where('estado', 'Activo')
            ->whereBetween('fecha', [$this->fecha_inicio, $this->fecha_fin])
            ->where('tipo', 'Entrada')
            ->sum('monto');

        $totalSalidasSistema = MovimientoBancario::query()
            ->where('cuenta_bancaria_id', $cuenta->id)
            ->where('estado', 'Activo')
            ->whereBetween('fecha', [$this->fecha_inicio, $this->fecha_fin])
            ->where('tipo', 'Salida')
            ->sum('monto');

        $cantidadMovimientos = MovimientoBancario::query()
            ->where('cuenta_bancaria_id', $cuenta->id)
            ->where('estado', 'Activo')
            ->whereBetween('fecha', [$this->fecha_inicio, $this->fecha_fin])
            ->count();

        $saldoFinalSistema = (float) $saldoInicialSistema + (float) $totalEntradasSistema - (float) $totalSalidasSistema;

        $diferencia = (float) $this->saldo_final_banco - (float) $saldoFinalSistema;

        return [
            'saldo_inicial_sistema' => round($saldoInicialSistema, 2),
            'total_entradas_sistema' => round($totalEntradasSistema, 2),
            'total_salidas_sistema' => round($totalSalidasSistema, 2),
            'saldo_final_sistema' => round($saldoFinalSistema, 2),
            'diferencia' => round($diferencia, 2),
            'cantidad_movimientos' => $cantidadMovimientos,
        ];
    }

    public function registrarConciliacion()
    {
        if (!auth()->user()->can('crear conciliaciones bancarias')) {
            abort(403, 'No tiene permiso para crear conciliaciones bancarias.');
        }

        $this->validate();

        $resultado = $this->calcularDatosConciliacion();

        $existeConciliacion = ConciliacionBancaria::query()
            ->where('cuenta_bancaria_id', $this->cuenta_bancaria_id)
            ->whereDate('fecha_inicio', $this->fecha_inicio)
            ->whereDate('fecha_fin', $this->fecha_fin)
            ->where('estado', '!=', 'Anulada')
            ->exists();

        if ($existeConciliacion) {
            session()->flash('error', 'Ya existe una conciliación activa para esta cuenta bancaria y este rango de fechas.');
            return;
        }

        $estado = abs((float) $resultado['diferencia']) <= 0.01
            ? 'Conciliada'
            : 'Con diferencia';

        $conciliacion = ConciliacionBancaria::create([
            'cuenta_bancaria_id' => $this->cuenta_bancaria_id,
            'fecha_inicio' => $this->fecha_inicio,
            'fecha_fin' => $this->fecha_fin,
            'saldo_inicial_sistema' => $resultado['saldo_inicial_sistema'],
            'total_entradas_sistema' => $resultado['total_entradas_sistema'],
            'total_salidas_sistema' => $resultado['total_salidas_sistema'],
            'saldo_final_sistema' => $resultado['saldo_final_sistema'],
            'saldo_final_banco' => $this->saldo_final_banco,
            'diferencia' => $resultado['diferencia'],
            'cantidad_movimientos' => $resultado['cantidad_movimientos'],
            'estado' => $estado,
            'observacion' => $this->observacion,
            'user_id' => auth()->id(),
        ]);

        BitacoraSistema::registrar(
            'Conciliaciones bancarias',
            'Registrar',
            'Registró la conciliación bancaria ' . $conciliacion->codigo . '.',
            ConciliacionBancaria::class,
            $conciliacion->id,
            null,
            $conciliacion->fresh()->load(['cuentaBancaria', 'usuario'])->toArray()
        );

        $this->cerrarFormulario();

        session()->flash('message', 'Conciliación bancaria registrada correctamente.');
    }

    public function abrirAnular($id)
    {
        if (!auth()->user()->can('anular conciliaciones bancarias')) {
            abort(403, 'No tiene permiso para anular conciliaciones bancarias.');
        }

        $conciliacion = ConciliacionBancaria::findOrFail($id);

        if ($conciliacion->estado === 'Anulada') {
            session()->flash('error', 'Esta conciliación ya fue anulada.');
            return;
        }

        $this->conciliacionAnularId = $conciliacion->id;
        $this->conciliacionAnularCodigo = $conciliacion->codigo;
        $this->motivo_anulacion = null;
        $this->mostrarModalAnular = true;
    }

    public function cerrarModalAnular()
    {
        $this->mostrarModalAnular = false;
        $this->conciliacionAnularId = null;
        $this->conciliacionAnularCodigo = null;
        $this->motivo_anulacion = null;

        $this->resetValidation();
    }

    public function confirmarAnular()
    {
        if (!auth()->user()->can('anular conciliaciones bancarias')) {
            abort(403, 'No tiene permiso para anular conciliaciones bancarias.');
        }

        $this->validate([
            'motivo_anulacion' => 'required|string|max:500',
        ], [
            'motivo_anulacion.required' => 'Debe ingresar el motivo de anulación.',
        ]);

        $conciliacion = ConciliacionBancaria::findOrFail($this->conciliacionAnularId);

        if ($conciliacion->estado === 'Anulada') {
            session()->flash('error', 'Esta conciliación ya fue anulada.');
            $this->cerrarModalAnular();
            return;
        }

        $datosAnteriores = $conciliacion->toArray();

        $conciliacion->update([
            'estado' => 'Anulada',
            'fecha_anulacion' => now(),
            'anulado_por' => auth()->id(),
            'motivo_anulacion' => $this->motivo_anulacion,
        ]);

        BitacoraSistema::registrar(
            'Conciliaciones bancarias',
            'Anular',
            'Anuló la conciliación bancaria ' . $conciliacion->codigo . '.',
            ConciliacionBancaria::class,
            $conciliacion->id,
            $datosAnteriores,
            $conciliacion->fresh()->load(['cuentaBancaria', 'usuario', 'usuarioAnulacion'])->toArray()
        );

        $this->cerrarModalAnular();

        session()->flash('message', 'Conciliación bancaria anulada correctamente.');
    }

    public function render()
    {
        if (!auth()->user()->can('ver conciliaciones bancarias')) {
            abort(403, 'No tiene permiso para ver conciliaciones bancarias.');
        }

        $cuentas = CuentaBancaria::where('activo', true)
            ->orderBy('banco')
            ->orderBy('nombre_cuenta')
            ->get();

        $conciliaciones = ConciliacionBancaria::with(['cuentaBancaria', 'usuario', 'usuarioAnulacion'])
            ->where(function ($query) {
                $query->where('codigo', 'like', '%' . $this->search . '%')
                    ->orWhere('estado', 'like', '%' . $this->search . '%')
                    ->orWhereHas('cuentaBancaria', function ($q) {
                        $q->where('codigo', 'like', '%' . $this->search . '%')
                            ->orWhere('banco', 'like', '%' . $this->search . '%')
                            ->orWhere('nombre_cuenta', 'like', '%' . $this->search . '%')
                            ->orWhere('numero_cuenta', 'like', '%' . $this->search . '%');
                    });
            })
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.bancos.conciliacion-bancaria-index', [
            'cuentas' => $cuentas,
            'conciliaciones' => $conciliaciones,
        ]);
    }
}
