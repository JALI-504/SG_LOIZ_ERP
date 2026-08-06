<?php

namespace App\Listeners;

use App\Models\BitacoraSistema;
use Illuminate\Auth\Events\Login;

class RegistrarInicioSesion
{
    public function handle(Login $event)
    {
        BitacoraSistema::registrar(
            'Usuarios',
            'Iniciar sesión',
            'El usuario ' . $event->user->name . ' inició sesión en el sistema.',
            get_class($event->user),
            $event->user->id,
            null,
            [
                'usuario_id' => $event->user->id,
                'nombre' => $event->user->name,
                'email' => $event->user->email,
                'fecha_hora' => now()->format('Y-m-d H:i:s'),
            ]
        );
    }
}
