<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoVenta extends Model
{
    use HasFactory;

    protected $table = 'pago_ventas';

    protected $fillable = [
        'venta_id',
        'fecha',
        'hora',
        'monto',
        'metodo_pago',
        'referencia',
        'observacion',
    ];

    protected static function booted()
    {
        static::creating(function ($pago) {
            if (empty($pago->fecha)) {
                $pago->fecha = now()->format('Y-m-d');
            }

            if (empty($pago->hora)) {
                $pago->hora = now()->format('H:i:s');
            }
        });
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }
}
