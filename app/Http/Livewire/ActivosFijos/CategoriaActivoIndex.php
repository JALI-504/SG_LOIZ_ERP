<?php

namespace App\Http\Livewire\ActivosFijos;

use App\Models\BitacoraSistema;
use App\Models\CategoriaActivo;
use Livewire\Component;
use Livewire\WithPagination;

class CategoriaActivoIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $filtroEstado = 'todos';
    public $perPage = 10;

    public $categoria_id;
    public $nombre;
    public $prefijo_codigo;
    public $descripcion;
    public $depreciable = true;
    public $vida_util_meses = 60;
    public $porcentaje_depreciacion_anual = 20;
    public $metodo_depreciacion = 'Linea recta';
    public $requiere_numero_serie = false;
    public $requiere_marca_modelo = false;
    public $requiere_responsable = false;
    public $activo = true;


    public $mostrarModal = false;

    protected function rules()
    {
        return [
            'nombre' => 'required|min:3|max:150',
            'descripcion' => 'nullable|max:1000',
            'depreciable' => 'boolean',
            'vida_util_meses' => 'required|integer|min:1|max:600',
            'porcentaje_depreciacion_anual' => 'required|numeric|min:0|max:100',
            'metodo_depreciacion' => 'required|max:80',
            'activo' => 'boolean',
            'prefijo_codigo' => 'required|max:10',
            'requiere_numero_serie' => 'boolean',
            'requiere_marca_modelo' => 'boolean',
            'requiere_responsable' => 'boolean',
        ];
    }

    public function mount()
    {
        if (!auth()->user()->can('ver categorias activos')) {
            abort(403, 'No tiene permiso para ver categorías de activos.');
        }
    }

    public function updatingSearch()
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

    public function crear()
    {
        if (!auth()->user()->can('crear categorias activos')) {
            abort(403, 'No tiene permiso para crear categorías de activos.');
        }

        $this->resetFormulario();
        $this->mostrarModal = true;
    }

    public function editar($categoriaId)
    {
        if (!auth()->user()->can('editar categorias activos')) {
            abort(403, 'No tiene permiso para editar categorías de activos.');
        }

        $categoria = CategoriaActivo::findOrFail($categoriaId);

        $this->categoria_id = $categoria->id;
        $this->nombre = $categoria->nombre;
        $this->prefijo_codigo = $categoria->prefijo_codigo;
        $this->descripcion = $categoria->descripcion;
        $this->depreciable = (bool) $categoria->depreciable;
        $this->vida_util_meses = $categoria->vida_util_meses;
        $this->porcentaje_depreciacion_anual = $categoria->porcentaje_depreciacion_anual;
        $this->metodo_depreciacion = $categoria->metodo_depreciacion;
        $this->requiere_numero_serie = (bool) $categoria->requiere_numero_serie;
        $this->requiere_marca_modelo = (bool) $categoria->requiere_marca_modelo;
        $this->requiere_responsable = (bool) $categoria->requiere_responsable;
        $this->activo = (bool) $categoria->activo;

        $this->mostrarModal = true;
    }

    public function guardar()
    {
        if ($this->categoria_id) {
            return $this->actualizar();
        }

        return $this->store();
    }

    public function store()
    {
        if (!auth()->user()->can('crear categorias activos')) {
            abort(403, 'No tiene permiso para crear categorías de activos.');
        }

        $this->validate();

        $categoria = CategoriaActivo::create([
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'depreciable' => $this->depreciable ? true : false,
            'vida_util_meses' => $this->vida_util_meses,
            'porcentaje_depreciacion_anual' => $this->porcentaje_depreciacion_anual,
            'metodo_depreciacion' => $this->metodo_depreciacion,
            'activo' => $this->activo ? true : false,
            'prefijo_codigo' => strtoupper(trim($this->prefijo_codigo)),
            'requiere_numero_serie' => $this->requiere_numero_serie ? true : false,
            'requiere_marca_modelo' => $this->requiere_marca_modelo ? true : false,
            'requiere_responsable' => $this->requiere_responsable ? true : false,
            'user_id' => auth()->id(),
        ]);

        BitacoraSistema::registrar(
            'Categorías de activos',
            'Registrar',
            'Registró la categoría de activo ' . $categoria->nombre . '.',
            CategoriaActivo::class,
            $categoria->id,
            null,
            $categoria->fresh()->toArray()
        );

        $this->cerrarModal();

        session()->flash('message', 'Categoría registrada correctamente.');
    }

    public function actualizar()
    {
        if (!auth()->user()->can('editar categorias activos')) {
            abort(403, 'No tiene permiso para actualizar categorías de activos.');
        }

        $this->validate();

        $categoria = CategoriaActivo::findOrFail($this->categoria_id);

        $datosAnteriores = $categoria->toArray();

        $categoria->update([
            'nombre' => $this->nombre,
            'prefijo_codigo' => strtoupper(trim($this->prefijo_codigo)),
            'descripcion' => $this->descripcion,
            'depreciable' => $this->depreciable ? true : false,
            'vida_util_meses' => $this->vida_util_meses,
            'porcentaje_depreciacion_anual' => $this->porcentaje_depreciacion_anual,
            'metodo_depreciacion' => $this->metodo_depreciacion,
            'requiere_numero_serie' => $this->requiere_numero_serie ? true : false,
            'requiere_marca_modelo' => $this->requiere_marca_modelo ? true : false,
            'requiere_responsable' => $this->requiere_responsable ? true : false,
            'activo' => $this->activo ? true : false,
        ]);

        BitacoraSistema::registrar(
            'Categorías de activos',
            'Actualizar',
            'Actualizó la categoría de activo ' . $categoria->nombre . '.',
            CategoriaActivo::class,
            $categoria->id,
            $datosAnteriores,
            $categoria->fresh()->toArray()
        );

        $this->cerrarModal();

        session()->flash('message', 'Categoría actualizada correctamente.');
    }

    public function cambiarEstado($categoriaId)
    {
        if (!auth()->user()->can('eliminar categorias activos')) {
            abort(403, 'No tiene permiso para activar o desactivar categorías de activos.');
        }

        $categoria = CategoriaActivo::findOrFail($categoriaId);

        $datosAnteriores = $categoria->toArray();

        $categoria->update([
            'activo' => !$categoria->activo,
        ]);

        BitacoraSistema::registrar(
            'Categorías de activos',
            $categoria->activo ? 'Activar' : 'Desactivar',
            ($categoria->activo ? 'Activó' : 'Desactivó') . ' la categoría de activo ' . $categoria->nombre . '.',
            CategoriaActivo::class,
            $categoria->id,
            $datosAnteriores,
            $categoria->fresh()->toArray()
        );

        session()->flash('message', 'Estado de la categoría actualizado correctamente.');
    }

    public function cerrarModal()
    {
        $this->resetFormulario();
        $this->mostrarModal = false;
    }

    private function resetFormulario()
    {
        $this->categoria_id = null;
        $this->nombre = null;
        $this->descripcion = null;
        $this->depreciable = true;
        $this->vida_util_meses = 60;
        $this->porcentaje_depreciacion_anual = 20;
        $this->metodo_depreciacion = 'Linea recta';
        $this->prefijo_codigo = null;
        $this->requiere_numero_serie = false;
        $this->requiere_marca_modelo = false;
        $this->requiere_responsable = false;
        $this->activo = true;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $query = CategoriaActivo::query()
            ->when($this->search, function ($query) {
                $search = '%' . $this->search . '%';

                $query->where(function ($q) use ($search) {
                    $q->where('codigo', 'like', $search)
                        ->orWhere('prefijo_codigo', 'like', $search)
                        ->orWhere('nombre', 'like', $search)
                        ->orWhere('descripcion', 'like', $search)
                        ->orWhere('metodo_depreciacion', 'like', $search);
                });
            })
            ->when($this->filtroEstado !== 'todos', function ($query) {
                if ($this->filtroEstado === 'activas') {
                    $query->where('activo', true);
                }

                if ($this->filtroEstado === 'inactivas') {
                    $query->where('activo', false);
                }
            });

        $totalCategorias = (clone $query)->count();
        $totalActivas = (clone $query)->where('activo', true)->count();
        $totalInactivas = (clone $query)->where('activo', false)->count();

        $categorias = $query
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.activos-fijos.categoria-activo-index', [
            'categorias' => $categorias,
            'totalCategorias' => $totalCategorias,
            'totalActivas' => $totalActivas,
            'totalInactivas' => $totalInactivas,
        ]);
    }
}
