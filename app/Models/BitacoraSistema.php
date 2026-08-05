<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BitacoraSistema extends Model
{
    use HasFactory;

    protected $table = 'bitacoras_sistema';

    protected $fillable = [
        'fecha',
        'hora',
        'user_id',
        'modulo',
        'accion',
        'descripcion',
        'modelo',
        'modelo_id',
        'url',
        'ip',
        'user_agent',
        'datos_anteriores',
        'datos_nuevos',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public static function registrar(
        $modulo,
        $accion,
        $descripcion = null,
        $modelo = null,
        $modeloId = null,
        $datosAnteriores = null,
        $datosNuevos = null
    ) {
        try {
            self::create([
                'fecha' => now()->format('Y-m-d'),
                'hora' => now()->format('H:i:s'),

                'user_id' => auth()->check() ? auth()->id() : null,

                'modulo' => $modulo,
                'accion' => $accion,
                'descripcion' => $descripcion,

                'modelo' => $modelo,
                'modelo_id' => $modeloId,

                'url' => request() ? request()->fullUrl() : null,
                'ip' => request() ? request()->ip() : null,
                'user_agent' => request() ? request()->userAgent() : null,

                'datos_anteriores' => $datosAnteriores ? json_encode($datosAnteriores, JSON_UNESCAPED_UNICODE) : null,
                'datos_nuevos' => $datosNuevos ? json_encode($datosNuevos, JSON_UNESCAPED_UNICODE) : null,
            ]);
        } catch (\Exception $e) {
            // La bitácora nunca debe detener el sistema.
        }
    }
}
