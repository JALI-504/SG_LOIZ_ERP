<?php

namespace App\Http\Livewire\Ventas;

use App\Models\Catalogo;
use App\Models\PagoVenta;
use App\Models\Venta;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class CuentasPorCobrar extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $fechaDesde;
    public $fechaHasta;
    public $perPage = 10;

    public $filtroComprobante = 'todos';

    public $ventaSeleccionadaId;

    public $monto_abono;
    public $metodo_pago = 'Efectivo';
    public $referencia;
    public $observacion;

    public $metodosPago = [];

    public function mount()
    {
        $this->metodosPago = Catalogo::opciones('metodo_pago')
            ->pluck('nombre')
            ->toArray();

        $this->metodo_pago = $this->metodosPago[0] ?? 'Efectivo';
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFechaDesde()
    {
        $this->resetPage();
    }

    public function updatingFechaHasta()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function updatingFiltroComprobante()
    {
        $this->resetPage();
    }

    private function queryCuentas()
    {
        return Venta::with(['cliente', 'pagos'])
            ->where('estado', '!=', 'Anulada')
            ->where('saldo_pendiente', '>', 0)
            ->when($this->search, function ($query) {
                $search = '%' . $this->search . '%';

                $query->where(function ($q) use ($search) {
                    $q->where('numero', 'like', $search)
                        ->orWhere('tipo_comprobante', 'like', $search)
                        ->orWhere('cai', 'like', $search)
                        ->orWhere('metodo_pago', 'like', $search)
                        ->orWhereHas('cliente', function ($clienteQuery) use ($search) {
                            $clienteQuery->where('primer_nombre', 'like', $search)
                                ->orWhere('segundo_nombre', 'like', $search)
                                ->orWhere('primer_apellido', 'like', $search)
                                ->orWhere('segundo_apellido', 'like', $search)
                                ->orWhere('dni', 'like', $search)
                                ->orWhere('rtn', 'like', $search)
                                ->orWhere('telefono', 'like', $search);
                        });
                });
            })
            ->when($this->fechaDesde, function ($query) {
                $query->whereDate('fecha', '>=', $this->fechaDesde);
            })
            ->when($this->fechaHasta, function ($query) {
                $query->whereDate('fecha', '<=', $this->fechaHasta);
            })
            ->when($this->filtroComprobante === 'fiscal', function ($query) {
                $query->where('es_fiscal', 1);
            })
            ->when($this->filtroComprobante === 'interno', function ($query) {
                $query->where('es_fiscal', 0);
            });
    }

    public function abrirAbono($ventaId)
    {
        $venta = Venta::findOrFail($ventaId);

        if ($venta->estado === 'Anulada') {
            session()->flash('error', 'No se puede registrar abono a una venta anulada.');
            return;
        }

        if ($venta->saldo_pendiente <= 0) {
            session()->flash('error', 'Esta venta ya está pagada.');
            return;
        }

        $this->ventaSeleccionadaId = $venta->id;
        $this->monto_abono = $venta->saldo_pendiente;
        $this->metodo_pago = $this->metodosPago[0] ?? 'Efectivo';
        $this->referencia = null;
        $this->observacion = null;

        $this->dispatchBrowserEvent('open-abono-modal');
    }

    public function registrarAbono()
    {
        $venta = Venta::with('pagos')->findOrFail($this->ventaSeleccionadaId);

        $this->validate([
            'monto_abono' => 'required|numeric|min:0.01|max:' . $venta->saldo_pendiente,
            'metodo_pago' => 'required|max:50',
            'referencia' => 'nullable|max:100',
            'observacion' => 'nullable|max:500',
        ]);

        try {
            DB::transaction(function () use ($venta) {
                PagoVenta::create([
                    'venta_id' => $venta->id,
                    'monto' => $this->monto_abono,
                    'metodo_pago' => $this->metodo_pago,
                    'referencia' => $this->referencia,
                    'observacion' => $this->observacion,
                ]);

                $totalPagosRecibidos = PagoVenta::where('venta_id', $venta->id)
                    ->sum('monto');

                $retencionAplicada = (float) ($venta->retencion ?? 0);

                $nuevoMontoPagado = (float) $totalPagosRecibidos + $retencionAplicada;

                if ($nuevoMontoPagado > (float) $venta->total) {
                    $nuevoMontoPagado = (float) $venta->total;
                }

                $nuevoSaldo = (float) $venta->total - $nuevoMontoPagado;

                if ($nuevoSaldo < 0) {
                    $nuevoSaldo = 0;
                }

                $venta->monto_pagado = $nuevoMontoPagado;
                $venta->saldo_pendiente = $nuevoSaldo;
                $venta->estado = $nuevoSaldo <= 0 ? 'Pagada' : 'Pendiente';
                $venta->save();
            });

            $this->resetFormularioAbono();

            $this->dispatchBrowserEvent('close-abono-modal');

            session()->flash('message', 'Abono registrado correctamente.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    private function resetFormularioAbono()
    {
        $this->ventaSeleccionadaId = null;
        $this->monto_abono = null;
        $this->metodo_pago = $this->metodosPago[0] ?? 'Efectivo';
        $this->referencia = null;
        $this->observacion = null;
    }

    public function render()
    {
        $query = $this->queryCuentas();

        $totalCuentas = (clone $query)->count();
        $totalPendiente = (clone $query)->sum('saldo_pendiente');
        $totalOriginal = (clone $query)->sum('total');
        $totalPagado = (clone $query)->sum('monto_pagado');

        $totalRetencion = (clone $query)->sum('retencion');

        $totalFacturasFiscales = (clone $query)
            ->where('es_fiscal', 1)
            ->count();

        $totalRecibosInternos = (clone $query)
            ->where('es_fiscal', 0)
            ->count();

        $ventas = $query
            ->orderBy('fecha')
            ->orderBy('id')
            ->paginate($this->perPage);

        $ventaSeleccionada = null;

        if ($this->ventaSeleccionadaId) {
            $ventaSeleccionada = Venta::with(['cliente', 'pagos'])
                ->find($this->ventaSeleccionadaId);
        }

        return view('livewire.ventas.cuentas-por-cobrar', [
            'ventas' => $ventas,
            'totalCuentas' => $totalCuentas,
            'totalPendiente' => $totalPendiente,
            'totalOriginal' => $totalOriginal,
            'totalPagado' => $totalPagado,
            'totalRetencion' => $totalRetencion,
            'totalFacturasFiscales' => $totalFacturasFiscales,
            'totalRecibosInternos' => $totalRecibosInternos,
            'ventaSeleccionada' => $ventaSeleccionada,
        ]);
    }
}
