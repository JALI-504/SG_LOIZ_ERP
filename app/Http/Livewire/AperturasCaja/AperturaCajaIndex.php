<?php

namespace App\Http\Livewire\AperturasCaja;

use App\Models\AperturaCaja;
use App\Models\BitacoraSistema;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class AperturaCajaIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 10;

    public $fecha;
    public $monto_inicial = 0;
    public $observacion;

    public $mostrarModalAnulacion = false;
    public $aperturaAnularId;
    public $motivoAnulacion;

    protected $messages = [
        'fecha.required' => 'Debe seleccionar una fecha.',
        'fecha.date' => 'La fecha no es válida.',

        'monto_inicial.required' => 'Debe ingresar el monto inicial.',
        'monto_inicial.numeric' => 'El monto inicial debe ser numérico.',
        'monto_inicial.min' => 'El monto inicial no puede ser negativo.',

        'observacion.max' => 'La observación no debe superar los 500 caracteres.',

        'motivoAnulacion.required' => 'Debe ingresar el motivo de anulación.',
        'motivoAnulacion.min' => 'El motivo debe tener al menos 5 caracteres.',
        'motivoAnulacion.max' => 'El motivo no debe superar los 500 caracteres.',
    ];

    public function mount()
    {
        if (!auth()->user()->can('ver aperturas caja')) {
            abort(403, 'No tiene permiso para ver aperturas de caja.');
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

    public function registrarApertura()
    {
        if (!auth()->user()->can('crear aperturas caja')) {
            abort(403, 'No tiene permiso para crear aperturas de caja.');
        }

        $this->validate([
            'fecha' => 'required|date',
            'monto_inicial' => 'required|numeric|min:0',
            'observacion' => 'nullable|max:500',
        ]);

        $aperturaAbierta = AperturaCaja::where('estado', 'Abierta')->first();

        if ($aperturaAbierta) {
            session()->flash('error', 'Ya existe una caja abierta: ' . $aperturaAbierta->codigo . '. Primero debe cerrarla o anularla.');
            return;
        }

        $aperturaMismaFecha = AperturaCaja::whereDate('fecha', $this->fecha)
            ->where('estado', 'Abierta')
            ->exists();

        if ($aperturaMismaFecha) {
            session()->flash('error', 'Ya existe una apertura activa para esta fecha.');
            return;
        }

        DB::transaction(function () {
            $apertura = AperturaCaja::create([
                'fecha' => $this->fecha,
                'hora_apertura' => now()->format('H:i:s'),
                'user_id' => auth()->id(),
                'monto_inicial' => $this->monto_inicial,
                'estado' => 'Abierta',
                'observacion' => $this->observacion,
            ]);

            BitacoraSistema::registrar(
                'Apertura de caja',
                'Registrar',
                'Registró la apertura de caja ' . $apertura->codigo . ' con monto inicial de L ' . number_format($apertura->monto_inicial, 2) . '.',
                AperturaCaja::class,
                $apertura->id,
                null,
                $apertura->fresh()->load('usuario')->toArray()
            );
        });

        $this->resetFormulario();

        session()->flash('message', 'Apertura de caja registrada correctamente.');
    }

    public function abrirAnular($id)
    {
        if (!auth()->user()->can('anular aperturas caja')) {
            abort(403, 'No tiene permiso para anular aperturas de caja.');
        }

        $apertura = AperturaCaja::with('cierreCaja')->findOrFail($id);

        if ($apertura->estado === 'Anulada') {
            session()->flash('error', 'Esta apertura ya está anulada.');
            return;
        }

        if ($apertura->estado === 'Cerrada') {
            session()->flash('error', 'No puede anular una apertura que ya fue cerrada.');
            return;
        }

        if ($apertura->cierreCaja) {
            session()->flash('error', 'No puede anular esta apertura porque ya tiene un cierre de caja relacionado.');
            return;
        }

        $this->aperturaAnularId = $apertura->id;
        $this->motivoAnulacion = '';
        $this->mostrarModalAnulacion = true;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function cerrarModalAnulacion()
    {
        $this->mostrarModalAnulacion = false;
        $this->aperturaAnularId = null;
        $this->motivoAnulacion = '';

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function confirmarAnular()
    {
        if (!auth()->user()->can('anular aperturas caja')) {
            abort(403, 'No tiene permiso para anular aperturas de caja.');
        }

        $this->validate([
            'aperturaAnularId' => 'required|exists:aperturas_caja,id',
            'motivoAnulacion' => 'required|min:5|max:500',
        ]);

        DB::transaction(function () {
            $apertura = AperturaCaja::with('cierreCaja')->findOrFail($this->aperturaAnularId);

            if ($apertura->estado === 'Anulada') {
                session()->flash('error', 'Esta apertura ya está anulada.');
                return;
            }

            if ($apertura->estado === 'Cerrada') {
                session()->flash('error', 'No puede anular una apertura que ya fue cerrada.');
                return;
            }

            if ($apertura->cierreCaja) {
                session()->flash('error', 'No puede anular esta apertura porque ya tiene un cierre de caja relacionado.');
                return;
            }

            $datosAnteriores = $apertura->toArray();

            $apertura->update([
                'estado' => 'Anulada',
                'fecha_anulacion' => now(),
                'anulado_por' => auth()->id(),
                'motivo_anulacion' => $this->motivoAnulacion,
            ]);

            BitacoraSistema::registrar(
                'Apertura de caja',
                'Anular',
                'Anuló la apertura de caja ' . $apertura->codigo . '. Motivo: ' . $this->motivoAnulacion,
                AperturaCaja::class,
                $apertura->id,
                $datosAnteriores,
                $apertura->fresh()->load(['usuario', 'usuarioAnulacion'])->toArray()
            );
        });

        $this->cerrarModalAnulacion();

        session()->flash('message', 'Apertura de caja anulada correctamente.');
    }

    private function resetFormulario()
    {
        $this->fecha = now()->format('Y-m-d');
        $this->monto_inicial = 0;
        $this->observacion = '';

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        if (!auth()->user()->can('ver aperturas caja')) {
            abort(403, 'No tiene permiso para ver aperturas de caja.');
        }

        $aperturas = AperturaCaja::with(['usuario', 'usuarioAnulacion', 'cierreCaja'])
            ->where(function ($query) {
                $query->where('codigo', 'like', '%' . $this->search . '%')
                    ->orWhere('fecha', 'like', '%' . $this->search . '%')
                    ->orWhere('estado', 'like', '%' . $this->search . '%');
            })
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate($this->perPage);

        $aperturaAbierta = AperturaCaja::with('usuario')
            ->where('estado', 'Abierta')
            ->orderByDesc('id')
            ->first();

        return view('livewire.aperturas-caja.apertura-caja-index', [
            'aperturas' => $aperturas,
            'aperturaAbierta' => $aperturaAbierta,
        ]);
    }
}
