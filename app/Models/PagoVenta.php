<?php

namespace App\Models;

use App\Models\CuentaBancaria;
use App\Models\MovimientoBancario;
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
        'estado',
        'fecha_anulacion',
        'observacion_anulacion',
        'cuenta_bancaria_id',
        'movimiento_bancario_id',
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

            if (empty($pago->estado)) {
                $pago->estado = 'Activo';
            }
        });
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function cuentaBancaria()
    {
        return $this->belongsTo(CuentaBancaria::class, 'cuenta_bancaria_id');
    }

    public function movimientoBancario()
    {
        return $this->belongsTo(MovimientoBancario::class, 'movimiento_bancario_id');
    }
}
