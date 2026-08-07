<?php

namespace App\Http\Livewire\ActivosFijos;

use App\Models\ActivoFijo;
use App\Models\BitacoraSistema;
use App\Models\DepreciacionActivo;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class DepreciacionActivoIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $filtroPeriodo = '';
    public $filtroEstado = 'todos';
    public $perPage = 10;

    public $periodo;
    public $fecha_depreciacion;
    public $observacion;

    public $mostrarModalAnular = false;
    public $depreciacion_anular_id;
    public $motivo_anulacion;

    protected function rules()
    {
        return [
            'periodo' => 'required|regex:/^\d{4}\-\d{2}$/',
            'fecha_depreciacion' => 'required|date',
            'observacion' => 'nullable|max:1000',
        ];
    }

    protected $messages = [
        'periodo.required' => 'Debe seleccionar el período.',
        'periodo.regex' => 'El período debe tener el formato año-mes, por ejemplo 2026-08.',
        'fecha_depreciacion.required' => 'Debe ingresar la fecha de depreciación.',
    ];

    public function mount()
    {
        if (!auth()->user()->can('ver depreciaciones activos')) {
            abort(403, 'No tiene permiso para ver depreciaciones de activos.');
        }

        $this->periodo = now()->format('Y-m');
        $this->filtroPeriodo = now()->format('Y-m');
        $this->fecha_depreciacion = now()->format('Y-m-d');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroPeriodo()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function updatingPerPage()
    {
        $this->resetPage();
    }

    public function generarDepreciaciones()
    {
        if (!auth()->user()->can('generar depreciaciones activos')) {
            abort(403, 'No tiene permiso para generar depreciaciones de activos.');
        }

        $this->validate();

        try {
            DB::transaction(function () {
                $fechaInicioPeriodo = Carbon::createFromFormat('Y-m', $this->periodo)->startOfMonth();
                $fechaFinPeriodo = Carbon::createFromFormat('Y-m', $this->periodo)->endOfMonth();

                $activos = ActivoFijo::with('categoriaActivo')
                    ->whereNotIn('estado', ['Dado de baja', 'Vendido'])
                    ->where('depreciacion_mensual', '>', 0)
                    ->whereColumn('valor_en_libros', '>', 'valor_residual')
                    ->where(function ($query) use ($fechaFinPeriodo) {
                        $query->whereNull('fecha_inicio_uso')
                            ->orWhereDate('fecha_inicio_uso', '<=', $fechaFinPeriodo->format('Y-m-d'));
                    })
                    ->whereHas('categoriaActivo', function ($query) {
                        $query->where('depreciable', true);
                    })
                    ->whereDoesntHave('depreciaciones', function ($query) {
                        $query->where('periodo', $this->periodo);
                    })
                    ->lockForUpdate()
                    ->get();

                if ($activos->count() <= 0) {
                    throw new \Exception('No hay activos pendientes de depreciar para este período.');
                }

                $cantidadGenerada = 0;
                $totalGenerado = 0;

                foreach ($activos as $activo) {
                    $depreciacionMensual = (float) $activo->depreciacion_mensual;
                    $valorEnLibrosActual = (float) $activo->valor_en_libros;
                    $valorResidual = (float) $activo->valor_residual;

                    $montoMaximoDepreciable = $valorEnLibrosActual - $valorResidual;

                    if ($montoMaximoDepreciable <= 0) {
                        continue;
                    }

                    $montoDepreciacion = $depreciacionMensual;

                    if ($montoDepreciacion > $montoMaximoDepreciable) {
                        $montoDepreciacion = $montoMaximoDepreciable;
                    }

                    if ($montoDepreciacion <= 0) {
                        continue;
                    }

                    $depreciacionAcumuladaAnterior = (float) $activo->depreciacion_acumulada;
                    $valorEnLibrosAnterior = (float) $activo->valor_en_libros;

                    $depreciacionAcumuladaNueva = $depreciacionAcumuladaAnterior + $montoDepreciacion;
                    $valorEnLibrosNuevo = $valorEnLibrosAnterior - $montoDepreciacion;

                    if ($valorEnLibrosNuevo < $valorResidual) {
                        $valorEnLibrosNuevo = $valorResidual;
                    }

                    $depreciacion = DepreciacionActivo::create([
                        'activo_fijo_id' => $activo->id,
                        'periodo' => $this->periodo,
                        'fecha_depreciacion' => $this->fecha_depreciacion,
                        'monto' => round($montoDepreciacion, 2),
                        'depreciacion_acumulada_anterior' => round($depreciacionAcumuladaAnterior, 2),
                        'depreciacion_acumulada_nueva' => round($depreciacionAcumuladaNueva, 2),
                        'valor_en_libros_anterior' => round($valorEnLibrosAnterior, 2),
                        'valor_en_libros_nuevo' => round($valorEnLibrosNuevo, 2),
                        'estado' => 'Registrada',
                        'observacion' => $this->observacion,
                        'user_id' => auth()->id(),
                    ]);

                    $datosAnterioresActivo = $activo->toArray();

                    $activo->update([
                        'depreciacion_acumulada' => round($depreciacionAcumuladaNueva, 2),
                        'valor_en_libros' => round($valorEnLibrosNuevo, 2),
                    ]);

                    BitacoraSistema::registrar(
                        'Depreciaciones de activos',
                        'Generar',
                        'Generó depreciación del activo ' . $activo->codigo . ' por L ' . number_format($montoDepreciacion, 2) . ' en el período ' . $this->periodo . '.',
                        DepreciacionActivo::class,
                        $depreciacion->id,
                        null,
                        $depreciacion->fresh()->load(['activoFijo', 'usuario'])->toArray()
                    );

                    BitacoraSistema::registrar(
                        'Activos fijos',
                        'Actualizar',
                        'Actualizó depreciación acumulada y valor en libros del activo ' . $activo->codigo . '.',
                        ActivoFijo::class,
                        $activo->id,
                        $datosAnterioresActivo,
                        $activo->fresh()->load(['categoriaActivo', 'usuario'])->toArray()
                    );

                    $cantidadGenerada++;
                    $totalGenerado += $montoDepreciacion;
                }

                if ($cantidadGenerada <= 0) {
                    throw new \Exception('No se generó depreciación porque los activos ya llegaron a su valor residual.');
                }

                session()->flash(
                    'message',
                    'Depreciaciones generadas correctamente. Activos depreciados: ' .
                        $cantidadGenerada .
                        '. Total depreciado: L ' .
                        number_format($totalGenerado, 2)
                );
            });

            $this->observacion = null;
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function abrirAnular($depreciacionId)
    {
        if (!auth()->user()->can('anular depreciaciones activos')) {
            abort(403, 'No tiene permiso para anular depreciaciones de activos.');
        }

        $depreciacion = DepreciacionActivo::findOrFail($depreciacionId);

        if ($depreciacion->estado === 'Anulada') {
            session()->flash('error', 'Esta depreciación ya está anulada.');
            return;
        }

        $this->depreciacion_anular_id = $depreciacion->id;
        $this->motivo_anulacion = null;
        $this->mostrarModalAnular = true;
    }

    public function confirmarAnular()
    {
        if (!auth()->user()->can('anular depreciaciones activos')) {
            abort(403, 'No tiene permiso para anular depreciaciones de activos.');
        }

        try {
            DB::transaction(function () {
                $depreciacion = DepreciacionActivo::with('activoFijo')
                    ->where('estado', 'Registrada')
                    ->lockForUpdate()
                    ->findOrFail($this->depreciacion_anular_id);

                $activo = ActivoFijo::lockForUpdate()->findOrFail($depreciacion->activo_fijo_id);

                $ultimaDepreciacion = DepreciacionActivo::where('activo_fijo_id', $activo->id)
                    ->where('estado', 'Registrada')
                    ->orderByDesc('periodo')
                    ->orderByDesc('id')
                    ->first();

                if (!$ultimaDepreciacion || $ultimaDepreciacion->id !== $depreciacion->id) {
                    throw new \Exception('Solo se puede anular la última depreciación registrada de este activo.');
                }

                $datosAnterioresDepreciacion = $depreciacion->toArray();
                $datosAnterioresActivo = $activo->toArray();

                $depreciacion->update([
                    'estado' => 'Anulada',
                    'fecha_anulacion' => now(),
                    'anulado_por' => auth()->id(),
                    'motivo_anulacion' => $this->motivo_anulacion,
                ]);

                $activo->update([
                    'depreciacion_acumulada' => $depreciacion->depreciacion_acumulada_anterior,
                    'valor_en_libros' => $depreciacion->valor_en_libros_anterior,
                ]);

                BitacoraSistema::registrar(
                    'Depreciaciones de activos',
                    'Anular',
                    'Anuló la depreciación ' . $depreciacion->codigo . ' del activo ' . ($activo->codigo ?? 'N/D') . '.',
                    DepreciacionActivo::class,
                    $depreciacion->id,
                    $datosAnterioresDepreciacion,
                    $depreciacion->fresh()->load(['activoFijo', 'usuario', 'usuarioAnulacion'])->toArray()
                );

                BitacoraSistema::registrar(
                    'Activos fijos',
                    'Actualizar',
                    'Reversó depreciación acumulada y valor en libros del activo ' . ($activo->codigo ?? 'N/D') . '.',
                    ActivoFijo::class,
                    $activo->id,
                    $datosAnterioresActivo,
                    $activo->fresh()->load(['categoriaActivo', 'usuario'])->toArray()
                );
            });

            $this->cerrarModalAnular();

            session()->flash('message', 'Depreciación anulada correctamente y activo reversado.');
        } catch (\Exception $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function cerrarModalAnular()
    {
        $this->mostrarModalAnular = false;
        $this->depreciacion_anular_id = null;
        $this->motivo_anulacion = null;
    }

    public function limpiarFiltros()
    {
        $this->search = '';
        $this->filtroPeriodo = '';
        $this->filtroEstado = 'todos';
        $this->perPage = 10;
        $this->resetPage();
    }

    public function render()
    {
        $query = DepreciacionActivo::with(['activoFijo.categoriaActivo', 'usuario', 'usuarioAnulacion'])
            ->when($this->search, function ($query) {
                $search = '%' . $this->search . '%';

                $query->where(function ($q) use ($search) {
                    $q->where('codigo', 'like', $search)
                        ->orWhere('periodo', 'like', $search)
                        ->orWhere('observacion', 'like', $search)
                        ->orWhereHas('activoFijo', function ($activoQuery) use ($search) {
                            $activoQuery->where('codigo', 'like', $search)
                                ->orWhere('nombre', 'like', $search)
                                ->orWhere('numero_serie', 'like', $search)
                                ->orWhere('marca', 'like', $search)
                                ->orWhere('modelo', 'like', $search);
                        });
                });
            })
            ->when($this->filtroPeriodo, function ($query) {
                $query->where('periodo', $this->filtroPeriodo);
            })
            ->when($this->filtroEstado !== 'todos', function ($query) {
                $query->where('estado', $this->filtroEstado);
            });

        $totalRegistros = (clone $query)->count();

        $totalDepreciado = (clone $query)
            ->where('estado', 'Registrada')
            ->sum('monto');

        $totalAnulado = (clone $query)
            ->where('estado', 'Anulada')
            ->sum('monto');

        $cantidadAnuladas = (clone $query)
            ->where('estado', 'Anulada')
            ->count();

        $depreciaciones = $query
            ->orderByDesc('periodo')
            ->orderByDesc('id')
            ->paginate($this->perPage);

        $activosPendientesPeriodo = ActivoFijo::with('categoriaActivo')
            ->whereNotIn('estado', ['Dado de baja', 'Vendido'])
            ->where('depreciacion_mensual', '>', 0)
            ->whereColumn('valor_en_libros', '>', 'valor_residual')
            ->whereHas('categoriaActivo', function ($query) {
                $query->where('depreciable', true);
            })
            ->whereDoesntHave('depreciaciones', function ($query) {
                $query->where('periodo', $this->periodo);
            })
            ->count();

        return view('livewire.activos-fijos.depreciacion-activo-index', [
            'depreciaciones' => $depreciaciones,
            'totalRegistros' => $totalRegistros,
            'totalDepreciado' => $totalDepreciado,
            'totalAnulado' => $totalAnulado,
            'cantidadAnuladas' => $cantidadAnuladas,
            'activosPendientesPeriodo' => $activosPendientesPeriodo,
        ]);
    }
}
