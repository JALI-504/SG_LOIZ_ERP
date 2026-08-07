<?php

namespace App\Models;

use App\Models\AperturaCaja;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CierreCaja extends Model
{
    use HasFactory;

    protected $table = 'cierres_caja';

    protected $fillable = [
        'codigo',
        'fecha',
        'user_id',
        'monto_inicial',

        'ventas_efectivo',
        'ventas_transferencia',
        'ventas_tarjeta',
        'ventas_otros',

        'total_ingresos_ventas',

        'gastos_registrados',
        'pagos_proveedores',

        'otros_ingresos',
        'otros_egresos',

        'total_ingresos',
        'total_egresos',

        'efectivo_esperado',
        'efectivo_contado',
        'diferencia',

        'cantidad_pagos_ventas',
        'cantidad_gastos',
        'cantidad_pagos_proveedores',

        'observacion',
        'estado',

        'fecha_anulacion',
        'anulado_por',
        'motivo_anulacion',

        'apertura_caja_id',
    ];

    protected static function booted()
    {
        static::creating(function ($cierre) {
            if (empty($cierre->codigo)) {
                $cierre->codigo = self::generarCodigo();
            }

            if (empty($cierre->fecha)) {
                $cierre->fecha = now()->format('Y-m-d');
            }

            if (empty($cierre->estado)) {
                $cierre->estado = 'Cerrado';
            }
        });
    }

    public static function generarCodigo()
    {
        $ultimo = self::orderByDesc('id')->first();

        $numero = $ultimo ? $ultimo->id + 1 : 1;

        return 'CAJA-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function usuarioAnulacion()
    {
        return $this->belongsTo(User::class, 'anulado_por');
    }

    public function getEstaAnuladoAttribute()
    {
        return $this->estado === 'Anulado';
    }

    public function getTieneSobranteAttribute()
    {
        return $this->diferencia > 0;
    }

    public function getTieneFaltanteAttribute()
    {
        return $this->diferencia < 0;
    }

    public function getEstaCuadradoAttribute()
    {
        return (float) $this->diferencia === 0.0;
    }

    public function aperturaCaja()
    {
        return $this->belongsTo(AperturaCaja::class, 'apertura_caja_id');
    }
}
