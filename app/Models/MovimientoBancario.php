<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoBancario extends Model
{
    use HasFactory;

    protected $table = 'movimientos_bancarios';

    protected $fillable = [
        'codigo',
        'cuenta_bancaria_id',
        'fecha',
        'hora',
        'tipo',
        'categoria',
        'referencia',
        'descripcion',
        'monto',
        'saldo_anterior',
        'saldo_nuevo',
        'origen',
        'origen_id',
        'estado',
        'observacion',
        'user_id',
        'fecha_anulacion',
        'anulado_por',
        'motivo_anulacion',
    ];

    protected $dates = [
        'fecha_anulacion',
        'created_at',
        'updated_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($movimiento) {
            if (!$movimiento->codigo) {
                $ultimoId = self::max('id') + 1;
                $movimiento->codigo = 'MVB-' . str_pad($ultimoId, 6, '0', STR_PAD_LEFT);
            }

            if (!$movimiento->hora) {
                $movimiento->hora = now()->format('H:i:s');
            }

            if (!$movimiento->estado) {
                $movimiento->estado = 'Activo';
            }

            if (!$movimiento->origen) {
                $movimiento->origen = 'Manual';
            }
        });
    }

    public function cuentaBancaria()
    {
        return $this->belongsTo(CuentaBancaria::class, 'cuenta_bancaria_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function usuarioAnulacion()
    {
        return $this->belongsTo(User::class, 'anulado_por');
    }

    public function getTipoTextoAttribute()
    {
        return $this->tipo;
    }
}
