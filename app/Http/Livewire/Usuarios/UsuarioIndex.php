<?php

namespace App\Http\Livewire\Usuarios;

use App\Models\User;
use App\Models\BitacoraSistema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class UsuarioIndex extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $perPage = 10;
    public $filtroEstado = 'activos';
    public $filtroRol = 'todos';

    public $usuario_id;
    public $name;
    public $email;
    public $password;
    public $password_confirmation;
    public $activo = true;
    public $rol;

    public $usuarioPasswordId;
    public $nueva_password;
    public $nueva_password_confirmation;

    public $modalTitle = 'Nuevo usuario';

    public $roles = [];

    public $mostrarModalUsuario = false;
    public $mostrarModalPassword = false;

    private function autorizarVerUsuarios()
    {
        if (!auth()->user()->can('ver usuarios')) {
            abort(403, 'No tiene permiso para ver usuarios.');
        }
    }

    private function autorizarCrearUsuarios()
    {
        if (!auth()->user()->can('crear usuarios')) {
            abort(403, 'No tiene permiso para crear usuarios.');
        }
    }

    private function autorizarEditarUsuarios()
    {
        if (!auth()->user()->can('editar usuarios')) {
            abort(403, 'No tiene permiso para editar usuarios.');
        }
    }

    private function autorizarDesactivarUsuarios()
    {
        if (!auth()->user()->can('desactivar usuarios')) {
            abort(403, 'No tiene permiso para activar o desactivar usuarios.');
        }
    }

    private function autorizarCambiarPassword()
    {
        if (!auth()->user()->can('cambiar password usuarios')) {
            abort(403, 'No tiene permiso para cambiar contraseñas.');
        }
    }

    private function autorizarAsignarRoles()
    {
        if (!auth()->user()->can('asignar roles usuarios')) {
            abort(403, 'No tiene permiso para asignar roles.');
        }
    }

    public function mount()
    {
        $this->autorizarVerUsuarios();

        $this->roles = Role::orderBy('name')
            ->pluck('name')
            ->toArray();

        $this->rol = $this->roles[0] ?? null;
    }

    protected function rules()
    {
        $rules = [
            'name' => 'required|min:3|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($this->usuario_id),
            ],
            'activo' => 'boolean',
            'rol' => 'required|exists:roles,name',
        ];

        if (!$this->usuario_id) {
            $rules['password'] = 'required|min:8|confirmed';
        }

        return $rules;
    }

    protected $messages = [
        'name.required' => 'El nombre es obligatorio.',
        'name.min' => 'El nombre debe tener al menos 3 caracteres.',
        'email.required' => 'El correo es obligatorio.',
        'email.email' => 'Debe ingresar un correo válido.',
        'email.unique' => 'Este correo ya está registrado.',
        'password.required' => 'La contraseña es obligatoria.',
        'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
        'password.confirmed' => 'La confirmación de contraseña no coincide.',
        'rol.required' => 'Debe seleccionar un rol.',
        'rol.exists' => 'El rol seleccionado no es válido.',
        'nueva_password.required' => 'La nueva contraseña es obligatoria.',
        'nueva_password.min' => 'La nueva contraseña debe tener al menos 8 caracteres.',
        'nueva_password.confirmed' => 'La confirmación de contraseña no coincide.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFiltroEstado()
    {
        $this->resetPage();
    }

    public function updatingFiltroRol()
    {
        $this->resetPage();
    }

    public function create()
    {
        $this->autorizarCrearUsuarios();
        $this->autorizarAsignarRoles();

        $this->resetInput();

        $this->modalTitle = 'Nuevo usuario';

        $this->mostrarModalUsuario = true;
    }

    public function store()
    {
        $this->autorizarCrearUsuarios();
        $this->autorizarAsignarRoles();

        $this->validate();

        $usuario = User::create([
            'name' => trim($this->name),
            'email' => trim($this->email),
            'password' => Hash::make($this->password),
            'activo' => (bool) $this->activo,
        ]);

        $usuario->syncRoles([$this->rol]);

        $datosNuevos = $usuario->fresh()->load('roles')->toArray();
        unset($datosNuevos['password'], $datosNuevos['remember_token']);

        BitacoraSistema::registrar(
            'Usuarios',
            'Registrar',
            'Registró el usuario ' . $usuario->name . ' con el rol ' . $this->rol . '.',
            User::class,
            $usuario->id,
            null,
            $datosNuevos
        );

        $this->resetInput();

        $this->mostrarModalUsuario = false;

        session()->flash('message', 'Usuario registrado correctamente.');
    }

    public function edit($id)
    {
        $this->autorizarEditarUsuarios();
        $this->autorizarAsignarRoles();

        $usuario = User::with('roles')->findOrFail($id);

        $this->usuario_id = $usuario->id;
        $this->name = $usuario->name;
        $this->email = $usuario->email;
        $this->activo = (bool) $usuario->activo;
        $this->rol = optional($usuario->roles->first())->name;

        $this->password = null;
        $this->password_confirmation = null;

        $this->modalTitle = 'Editar usuario';

        $this->mostrarModalUsuario = true;
    }

    public function update()
    {
        $this->autorizarEditarUsuarios();
        $this->autorizarAsignarRoles();

        $this->validate();

        $usuario = User::with('roles')->findOrFail($this->usuario_id);

        if ($usuario->id === auth()->id() && !$this->activo) {
            session()->flash('error', 'No puede desactivar su propio usuario.');
            return;
        }

        if ($usuario->id === auth()->id() && $this->rol !== 'Administrador') {
            session()->flash('error', 'No puede quitarse a sí mismo el rol Administrador.');
            return;
        }

        $datosAnteriores = $usuario->toArray();
        unset($datosAnteriores['password'], $datosAnteriores['remember_token']);

        $rolAnterior = optional($usuario->roles->first())->name ?? 'Sin rol';

        $usuario->update([
            'name' => trim($this->name),
            'email' => trim($this->email),
            'activo' => (bool) $this->activo,
        ]);

        $usuario->syncRoles([$this->rol]);

        $usuarioActualizado = $usuario->fresh()->load('roles');

        $datosNuevos = $usuarioActualizado->toArray();
        unset($datosNuevos['password'], $datosNuevos['remember_token']);

        BitacoraSistema::registrar(
            'Usuarios',
            'Actualizar',
            'Actualizó el usuario ' . $usuarioActualizado->name . '. Rol anterior: ' . $rolAnterior . '. Rol nuevo: ' . $this->rol . '.',
            User::class,
            $usuarioActualizado->id,
            $datosAnteriores,
            $datosNuevos
        );

        $this->resetInput();

        $this->mostrarModalUsuario = false;

        session()->flash('message', 'Usuario actualizado correctamente.');
    }

    public function cambiarEstado($id)
    {
        $this->autorizarDesactivarUsuarios();

        $usuario = User::with('roles')->findOrFail($id);

        if ($usuario->id === auth()->id()) {
            session()->flash('error', 'No puede desactivar su propio usuario.');
            return;
        }

        $datosAnteriores = $usuario->toArray();
        unset($datosAnteriores['password'], $datosAnteriores['remember_token']);

        $estadoAnterior = $usuario->activo ? 'Activo' : 'Inactivo';

        $usuario->update([
            'activo' => !$usuario->activo,
        ]);

        $usuarioActualizado = $usuario->fresh()->load('roles');

        $estadoNuevo = $usuarioActualizado->activo ? 'Activo' : 'Inactivo';

        $datosNuevos = $usuarioActualizado->toArray();
        unset($datosNuevos['password'], $datosNuevos['remember_token']);

        BitacoraSistema::registrar(
            'Usuarios',
            'Actualizar',
            'Cambió el estado del usuario ' . $usuarioActualizado->name . ' de ' . $estadoAnterior . ' a ' . $estadoNuevo . '.',
            User::class,
            $usuarioActualizado->id,
            $datosAnteriores,
            $datosNuevos
        );

        session()->flash('message', 'Estado del usuario actualizado correctamente.');
    }

    public function abrirCambioPassword($id)
    {
        $this->autorizarCambiarPassword();

        $usuario = User::findOrFail($id);

        $this->usuarioPasswordId = $usuario->id;
        $this->nueva_password = null;
        $this->nueva_password_confirmation = null;

        $this->resetErrorBag();
        $this->resetValidation();

        $this->mostrarModalPassword = true;
    }

    public function cambiarPassword()
    {
        $this->autorizarCambiarPassword();

        $this->validate([
            'nueva_password' => 'required|min:8|confirmed',
        ]);

        $usuario = User::findOrFail($this->usuarioPasswordId);

        $usuario->update([
            'password' => Hash::make($this->nueva_password),
        ]);

        BitacoraSistema::registrar(
            'Usuarios',
            'Actualizar',
            'Cambió la contraseña del usuario ' . $usuario->name . '.',
            User::class,
            $usuario->id,
            null,
            [
                'usuario_id' => $usuario->id,
                'name' => $usuario->name,
                'email' => $usuario->email,
                'accion' => 'Contraseña actualizada',
            ]
        );

        $this->usuarioPasswordId = null;
        $this->nueva_password = null;
        $this->nueva_password_confirmation = null;

        $this->mostrarModalPassword = false;

        session()->flash('message', 'Contraseña actualizada correctamente.');
    }

    public function cerrarModalUsuario()
    {
        $this->mostrarModalUsuario = false;
        $this->resetInput();
    }

    public function cerrarModalPassword()
    {
        $this->mostrarModalPassword = false;

        $this->usuarioPasswordId = null;
        $this->nueva_password = null;
        $this->nueva_password_confirmation = null;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    private function resetInput()
    {
        $this->usuario_id = null;
        $this->name = '';
        $this->email = '';
        $this->password = null;
        $this->password_confirmation = null;
        $this->activo = true;
        $this->rol = $this->roles[0] ?? null;

        $this->resetErrorBag();
        $this->resetValidation();
    }

    public function render()
    {
        $this->autorizarVerUsuarios();

        $usuarios = User::with('roles')
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            })
            ->when($this->filtroEstado === 'activos', function ($query) {
                $query->where('activo', true);
            })
            ->when($this->filtroEstado === 'inactivos', function ($query) {
                $query->where('activo', false);
            })
            ->when($this->filtroRol !== 'todos', function ($query) {
                $query->role($this->filtroRol);
            })
            ->orderBy('name')
            ->paginate($this->perPage);

        return view('livewire.usuarios.usuario-index', [
            'usuarios' => $usuarios,
        ]);
    }
}
