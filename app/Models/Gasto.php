<?php

namespace App\Models;

use App\Models\CuentaBancaria;
use App\Models\MovimientoBancario;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gasto extends Model
{
    use HasFactory;

    protected $table = 'gastos';

    protected $fillable = [
        'fecha',
        'hora',
        'categoria',
        'descripcion',
        'monto',
        'metodo_pago',
        'referencia',
        'proveedor',
        'observacion',
        'estado',
        'cuenta_bancaria_id',
        'movimiento_bancario_id',
    ];

    protected static function booted()
    {
        static::creating(function ($gasto) {
            if (empty($gasto->fecha)) {
                $gasto->fecha = now()->format('Y-m-d');
            }

            if (empty($gasto->hora)) {
                $gasto->hora = now()->format('H:i:s');
            }

            if (empty($gasto->estado)) {
                $gasto->estado = 'Registrado';
            }
        });
    }

    public function getEsAnuladoAttribute()
    {
        return $this->estado === 'Anulado';
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
