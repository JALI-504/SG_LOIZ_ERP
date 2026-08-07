<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConciliacionBancaria extends Model
{
    use HasFactory;

    protected $table = 'conciliaciones_bancarias';

    protected $fillable = [
        'codigo',
        'cuenta_bancaria_id',
        'fecha_inicio',
        'fecha_fin',
        'saldo_inicial_sistema',
        'total_entradas_sistema',
        'total_salidas_sistema',
        'saldo_final_sistema',
        'saldo_final_banco',
        'diferencia',
        'cantidad_movimientos',
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

        static::creating(function ($conciliacion) {
            if (!$conciliacion->codigo) {
                $ultimoId = self::max('id') + 1;
                $conciliacion->codigo = 'CONB-' . str_pad($ultimoId, 6, '0', STR_PAD_LEFT);
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
}
