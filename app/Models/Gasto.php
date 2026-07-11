<?php

namespace App\Models;

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
}
