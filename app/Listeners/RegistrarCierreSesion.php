<?php

namespace App\Listeners;

use App\Models\BitacoraSistema;
use Illuminate\Auth\Events\Logout;

class RegistrarCierreSesion
{
    public function handle(Logout $event)
    {
        if (!$event->user) {
            return;
        }

        BitacoraSistema::registrar(
            'Usuarios',
            'Cerrar sesión',
            'El usuario ' . $event->user->name . ' cerró sesión en el sistema.',
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
