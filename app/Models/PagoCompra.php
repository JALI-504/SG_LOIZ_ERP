<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoCompra extends Model
{
    use HasFactory;

    protected $table = 'pago_compras';

    protected $fillable = [
        'compra_id',
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

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }
}
