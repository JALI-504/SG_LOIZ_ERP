<?php

namespace App\Http\Livewire\ActivosFijos;

use App\Models\ActivoFijo;
use App\Models\CategoriaActivo;
use Livewire\Component;
use Livewire\WithPagination;

class ReporteActivoFijoIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $categoria_id = 'todos';
    public $estado = 'todos';
    public $fecha_desde;
    public $fecha_hasta;
    public $perPage = 10;

    public function mount()
    {
        if (!auth()->user()->can('ver activos fijos')) {
            abort(403, 'No tiene permiso para ver reportes de activos fijos.');
        }
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingCategoriaId()
    {
        $this->resetPage();
    }

    public function updatingEstado()
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

    public function limpiarFiltros()
    {
        $this->search = '';
        $this->categoria_id = 'todos';
        $this->estado = 'todos';
        $this->fecha_desde = null;
        $this->fecha_hasta = null;
        $this->perPage = 10;

        $this->resetPage();
    }

    private function queryActivos()
    {
        return ActivoFijo::with('categoriaActivo')
            ->when($this->search, function ($query) {
                $search = '%' . $this->search . '%';

                $query->where(function ($q) use ($search) {
                    $q->where('codigo', 'like', $search)
                        ->orWhere('nombre', 'like', $search)
                        ->orWhere('descripcion', 'like', $search)
                        ->orWhere('numero_serie', 'like', $search)
                        ->orWhere('marca', 'like', $search)
                        ->orWhere('modelo', 'like', $search)
                        ->orWhere('responsable', 'like', $search)
                        ->orWhere('ubicacion', 'like', $search)
                        ->orWhere('proveedor', 'like', $search)
                        ->orWhere('documento_compra', 'like', $search)
                        ->orWhere('documento_baja', 'like', $search)
                        ->orWhere('motivo_baja', 'like', $search);
                });
            })
            ->when($this->categoria_id !== 'todos', function ($query) {
                $query->where('categoria_activo_id', $this->categoria_id);
            })
            ->when($this->estado !== 'todos', function ($query) {
                $query->where('estado', $this->estado);
            })
            ->when($this->fecha_desde, function ($query) {
                $query->whereDate('fecha_compra', '>=', $this->fecha_desde);
            })
            ->when($this->fecha_hasta, function ($query) {
                $query->whereDate('fecha_compra', '<=', $this->fecha_hasta);
            });
    }

    public function exportarExcel()
    {
        if (!auth()->user()->can('ver activos fijos')) {
            abort(403, 'No tiene permiso para exportar reportes de activos fijos.');
        }

        $nombreArchivo = 'reporte_activos_fijos_' . now()->format('Ymd_His') . '.xls';

        $activos = $this->queryActivos()
            ->orderBy('categoria_activo_id')
            ->orderBy('codigo')
            ->get();

        $totalActivos = $activos->count();

        $totalVigentes = $activos->filter(function ($activo) {
            return !in_array($activo->estado, ['Dado de baja', 'Vendido']);
        })->count();

        $totalValorCompra = $activos->filter(function ($activo) {
            return !in_array($activo->estado, ['Dado de baja', 'Vendido']);
        })->sum('valor_compra');

        $totalDepreciacion = $activos->filter(function ($activo) {
            return !in_array($activo->estado, ['Dado de baja', 'Vendido']);
        })->sum('depreciacion_acumulada');

        $totalValorLibros = $activos->filter(function ($activo) {
            return !in_array($activo->estado, ['Dado de baja', 'Vendido']);
        })->sum('valor_en_libros');

        $totalRecuperado = $activos->sum('valor_recuperado');

        $filtroCategoria = 'Todas';
        if ($this->categoria_id !== 'todos') {
            $categoria = \App\Models\CategoriaActivo::find($this->categoria_id);
            $filtroCategoria = $categoria ? $categoria->nombre : 'Todas';
        }

        $filtroEstado = $this->estado !== 'todos' ? $this->estado : 'Todos';

        return response()->streamDownload(function () use (
            $activos,
            $totalActivos,
            $totalVigentes,
            $totalValorCompra,
            $totalDepreciacion,
            $totalValorLibros,
            $totalRecuperado,
            $filtroCategoria,
            $filtroEstado
        ) {
            echo '<html>';
            echo '<head>';
            echo '<meta charset="UTF-8">';
            echo '<style>
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
                color: #111827;
            }

            .titulo {
                background-color: #1f2937;
                color: #ffffff;
                font-size: 20px;
                font-weight: bold;
                text-align: center;
                padding: 12px;
            }

            .subtitulo {
                background-color: #e5e7eb;
                font-weight: bold;
                padding: 8px;
            }

            .info {
                background-color: #f9fafb;
                padding: 6px;
            }

            .resumen-titulo {
                background-color: #334155;
                color: #ffffff;
                font-weight: bold;
                text-align: center;
            }

            .resumen-label {
                background-color: #e0f2fe;
                font-weight: bold;
            }

            .resumen-valor {
                background-color: #f8fafc;
                font-weight: bold;
            }

            table {
                border-collapse: collapse;
                width: 100%;
            }

            th {
                background-color: #1f2937;
                color: #ffffff;
                font-weight: bold;
                text-align: center;
                border: 1px solid #9ca3af;
                padding: 6px;
            }

            td {
                border: 1px solid #d1d5db;
                padding: 5px;
                vertical-align: top;
            }

            .money {
                text-align: right;
            }

            .center {
                text-align: center;
            }

            .estado-activo {
                background-color: #dcfce7;
                color: #166534;
                font-weight: bold;
            }

            .estado-baja {
                background-color: #e5e7eb;
                color: #374151;
                font-weight: bold;
            }

            .estado-vendido {
                background-color: #dbeafe;
                color: #1d4ed8;
                font-weight: bold;
            }

            .estado-danado {
                background-color: #fee2e2;
                color: #991b1b;
                font-weight: bold;
            }
        </style>';
            echo '</head>';
            echo '<body>';

            echo '<table>';
            echo '<tr><td colspan="25" class="titulo">REPORTE DE ACTIVOS FIJOS</td></tr>';
            echo '<tr><td colspan="25" class="info"><strong>Generado:</strong> ' . now()->format('d/m/Y H:i:s') . '</td></tr>';
            echo '<tr><td colspan="25" class="info"><strong>Categoría:</strong> ' . e($filtroCategoria) . ' | <strong>Estado:</strong> ' . e($filtroEstado) . '</td></tr>';
            echo '<tr><td colspan="25" class="info"><strong>Búsqueda:</strong> ' . e($this->search ?: 'Sin búsqueda') . ' | <strong>Fecha desde:</strong> ' . e($this->fecha_desde ?: 'N/D') . ' | <strong>Fecha hasta:</strong> ' . e($this->fecha_hasta ?: 'N/D') . '</td></tr>';
            echo '</table>';

            echo '<br>';

            echo '<table>';
            echo '<tr><td colspan="6" class="resumen-titulo">RESUMEN GENERAL</td></tr>';
            echo '<tr>';
            echo '<td class="resumen-label">Total activos filtrados</td>';
            echo '<td class="resumen-valor center">' . number_format($totalActivos, 0) . '</td>';
            echo '<td class="resumen-label">Activos vigentes</td>';
            echo '<td class="resumen-valor center">' . number_format($totalVigentes, 0) . '</td>';
            echo '<td class="resumen-label">Valor recuperado</td>';
            echo '<td class="resumen-valor money">L ' . number_format($totalRecuperado, 2) . '</td>';
            echo '</tr>';

            echo '<tr>';
            echo '<td class="resumen-label">Valor compra vigente</td>';
            echo '<td class="resumen-valor money">L ' . number_format($totalValorCompra, 2) . '</td>';
            echo '<td class="resumen-label">Depreciación acumulada</td>';
            echo '<td class="resumen-valor money">L ' . number_format($totalDepreciacion, 2) . '</td>';
            echo '<td class="resumen-label">Valor en libros vigente</td>';
            echo '<td class="resumen-valor money">L ' . number_format($totalValorLibros, 2) . '</td>';
            echo '</tr>';
            echo '</table>';

            echo '<br>';

            echo '<table>';
            echo '<tr><td colspan="25" class="subtitulo">DETALLE DE ACTIVOS FIJOS</td></tr>';

            echo '<tr>';
            echo '<th>Código</th>';
            echo '<th>Activo</th>';
            echo '<th>Categoría</th>';
            echo '<th>Fecha compra</th>';
            echo '<th>Fecha inicio uso</th>';
            echo '<th>Número de serie</th>';
            echo '<th>Marca</th>';
            echo '<th>Modelo</th>';
            echo '<th>Responsable</th>';
            echo '<th>Ubicación</th>';
            echo '<th>Proveedor</th>';
            echo '<th>Documento compra</th>';
            echo '<th>Valor compra</th>';
            echo '<th>Valor residual</th>';
            echo '<th>Valor depreciable</th>';
            echo '<th>Dep. mensual</th>';
            echo '<th>Dep. acumulada</th>';
            echo '<th>Valor libros</th>';
            echo '<th>Estado</th>';
            echo '<th>Fecha retiro</th>';
            echo '<th>Tipo retiro</th>';
            echo '<th>Documento retiro</th>';
            echo '<th>Valor recuperado</th>';
            echo '<th>Motivo retiro</th>';
            echo '<th>Observación</th>';
            echo '</tr>';

            foreach ($activos as $activo) {
                $estadoClase = 'center';

                if ($activo->estado === 'Activo') {
                    $estadoClase = 'estado-activo center';
                }

                if ($activo->estado === 'Dado de baja') {
                    $estadoClase = 'estado-baja center';
                }

                if ($activo->estado === 'Vendido') {
                    $estadoClase = 'estado-vendido center';
                }

                if ($activo->estado === 'Dañado') {
                    $estadoClase = 'estado-danado center';
                }

                echo '<tr>';
                echo '<td>' . e($activo->codigo) . '</td>';
                echo '<td>' . e($activo->nombre) . '</td>';
                echo '<td>' . e($activo->categoriaActivo ? $activo->categoriaActivo->nombre : 'Sin categoría') . '</td>';
                echo '<td class="center">' . e($activo->fecha_compra) . '</td>';
                echo '<td class="center">' . e($activo->fecha_inicio_uso) . '</td>';
                echo '<td>' . e($activo->numero_serie) . '</td>';
                echo '<td>' . e($activo->marca) . '</td>';
                echo '<td>' . e($activo->modelo) . '</td>';
                echo '<td>' . e($activo->responsable) . '</td>';
                echo '<td>' . e($activo->ubicacion) . '</td>';
                echo '<td>' . e($activo->proveedor) . '</td>';
                echo '<td>' . e($activo->documento_compra) . '</td>';
                echo '<td class="money">L ' . number_format($activo->valor_compra, 2) . '</td>';
                echo '<td class="money">L ' . number_format($activo->valor_residual, 2) . '</td>';
                echo '<td class="money">L ' . number_format($activo->valor_depreciable, 2) . '</td>';
                echo '<td class="money">L ' . number_format($activo->depreciacion_mensual, 2) . '</td>';
                echo '<td class="money">L ' . number_format($activo->depreciacion_acumulada, 2) . '</td>';
                echo '<td class="money">L ' . number_format($activo->valor_en_libros, 2) . '</td>';
                echo '<td class="' . $estadoClase . '">' . e($activo->estado) . '</td>';
                echo '<td class="center">' . e($activo->fecha_baja) . '</td>';
                echo '<td>' . e($activo->tipo_baja) . '</td>';
                echo '<td>' . e($activo->documento_baja) . '</td>';
                echo '<td class="money">L ' . number_format($activo->valor_recuperado, 2) . '</td>';
                echo '<td>' . e($activo->motivo_baja) . '</td>';
                echo '<td>' . e($activo->observacion) . '</td>';
                echo '</tr>';
            }

            echo '</table>';

            echo '</body>';
            echo '</html>';
        }, $nombreArchivo, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $nombreArchivo . '"',
        ]);
    }
    
    public function render()
    {
        $query = $this->queryActivos();

        $totalActivos = (clone $query)->count();

        $totalActivosVigentes = (clone $query)
            ->whereNotIn('estado', ['Dado de baja', 'Vendido'])
            ->count();

        $totalBajas = (clone $query)
            ->where('estado', 'Dado de baja')
            ->count();

        $totalVendidos = (clone $query)
            ->where('estado', 'Vendido')
            ->count();

        $totalRobadosExtraviados = (clone $query)
            ->whereIn('tipo_baja', ['Robado', 'Extraviado'])
            ->count();

        $valorCompraTotal = (clone $query)
            ->whereNotIn('estado', ['Dado de baja', 'Vendido'])
            ->sum('valor_compra');

        $depreciacionAcumuladaTotal = (clone $query)
            ->whereNotIn('estado', ['Dado de baja', 'Vendido'])
            ->sum('depreciacion_acumulada');

        $valorLibrosTotal = (clone $query)
            ->whereNotIn('estado', ['Dado de baja', 'Vendido'])
            ->sum('valor_en_libros');

        $valorRecuperadoTotal = (clone $query)
            ->whereIn('estado', ['Dado de baja', 'Vendido'])
            ->sum('valor_recuperado');

        $activos = $query
            ->orderBy('categoria_activo_id')
            ->orderBy('codigo')
            ->paginate($this->perPage);

        $categorias = CategoriaActivo::orderBy('nombre')->get();

        $resumenCategorias = ActivoFijo::selectRaw('
                categoria_activo_id,
                COUNT(*) as cantidad,
                SUM(valor_compra) as valor_compra,
                SUM(depreciacion_acumulada) as depreciacion_acumulada,
                SUM(valor_en_libros) as valor_en_libros
            ')
            ->with('categoriaActivo')
            ->whereNotIn('estado', ['Dado de baja', 'Vendido'])
            ->groupBy('categoria_activo_id')
            ->orderBy('categoria_activo_id')
            ->get();

        return view('livewire.activos-fijos.reporte-activo-fijo-index', [
            'activos' => $activos,
            'categorias' => $categorias,
            'resumenCategorias' => $resumenCategorias,
            'totalActivos' => $totalActivos,
            'totalActivosVigentes' => $totalActivosVigentes,
            'totalBajas' => $totalBajas,
            'totalVendidos' => $totalVendidos,
            'totalRobadosExtraviados' => $totalRobadosExtraviados,
            'valorCompraTotal' => $valorCompraTotal,
            'depreciacionAcumuladaTotal' => $depreciacionAcumuladaTotal,
            'valorLibrosTotal' => $valorLibrosTotal,
            'valorRecuperadoTotal' => $valorRecuperadoTotal,
        ]);
    }
}
