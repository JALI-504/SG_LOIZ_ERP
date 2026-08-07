<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AperturaCaja extends Model
{
    use HasFactory;

    protected $table = 'aperturas_caja';

    protected $fillable = [
        'codigo',
        'fecha',
        'hora_apertura',
        'user_id',
        'monto_inicial',
        'estado',
        'observacion',
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

        static::creating(function ($apertura) {
            if (!$apertura->codigo) {
                $ultimoId = self::max('id') + 1;
                $apertura->codigo = 'APC-' . str_pad($ultimoId, 6, '0', STR_PAD_LEFT);
            }

            if (!$apertura->fecha) {
                $apertura->fecha = now()->format('Y-m-d');
            }

            if (!$apertura->hora_apertura) {
                $apertura->hora_apertura = now()->format('H:i:s');
            }
        });
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function usuarioAnulacion()
    {
        return $this->belongsTo(User::class, 'anulado_por');
    }

    public function cierreCaja()
    {
        return $this->hasOne(CierreCaja::class, 'apertura_caja_id');
    }
}
