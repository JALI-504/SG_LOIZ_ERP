<?php

namespace App\Http\Livewire\Roles;

use App\Models\BitacoraSistema;
use Livewire\Component;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Str;

class RolIndex extends Component
{
    public $rol_id;
    public $name;
    public $permisosSeleccionados = [];

    public $mostrarModalRol = false;
    public $modalTitle = 'Nuevo rol';

    public $search = '';

    private function autorizarVerRoles()
    {
        if (!auth()->user()->can('ver roles')) {
            abort(403, 'No tiene permiso para ver roles.');
        }
    }

    private function autorizarCrearRoles()
    {
        if (!auth()->user()->can('crear roles')) {
            abort(403, 'No tiene permiso para crear roles.');
        }
    }

    private function autorizarEditarRoles()
    {
        if (!auth()->user()->can('editar roles')) {
            abort(403, 'No tiene permiso para editar roles.');
        }
    }

    private function autorizarEliminarRoles()
    {
        if (!auth()->user()->can('eliminar roles')) {
            abort(403, 'No tiene permiso para eliminar roles.');
        }
    }

    private function autorizarAsignarPermisos()
    {
        if (!auth()->user()->can('asignar permisos roles')) {
            abort(403, 'No tiene permiso para asignar permisos a roles.');
        }
    }

    protected function rules()
    {
        return [
            'name' => 'required|min:3|max:255|unique:roles,name,' . $this->rol_id,
            'permisosSeleccionados' => 'array',
        ];
    }

    protected $messages = [
        'name.required' => 'El nombre del rol es obligatorio.',
        'name.min' => 'El nombre del rol debe tener al menos 3 caracteres.',
        'name.unique' => 'Ya existe un rol con ese nombre.',
    ];

    public function create()
    {
        $this->autorizarCrearRoles();
        $this->autorizarAsignarPermisos();

        $this->resetInput();

        $this->modalTitle = 'Nuevo rol';
        $this->mostrarModalRol = true;
    }

    public function store()
    {
        $this->autorizarCrearRoles();
        $this->autorizarAsignarPermisos();

        $this->validate();

        $rol = Role::create([
            'name' => trim($this->name),
        ]);

        $rol->syncPermissions($this->permisosSeleccionados);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $rolActualizado = $rol->fresh()->load('permissions');

        BitacoraSistema::registrar(
            'Roles y permisos',
            'Registrar',
            'Registró el rol ' . $rolActualizado->name . ' con ' . count($this->permisosSeleccionados) . ' permisos.',
            Role::class,
            $rolActualizado->id,
            null,
            $this->datosRolBitacora($rolActualizado)
        );

        $this->resetInput();
        $this->mostrarModalRol = false;

        session()->flash('message', 'Rol creado correctamente.');
    }

    public function edit($id)
    {
        $this->autorizarEditarRoles();
        $this->autorizarAsignarPermisos();

        $rol = Role::with('permissions')->findOrFail($id);

        if ($rol->name === 'Administrador') {
            session()->flash('error', 'El rol Administrador no se puede modificar desde esta pantalla.');
            return;
        }

        $this->rol_id = $rol->id;
        $this->name = $rol->name;
        $this->permisosSeleccionados = $rol->permissions->pluck('name')->toArray();

        $this->modalTitle = 'Editar rol';
        $this->mostrarModalRol = true;
    }

    public function update()
    {
        $this->autorizarEditarRoles();
        $this->autorizarAsignarPermisos();

        $this->validate();

        $rol = Role::with('permissions')->findOrFail($this->rol_id);

        if ($rol->name === 'Administrador') {
            session()->flash('error', 'El rol Administrador no se puede modificar desde esta pantalla.');
            return;
        }

        $datosAnteriores = $this->datosRolBitacora($rol);

        $nombreAnterior = $rol->name;

        $permisosAnteriores = $rol->permissions
            ->pluck('name')
            ->sort()
            ->values()
            ->toArray();

        $rol->update([
            'name' => trim($this->name),
        ]);

        $rol->syncPermissions($this->permisosSeleccionados);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $rolActualizado = $rol->fresh()->load('permissions');

        $permisosNuevos = $rolActualizado->permissions
            ->pluck('name')
            ->sort()
            ->values()
            ->toArray();

        BitacoraSistema::registrar(
            'Roles y permisos',
            'Actualizar',
            'Actualizó el rol ' . $nombreAnterior . ' a ' . $rolActualizado->name . '. Permisos anteriores: ' . count($permisosAnteriores) . '. Permisos nuevos: ' . count($permisosNuevos) . '.',
            Role::class,
            $rolActualizado->id,
            $datosAnteriores,
            $this->datosRolBitacora($rolActualizado)
        );

        $this->resetInput();
        $this->mostrarModalRol = false;

        session()->flash('message', 'Rol actualizado correctamente.');
    }

