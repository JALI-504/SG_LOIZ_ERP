<?php

namespace App\Http\Livewire\ActivosFijos;

use App\Models\ActivoFijo;
use App\Models\BitacoraSistema;
use App\Models\CategoriaActivo;
use Livewire\Component;
use Livewire\WithPagination;

class ActivoFijoIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $filtroCategoria = 'todos';
    public $filtroEstado = 'todos';
    public $perPage = 10;

    public $activo_id;
    public $categoria_activo_id;
    public $nombre;
    public $descripcion;
    public $fecha_compra;
    public $fecha_inicio_uso;
    public $valor_compra = 0;
    public $valor_residual = 0;
    public $vida_util_meses = 60;
    public $depreciacion_acumulada = 0;
    public $ubicacion;
    public $responsable;
    public $proveedor;
    public $documento_compra;
    public $numero_serie;
    public $marca;
    public $modelo;
    public $estado = 'Activo';
    public $observacion;

    public $mostrarModal = false;
    public $mostrarModalBaja = false;
    public $activo_baja_id;
    public $motivo_baja_form;

    public $categorias = [];

    protected function rules()
    {
        return [
            'categoria_activo_id' => 'required|exists:categorias_activos,id',
            'nombre' => 'required|min:3|max:180',
            'descripcion' => 'nullable|max:1000',
            'fecha_compra' => 'nullable|date',
            'fecha_inicio_uso' => 'nullable|date',
            'valor_compra' => 'required|numeric|min:0',
            'valor_residual' => 'required|numeric|min:0',
            'vida_util_meses' => 'required|integer|min:1|max:600',
            'depreciacion_acumulada' => 'required|numeric|min:0',
            'ubicacion' => 'nullable|max:150',
            'responsable' => 'nullable|max:150',
            'proveedor' => 'nullable|max:150',
            'documento_compra' => 'nullable|max:150',
            'numero_serie' => 'nullable|max:150',
            'marca' => 'nullable|max:100',
            'modelo' => 'nullable|max:100',
            'estado' => 'required|max:50',
            'observacion' => 'nullable|max:1000',
        ];
    }

    public function mount()
    {
        if (!auth()->user()->can('ver activos fijos')) {
            abort(403, 'No tiene permiso para ver activos fijos.');
        }

        $this->categorias = CategoriaActivo::where('activo', true)
            ->orderBy('nombre')
            ->get();

        $this->categoria_activo_id = optional($this->categorias->first())->id;
        $this->fecha_compra = now()->format('Y-m-d');
        $this->fecha_inicio_uso = now()->format('Y-m-d');

        $this->cargarDatosCategoria();
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroCategoria()
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

    public function updatedCategoriaActivoId()
    {
        $this->cargarDatosCategoria();
    }

    private function cargarDatosCategoria()
    {
        if (!$this->categoria_activo_id) {
            return;
        }

        $categoria = CategoriaActivo::find($this->categoria_activo_id);

        if (!$categoria) {
            return;
        }

        if ($categoria->depreciable) {
            $this->vida_util_meses = $categoria->vida_util_meses;
        } else {
            $this->vida_util_meses = 1;
            $this->valor_residual = $this->valor_compra;
            $this->depreciacion_acumulada = 0;
        }
    }

    public function crear()
    {
        if (!auth()->user()->can('crear activos fijos')) {
            abort(403, 'No tiene permiso para crear activos fijos.');
        }

        $this->resetFormulario();

        $this->mostrarModal = true;
    }

    public function editar($activoId)
    {
        if (!auth()->user()->can('editar activos fijos')) {
            abort(403, 'No tiene permiso para editar activos fijos.');
        }

        $activo = ActivoFijo::findOrFail($activoId);

        $this->activo_id = $activo->id;
        $this->categoria_activo_id = $activo->categoria_activo_id;
        $this->nombre = $activo->nombre;
        $this->descripcion = $activo->descripcion;
        $this->fecha_compra = $activo->fecha_compra;
        $this->fecha_inicio_uso = $activo->fecha_inicio_uso;
        $this->valor_compra = $activo->valor_compra;
        $this->valor_residual = $activo->valor_residual;
        $this->vida_util_meses = $activo->vida_util_meses;
        $this->depreciacion_acumulada = $activo->depreciacion_acumulada;
        $this->ubicacion = $activo->ubicacion;
        $this->responsable = $activo->responsable;
        $this->proveedor = $activo->proveedor;
        $this->documento_compra = $activo->documento_compra;
        $this->numero_serie = $activo->numero_serie;
        $this->marca = $activo->marca;
        $this->modelo = $activo->modelo;
        $this->estado = $activo->estado;
        $this->observacion = $activo->observacion;

        $this->mostrarModal = true;
    }

    public function guardar()
    {
        if ($this->activo_id) {
            return $this->actualizar();
        }

        return $this->store();
    }

    public function store()
    {
        if (!auth()->user()->can('crear activos fijos')) {
            abort(403, 'No tiene permiso para crear activos fijos.');
        }

        $this->validate();

        $datos = $this->prepararDatosActivo();

        $activo = ActivoFijo::create($datos);

        BitacoraSistema::registrar(
            'Activos fijos',
            'Registrar',
            'Registró el activo fijo ' . $activo->codigo . ' - ' . $activo->nombre . '.',
            ActivoFijo::class,
            $activo->id,
            null,
            $activo->fresh()->load(['categoriaActivo', 'usuario'])->toArray()
        );

        $this->cerrarModal();

        session()->flash('message', 'Activo fijo registrado correctamente.');
    }

    public function actualizar()
    {
        if (!auth()->user()->can('editar activos fijos')) {
            abort(403, 'No tiene permiso para actualizar activos fijos.');
        }

        $this->validate();

        $activo = ActivoFijo::findOrFail($this->activo_id);

        $datosAnteriores = $activo->toArray();

        $datos = $this->prepararDatosActivo();

        $activo->update($datos);

        BitacoraSistema::registrar(
            'Activos fijos',
            'Actualizar',
            'Actualizó el activo fijo ' . $activo->codigo . ' - ' . $activo->nombre . '.',
            ActivoFijo::class,
            $activo->id,
            $datosAnteriores,
            $activo->fresh()->load(['categoriaActivo', 'usuario'])->toArray()
        );

        $this->cerrarModal();

        session()->flash('message', 'Activo fijo actualizado correctamente.');
    }

    private function prepararDatosActivo()
    {
        $categoria = CategoriaActivo::findOrFail($this->categoria_activo_id);

        $valorCompra = (float) $this->valor_compra;
        $valorResidual = (float) $this->valor_residual;
        $vidaUtilMeses = (int) $this->vida_util_meses;
        $depreciacionAcumulada = (float) $this->depreciacion_acumulada;

        if (!$categoria->depreciable) {
            $valorResidual = $valorCompra;
            $vidaUtilMeses = 1;
            $depreciacionAcumulada = 0;
        }

        if ($valorResidual > $valorCompra) {
            $valorResidual = $valorCompra;
        }

        $valorDepreciable = $valorCompra - $valorResidual;

        if ($vidaUtilMeses > 0) {
            $depreciacionMensual = $valorDepreciable / $vidaUtilMeses;
        } else {
            $depreciacionMensual = 0;
        }

        if ($depreciacionAcumulada > $valorDepreciable) {
            $depreciacionAcumulada = $valorDepreciable;
        }

        $valorEnLibros = $valorCompra - $depreciacionAcumulada;

        if ($valorEnLibros < $valorResidual) {
            $valorEnLibros = $valorResidual;
        }

        return [
            'categoria_activo_id' => $this->categoria_activo_id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'fecha_compra' => $this->fecha_compra,
            'fecha_inicio_uso' => $this->fecha_inicio_uso,
            'valor_compra' => round($valorCompra, 2),
            'valor_residual' => round($valorResidual, 2),
            'valor_depreciable' => round($valorDepreciable, 2),
            'vida_util_meses' => $vidaUtilMeses,
            'depreciacion_mensual' => round($depreciacionMensual, 2),
            'depreciacion_acumulada' => round($depreciacionAcumulada, 2),
            'valor_en_libros' => round($valorEnLibros, 2),
            'ubicacion' => $this->ubicacion,
            'responsable' => $this->responsable,
            'proveedor' => $this->proveedor,
            'documento_compra' => $this->documento_compra,
            'numero_serie' => $this->numero_serie,
            'marca' => $this->marca,
            'modelo' => $this->modelo,
            'estado' => $this->estado,
            'observacion' => $this->observacion,
            'user_id' => auth()->id(),
        ];
    }

    public function abrirBaja($activoId)
    {
        if (!auth()->user()->can('anular activos fijos')) {
            abort(403, 'No tiene permiso para dar de baja activos fijos.');
        }

        $activo = ActivoFijo::findOrFail($activoId);

        if ($activo->estado === 'Dado de baja') {
            session()->flash('error', 'Este activo ya está dado de baja.');
            return;
        }

        $this->activo_baja_id = $activo->id;
        $this->motivo_baja_form = null;
        $this->mostrarModalBaja = true;
    }

    public function confirmarBaja()
    {
        if (!auth()->user()->can('anular activos fijos')) {
            abort(403, 'No tiene permiso para dar de baja activos fijos.');
        }

        $activo = ActivoFijo::findOrFail($this->activo_baja_id);

        $datosAnteriores = $activo->toArray();

        $activo->update([
            'estado' => 'Dado de baja',
            'fecha_baja' => now()->format('Y-m-d'),
            'motivo_baja' => $this->motivo_baja_form,
        ]);

        BitacoraSistema::registrar(
            'Activos fijos',
            'Dar de baja',
            'Dio de baja el activo fijo ' . $activo->codigo . ' - ' . $activo->nombre . '.',
            ActivoFijo::class,
            $activo->id,
            $datosAnteriores,
            $activo->fresh()->load(['categoriaActivo', 'usuario'])->toArray()
        );

        $this->cerrarModalBaja();

        session()->flash('message', 'Activo fijo dado de baja correctamente.');
    }

    public function reactivar($activoId)
    {
        if (!auth()->user()->can('anular activos fijos')) {
            abort(403, 'No tiene permiso para reactivar activos fijos.');
        }

        $activo = ActivoFijo::findOrFail($activoId);

        if ($activo->estado !== 'Dado de baja') {
            session()->flash('error', 'Solo se pueden reactivar activos dados de baja.');
            return;
        }

        $datosAnteriores = $activo->toArray();

        $activo->update([
            'estado' => 'Activo',
            'fecha_baja' => null,
            'motivo_baja' => null,
        ]);

        BitacoraSistema::registrar(
            'Activos fijos',
            'Reactivar',
            'Reactivó el activo fijo ' . $activo->codigo . ' - ' . $activo->nombre . '.',
            ActivoFijo::class,
            $activo->id,
            $datosAnteriores,
            $activo->fresh()->load(['categoriaActivo', 'usuario'])->toArray()
        );

        session()->flash('message', 'Activo fijo reactivado correctamente.');
    }

    public function cerrarModal()
    {
        $this->resetFormulario();
        $this->mostrarModal = false;
    }

    public function cerrarModalBaja()
    {
        $this->activo_baja_id = null;
        $this->motivo_baja_form = null;
        $this->mostrarModalBaja = false;
    }

    private function resetFormulario()
    {
        $this->activo_id = null;
        $this->categoria_activo_id = optional($this->categorias->first())->id;
        $this->nombre = null;
        $this->descripcion = null;
        $this->fecha_compra = now()->format('Y-m-d');
        $this->fecha_inicio_uso = now()->format('Y-m-d');
        $this->valor_compra = 0;
        $this->valor_residual = 0;
        $this->vida_util_meses = 60;
        $this->depreciacion_acumulada = 0;
        $this->ubicacion = null;
        $this->responsable = null;
        $this->proveedor = null;
        $this->documento_compra = null;
        $this->numero_serie = null;
        $this->marca = null;
        $this->modelo = null;
        $this->estado = 'Activo';
        $this->observacion = null;

        $this->cargarDatosCategoria();

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $query = ActivoFijo::with(['categoriaActivo', 'usuario'])
            ->when($this->search, function ($query) {
                $search = '%' . $this->search . '%';

                $query->where(function ($q) use ($search) {
                    $q->where('codigo', 'like', $search)
                        ->orWhere('nombre', 'like', $search)
                        ->orWhere('descripcion', 'like', $search)
                        ->orWhere('ubicacion', 'like', $search)
                        ->orWhere('responsable', 'like', $search)
                        ->orWhere('proveedor', 'like', $search)
                        ->orWhere('documento_compra', 'like', $search)
                        ->orWhere('numero_serie', 'like', $search)
                        ->orWhere('marca', 'like', $search)
                        ->orWhere('modelo', 'like', $search);
                });
            })
            ->when($this->filtroCategoria !== 'todos', function ($query) {
                $query->where('categoria_activo_id', $this->filtroCategoria);
            })
            ->when($this->filtroEstado !== 'todos', function ($query) {
                $query->where('estado', $this->filtroEstado);
            });

        $totalActivos = (clone $query)->count();

        $totalValorCompra = (clone $query)
            ->where('estado', '!=', 'Dado de baja')
            ->sum('valor_compra');

        $totalDepreciacionAcumulada = (clone $query)
            ->where('estado', '!=', 'Dado de baja')
            ->sum('depreciacion_acumulada');

        $totalValorLibros = (clone $query)
            ->where('estado', '!=', 'Dado de baja')
            ->sum('valor_en_libros');

        $totalBajas = (clone $query)
            ->where('estado', 'Dado de baja')
            ->count();

        $activos = $query
            ->orderByDesc('id')
            ->paginate($this->perPage);

        $categoriasFiltro = CategoriaActivo::orderBy('nombre')->get();

        return view('livewire.activos-fijos.activo-fijo-index', [
            'activos' => $activos,
            'categoriasFiltro' => $categoriasFiltro,
            'totalActivos' => $totalActivos,
            'totalValorCompra' => $totalValorCompra,
            'totalDepreciacionAcumulada' => $totalDepreciacionAcumulada,
            'totalValorLibros' => $totalValorLibros,
            'totalBajas' => $totalBajas,
        ]);
    }
}
