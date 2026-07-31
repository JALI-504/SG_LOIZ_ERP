<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cotizacion extends Model
{
    use HasFactory;

    protected $table = 'cotizaciones';

    protected $fillable = [
        'codigo',
        'fecha',
        'fecha_validez',
        'cliente_id',
        'cliente_nombre',
        'cliente_telefono',
        'titulo',
        'descripcion',
        'estado',
        'subtotal',
        'descuento',
        'total',
        'condiciones',
        'observacion',
        'orden_trabajo_id',
        'user_id',
        'fecha_anulacion',
        'anulado_por',
        'motivo_anulacion',
    ];

    protected static function booted()
    {
        static::creating(function ($cotizacion) {
            if (empty($cotizacion->codigo)) {
                $cotizacion->codigo = self::generarCodigo();
            }

            if (empty($cotizacion->fecha)) {
                $cotizacion->fecha = now()->format('Y-m-d');
            }

            if (empty($cotizacion->estado)) {
                $cotizacion->estado = 'Pendiente';
            }
        });
    }

    public static function generarCodigo()
    {
        $ultimo = self::orderByDesc('id')->first();

        $numero = $ultimo ? $ultimo->id + 1 : 1;

        return 'COT-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function detalles()
    {
        return $this->hasMany(CotizacionDetalle::class);
    }

    public function ordenTrabajo()
    {
        return $this->belongsTo(OrdenTrabajo::class, 'orden_trabajo_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function usuarioAnulacion()
    {
        return $this->belongsTo(User::class, 'anulado_por');
    }

    public function getEstaAnuladaAttribute()
    {
        return $this->estado === 'Anulada';
    }

    public function getEstaConvertidaAttribute()
    {
        return $this->orden_trabajo_id !== null;
    }
}