    public function eliminar($id)
    {
        $this->autorizarEliminarRoles();

        $rol = Role::with('permissions')->findOrFail($id);

        if ($rol->name === 'Administrador') {
            session()->flash('error', 'El rol Administrador no se puede eliminar.');
            return;
        }

        if (in_array($rol->name, ['Cajero', 'Inventario', 'Reportes'])) {
            session()->flash('error', 'Este rol base del sistema no se puede eliminar.');
            return;
        }

        if ($rol->users()->count() > 0) {
            session()->flash('error', 'No se puede eliminar un rol que tiene usuarios asignados.');
            return;
        }

        $datosAnteriores = $this->datosRolBitacora($rol);

        $rolId = $rol->id;
        $rolNombre = $rol->name;

        $rol->delete();

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        BitacoraSistema::registrar(
            'Roles y permisos',
            'Eliminar',
            'Eliminó el rol ' . $rolNombre . '.',
            Role::class,
            $rolId,
            $datosAnteriores,
            null
        );

        session()->flash('message', 'Rol eliminado correctamente.');
    }

    public function cerrarModalRol()
    {
        $this->mostrarModalRol = false;
        $this->resetInput();
    }

    private function datosRolBitacora($rol)
    {
        if (!$rol->relationLoaded('permissions')) {
            $rol->load('permissions');
        }

        return [
            'id' => $rol->id,
            'name' => $rol->name,
            'guard_name' => $rol->guard_name,
            'permisos' => $rol->permissions
                ->pluck('name')
                ->sort()
                ->values()
                ->toArray(),
        ];
    }

    private function resetInput()
    {
        $this->rol_id = null;
        $this->name = '';
        $this->permisosSeleccionados = [];

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $this->autorizarVerRoles();

        $roles = Role::with('permissions')
            ->where('name', 'like', '%' . $this->search . '%')
            ->orderBy('name')
            ->get();

        $permisos = Permission::orderBy('name')->get();

        $permisosAgrupados = [
            'Dashboard' => $permisos->filter(function ($permiso) {
                return Str::contains($permiso->name, 'dashboard');
            }),

            'Ventas' => $permisos->filter(function ($permiso) {
                return Str::contains($permiso->name, 'ventas')
                    || Str::contains($permiso->name, 'cuentas por cobrar')
                    || Str::contains($permiso->name, 'abonos clientes')
                    || Str::contains($permiso->name, 'recibos ventas');
            }),

            'Clientes' => $permisos->filter(function ($permiso) {
                return Str::contains($permiso->name, 'clientes');
            }),

            'Productos' => $permisos->filter(function ($permiso) {
                return Str::contains($permiso->name, 'productos');
            }),

            'Servicios' => $permisos->filter(function ($permiso) {
                return Str::contains($permiso->name, 'servicios');
            }),

            'Insumos e inventario' => $permisos->filter(function ($permiso) {
                return Str::contains($permiso->name, 'insumos')
                    || Str::contains($permiso->name, 'inventario');
            }),

            'Producción' => $permisos->filter(function ($permiso) {
                return Str::contains($permiso->name, 'produccion');
            }),

            'Compras y cuentas por pagar' => $permisos->filter(function ($permiso) {
                return Str::contains($permiso->name, 'compras')
                    || Str::contains($permiso->name, 'cuentas por pagar')
                    || Str::contains($permiso->name, 'pagos proveedores');
            }),

            'Proveedores' => $permisos->filter(function ($permiso) {
                return Str::contains($permiso->name, 'proveedores');
            }),

            'Gastos' => $permisos->filter(function ($permiso) {
                return Str::contains($permiso->name, 'gastos');
            }),

            'Reportes' => $permisos->filter(function ($permiso) {
                return Str::contains($permiso->name, 'reporte')
                    || $permiso->name === 'ver reportes';
            }),

            'Usuarios' => $permisos->filter(function ($permiso) {
                return Str::contains($permiso->name, 'usuarios');
            }),

            'Roles y permisos' => $permisos->filter(function ($permiso) {
                return Str::contains($permiso->name, 'roles');
            }),

            'Configuración' => $permisos->filter(function ($permiso) {
                return Str::contains($permiso->name, 'configuracion');
            }),
        ];

        return view('livewire.roles.rol-index', [
            'roles' => $roles,
            'permisosAgrupados' => $permisosAgrupados,
        ]);
    }
}
