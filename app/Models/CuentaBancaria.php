<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuentaBancaria extends Model
{
    use HasFactory;

    protected $table = 'cuentas_bancarias';

    protected $fillable = [
        'codigo',
        'banco',
        'nombre_cuenta',
        'numero_cuenta',
        'tipo_cuenta',
        'moneda',
        'saldo_inicial',
        'saldo_actual',
        'activo',
        'observacion',
        'user_id',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($cuenta) {
            if (!$cuenta->codigo) {
                $ultimoId = self::max('id') + 1;
                $cuenta->codigo = 'CBA-' . str_pad($ultimoId, 6, '0', STR_PAD_LEFT);
            }

            if ($cuenta->saldo_actual === null) {
                $cuenta->saldo_actual = $cuenta->saldo_inicial ?: 0;
            }
        });
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getEstadoTextoAttribute()
    {
        return $this->activo ? 'Activa' : 'Inactiva';
    }
}
