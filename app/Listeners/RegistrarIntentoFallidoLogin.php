<?php

namespace App\Listeners;

use App\Models\BitacoraSistema;
use Illuminate\Auth\Events\Failed;

class RegistrarIntentoFallidoLogin
{
    public function handle(Failed $event)
    {
        $correo = $event->credentials['email']
            ?? $event->credentials['correo']
            ?? $event->credentials['username']
            ?? $event->credentials['usuario']
            ?? request()->input('email')
            ?? request()->input('correo')
            ?? request()->input('username')
            ?? request()->input('usuario')
            ?? 'No identificado';

        BitacoraSistema::registrar(
            'Usuarios',
            'Intento fallido',
            'Intento fallido de inicio de sesión con el usuario/correo: ' . $correo . '.',
            $event->user ? get_class($event->user) : null,
            $event->user ? $event->user->id : null,
            null,
            [
                'usuario_o_correo' => $correo,
                'ip' => request()->ip(),
                'fecha_hora' => now()->format('Y-m-d H:i:s'),
                'motivo' => 'Credenciales incorrectas',
            ]
        );
    }
}
