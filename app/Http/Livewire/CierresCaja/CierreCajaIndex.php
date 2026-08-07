<?php

namespace App\Http\Livewire\CierresCaja;

use App\Models\CierreCaja;
use App\Models\Gasto;
use App\Models\PagoCompra;
use App\Models\PagoVenta;
use App\Models\BitacoraSistema;
use App\Models\AperturaCaja;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class CierreCajaIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 10;

    public $fecha;
    public $monto_inicial = 0;
    public $efectivo_contado = 0;
    public $otros_ingresos = 0;
    public $otros_egresos = 0;
    public $observacion;

    public $apertura_caja_id = null;
    public $apertura_caja_codigo = null;
    public $apertura_caja_usuario = null;
    public $apertura_caja_fecha = null;
    public $apertura_caja_monto_inicial = 0;

    public $ventas_efectivo = 0;
    public $ventas_transferencia = 0;
    public $ventas_tarjeta = 0;
    public $ventas_otros = 0;

    public $total_ingresos_ventas = 0;

    public $gastos_registrados = 0;
    public $pagos_proveedores = 0;

    public $total_ingresos = 0;
    public $total_egresos = 0;

    public $efectivo_esperado = 0;
    public $diferencia = 0;

    public $cantidad_pagos_ventas = 0;
    public $cantidad_gastos = 0;
    public $cantidad_pagos_proveedores = 0;

    public $mostrarModalDetalle = false;
    public $cierreDetalle = null;

    public $mostrarModalAnulacion = false;
    public $cierreAnularId;
    public $motivoAnulacion;

    protected $messages = [
        'fecha.required' => 'Debe seleccionar una fecha.',
        'fecha.date' => 'La fecha no es válida.',

        'monto_inicial.required' => 'Debe ingresar el monto inicial.',
        'monto_inicial.numeric' => 'El monto inicial debe ser numérico.',
        'monto_inicial.min' => 'El monto inicial no puede ser negativo.',

        'efectivo_contado.required' => 'Debe ingresar el efectivo contado.',
        'efectivo_contado.numeric' => 'El efectivo contado debe ser numérico.',
        'efectivo_contado.min' => 'El efectivo contado no puede ser negativo.',

        'otros_ingresos.numeric' => 'Otros ingresos debe ser numérico.',
        'otros_ingresos.min' => 'Otros ingresos no puede ser negativo.',

        'otros_egresos.numeric' => 'Otros egresos debe ser numérico.',
        'otros_egresos.min' => 'Otros egresos no puede ser negativo.',

        'observacion.max' => 'La observación no debe superar los 500 caracteres.',

        'motivoAnulacion.required' => 'Debe ingresar el motivo de anulación.',
        'motivoAnulacion.min' => 'El motivo debe tener al menos 5 caracteres.',
        'motivoAnulacion.max' => 'El motivo no debe superar los 500 caracteres.',
    ];

    public function mount()
    {
        if (!auth()->user()->can('ver cierres caja')) {
            abort(403, 'No tiene permiso para ver cierres de caja.');
        }

        $this->cargarAperturaAbierta();

        $this->fecha = $this->apertura_caja_fecha ?: now()->format('Y-m-d');
        $this->monto_inicial = $this->apertura_caja_monto_inicial ?: 0;

        $this->cargarResumenCaja();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updated($propertyName)
    {
        if (
            $propertyName === 'fecha' ||
            $propertyName === 'monto_inicial' ||
            $propertyName === 'efectivo_contado' ||
            $propertyName === 'otros_ingresos' ||
            $propertyName === 'otros_egresos'
        ) {
            $this->cargarResumenCaja();
        }
    }

    private function cargarAperturaAbierta()
    {
        $apertura = AperturaCaja::with('usuario')
            ->where('estado', 'Abierta')
            ->orderByDesc('id')
            ->first();

        if ($apertura) {
            $this->apertura_caja_id = $apertura->id;
            $this->apertura_caja_codigo = $apertura->codigo;
            $this->apertura_caja_usuario = $apertura->usuario ? $apertura->usuario->name : 'Sistema';
            $this->apertura_caja_fecha = $apertura->fecha;
            $this->apertura_caja_monto_inicial = $apertura->monto_inicial;

            $this->fecha = $apertura->fecha;
            $this->monto_inicial = $apertura->monto_inicial;
        } else {
            $this->apertura_caja_id = null;
            $this->apertura_caja_codigo = null;
            $this->apertura_caja_usuario = null;
            $this->apertura_caja_fecha = null;
            $this->apertura_caja_monto_inicial = 0;
        }
    }

    public function cargarResumenCaja()
    {
        $this->cargarAperturaAbierta();

        $fecha = $this->fecha ?: now()->format('Y-m-d');

        $montoInicial = (float) $this->monto_inicial;

        if ($this->apertura_caja_id) {
            $fecha = $this->apertura_caja_fecha;
            $montoInicial = (float) $this->apertura_caja_monto_inicial;

            $this->fecha = $fecha;
            $this->monto_inicial = $montoInicial;
        }

        $efectivoContado = (float) $this->efectivo_contado;
        $otrosIngresos = (float) $this->otros_ingresos;
        $otrosEgresos = (float) $this->otros_egresos;

        if ($montoInicial < 0) {
            $montoInicial = 0;
        }

        if ($efectivoContado < 0) {
            $efectivoContado = 0;
        }

        if ($otrosIngresos < 0) {
            $otrosIngresos = 0;
        }

        if ($otrosEgresos < 0) {
            $otrosEgresos = 0;
        }

        $pagosVentasQuery = PagoVenta::query()
            ->where('estado', 'Activo')
            ->whereDate('fecha', $fecha);

        $this->cantidad_pagos_ventas = (clone $pagosVentasQuery)->count();

        $this->total_ingresos_ventas = (float) (clone $pagosVentasQuery)->sum('monto');

        $this->ventas_efectivo = (float) (clone $pagosVentasQuery)
            ->where('metodo_pago', 'like', '%Efectivo%')
            ->sum('monto');

        $this->ventas_transferencia = (float) (clone $pagosVentasQuery)
            ->where(function ($query) {
                $query->where('metodo_pago', 'like', '%Transferencia%')
                    ->orWhere('metodo_pago', 'like', '%Deposito%')
                    ->orWhere('metodo_pago', 'like', '%Depósito%');
            })
            ->sum('monto');

        $this->ventas_tarjeta = (float) (clone $pagosVentasQuery)
            ->where(function ($query) {
                $query->where('metodo_pago', 'like', '%Tarjeta%')
                    ->orWhere('metodo_pago', 'like', '%POS%');
            })
            ->sum('monto');

        $this->ventas_otros = round(
            max(
                $this->total_ingresos_ventas
                    - $this->ventas_efectivo
                    - $this->ventas_transferencia
                    - $this->ventas_tarjeta,
                0
            ),
            2
        );

        $gastosQuery = Gasto::query()
            ->where('estado', 'Registrado')
            ->whereDate('fecha', $fecha);

        $this->gastos_registrados = (float) (clone $gastosQuery)->sum('monto');
        $this->cantidad_gastos = (clone $gastosQuery)->count();

        $pagosProveedoresQuery = PagoCompra::query()
            ->where('estado', 'Activo')
            ->whereDate('fecha', $fecha);

        $this->pagos_proveedores = (float) (clone $pagosProveedoresQuery)->sum('monto');
        $this->cantidad_pagos_proveedores = (clone $pagosProveedoresQuery)->count();

        $this->monto_inicial = round($montoInicial, 2);
        $this->efectivo_contado = round($efectivoContado, 2);
        $this->otros_ingresos = round($otrosIngresos, 2);
        $this->otros_egresos = round($otrosEgresos, 2);

        $this->total_ingresos = round(
            $montoInicial + $this->total_ingresos_ventas + $otrosIngresos,
            2
        );

        $this->total_egresos = round(
            $this->gastos_registrados + $this->pagos_proveedores + $otrosEgresos,
            2
        );

        $this->efectivo_esperado = round(
            $montoInicial
                + $this->ventas_efectivo
                + $otrosIngresos
                - $this->gastos_registrados
                - $this->pagos_proveedores
                - $otrosEgresos,
            2
        );

        $this->diferencia = round(
            $efectivoContado - $this->efectivo_esperado,
            2
        );
    }

    public function registrarCierre()
    {
        if (!auth()->user()->can('crear cierres caja')) {
            abort(403, 'No tiene permiso para crear cierres de caja.');
        }

        $this->cargarAperturaAbierta();

        if (!$this->apertura_caja_id) {
            session()->flash('error', 'No hay una caja abierta. Primero debe registrar una apertura de caja.');
            return;
        }

        $this->validate([
            'fecha' => 'required|date',
            'monto_inicial' => 'required|numeric|min:0',
            'efectivo_contado' => 'required|numeric|min:0',
            'otros_ingresos' => 'nullable|numeric|min:0',
            'otros_egresos' => 'nullable|numeric|min:0',
            'observacion' => 'nullable|max:500',
        ]);

        $apertura = AperturaCaja::where('estado', 'Abierta')
            ->findOrFail($this->apertura_caja_id);

        $this->fecha = $apertura->fecha;
        $this->monto_inicial = $apertura->monto_inicial;

        $existeCierre = CierreCaja::query()
            ->whereDate('fecha', $this->fecha)
            ->where('estado', '!=', 'Anulado')
            ->exists();

        if ($existeCierre) {
            session()->flash('error', 'Ya existe un cierre de caja activo para esta fecha. Si necesita corregirlo, primero debe anular el cierre existente.');
            return;
        }

        $this->cargarResumenCaja();

        DB::transaction(function () use ($apertura) {
            $apertura = AperturaCaja::lockForUpdate()->findOrFail($apertura->id);

            if ($apertura->estado !== 'Abierta') {
                throw new \Exception('La apertura de caja ya no está disponible para cierre.');
            }

            $datosAnterioresApertura = $apertura->toArray();

            $cierre = CierreCaja::create([
                'apertura_caja_id' => $apertura->id,

                'fecha' => $apertura->fecha,
                'user_id' => auth()->id(),

                'monto_inicial' => $apertura->monto_inicial,

                'ventas_efectivo' => $this->ventas_efectivo,
                'ventas_transferencia' => $this->ventas_transferencia,
                'ventas_tarjeta' => $this->ventas_tarjeta,
                'ventas_otros' => $this->ventas_otros,

                'total_ingresos_ventas' => $this->total_ingresos_ventas,

                'gastos_registrados' => $this->gastos_registrados,
                'pagos_proveedores' => $this->pagos_proveedores,

                'otros_ingresos' => $this->otros_ingresos,
                'otros_egresos' => $this->otros_egresos,

                'total_ingresos' => $this->total_ingresos,
                'total_egresos' => $this->total_egresos,

                'efectivo_esperado' => $this->efectivo_esperado,
                'efectivo_contado' => $this->efectivo_contado,
                'diferencia' => $this->diferencia,

                'cantidad_pagos_ventas' => $this->cantidad_pagos_ventas,
                'cantidad_gastos' => $this->cantidad_gastos,
                'cantidad_pagos_proveedores' => $this->cantidad_pagos_proveedores,

                'observacion' => $this->observacion,
                'estado' => 'Cerrado',
            ]);

            $apertura->update([
                'estado' => 'Cerrada',
            ]);

            BitacoraSistema::registrar(
                'Cierre de caja',
                'Registrar',
                'Registró el cierre de caja ' . $cierre->codigo . ' relacionado con la apertura ' . $apertura->codigo . '.',
                CierreCaja::class,
                $cierre->id,
                null,
                $cierre->fresh()->load(['usuario', 'aperturaCaja'])->toArray()
            );

            BitacoraSistema::registrar(
                'Apertura de caja',
                'Cerrar',
                'Cerró la apertura de caja ' . $apertura->codigo . ' mediante el cierre ' . $cierre->codigo . '.',
                AperturaCaja::class,
                $apertura->id,
                $datosAnterioresApertura,
                $apertura->fresh()->load(['usuario', 'cierreCaja'])->toArray()
            );
        });

        $this->resetFormulario();

        session()->flash('message', 'Cierre de caja registrado correctamente.');
    }

    public function verDetalle($id)
    {
        if (!auth()->user()->can('ver cierres caja')) {
            abort(403, 'No tiene permiso para ver cierres de caja.');
        }

        $this->cierreDetalle = CierreCaja::with([
            'usuario',
            'usuarioAnulacion',
        ])->findOrFail($id);

        $this->mostrarModalDetalle = true;
    }

    public function cerrarModalDetalle()
    {
        $this->mostrarModalDetalle = false;
        $this->cierreDetalle = null;
    }

    public function abrirAnular($id)
    {
        if (!auth()->user()->can('anular cierres caja')) {
            abort(403, 'No tiene permiso para anular cierres de caja.');
        }

        $cierre = CierreCaja::findOrFail($id);

        if ($cierre->estado === 'Anulado') {
            session()->flash('error', 'Este cierre de caja ya está anulado.');
            return;
        }

        $this->cierreAnularId = $cierre->id;
        $this->motivoAnulacion = '';
        $this->mostrarModalAnulacion = true;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function cerrarModalAnulacion()
    {
        $this->mostrarModalAnulacion = false;
        $this->cierreAnularId = null;
        $this->motivoAnulacion = '';

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function confirmarAnular()
    {
        if (!auth()->user()->can('anular cierres caja')) {
            abort(403, 'No tiene permiso para anular cierres de caja.');
        }

        $this->validate([
            'cierreAnularId' => 'required|exists:cierres_caja,id',
            'motivoAnulacion' => 'required|min:5|max:500',
        ]);

        $cierre = CierreCaja::findOrFail($this->cierreAnularId);

        if ($cierre->estado === 'Anulado') {
            session()->flash('error', 'Este cierre de caja ya está anulado.');
            return;
        }

        $datosAnteriores = $cierre->toArray();

        $cierre->update([
            'estado' => 'Anulado',
            'fecha_anulacion' => now(),
            'anulado_por' => auth()->id(),
            'motivo_anulacion' => $this->motivoAnulacion,
        ]);

        BitacoraSistema::registrar(
            'Cierre de caja',
            'Anular',
            'Anuló el cierre de caja ' . $cierre->codigo . '. Motivo: ' . $this->motivoAnulacion,
            CierreCaja::class,
            $cierre->id,
            $datosAnteriores,
            $cierre->fresh()->load(['usuario', 'usuarioAnulacion'])->toArray()
        );

        $this->cerrarModalAnulacion();

        session()->flash('message', 'Cierre de caja anulado correctamente.');
    }

    private function resetFormulario()
    {
        $this->cargarAperturaAbierta();

        $this->fecha = $this->apertura_caja_fecha ?: now()->format('Y-m-d');
        $this->monto_inicial = $this->apertura_caja_monto_inicial ?: 0;
        $this->efectivo_contado = 0;
        $this->otros_ingresos = 0;
        $this->otros_egresos = 0;
        $this->observacion = '';

        $this->cargarResumenCaja();

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        if (!auth()->user()->can('ver cierres caja')) {
            abort(403, 'No tiene permiso para ver cierres de caja.');
        }

        $cierres = CierreCaja::with(['usuario'])
            ->where(function ($query) {
                $query->where('codigo', 'like', '%' . $this->search . '%')
                    ->orWhere('fecha', 'like', '%' . $this->search . '%')
                    ->orWhere('estado', 'like', '%' . $this->search . '%');
            })
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.cierres-caja.cierre-caja-index', [
            'cierres' => $cierres,
        ]);
    }
}
