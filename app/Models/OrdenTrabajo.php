<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenTrabajo extends Model
{
    use HasFactory;

    protected $table = 'ordenes_trabajo';

    protected $fillable = [
        'codigo',
        'fecha',
        'fecha_entrega',
        'cliente_id',
        'cliente_nombre',
        'cliente_telefono',
        'titulo',
        'descripcion',
        'estado',
        'prioridad',
        'subtotal',
        'descuento',
        'total',
        'abono',
        'saldo',
        'venta_id',
        'observacion',
        'user_id',
        'fecha_anulacion',
        'anulado_por',
        'motivo_anulacion',
    ];

    protected static function booted()
    {
        static::creating(function ($orden) {
            if (empty($orden->codigo)) {
                $orden->codigo = self::generarCodigo();
            }
        });
    }

    public static function generarCodigo()
    {
        $ultimo = self::orderByDesc('id')->first();

        $numero = $ultimo ? $ultimo->id + 1 : 1;

        return 'OT-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function detalles()
    {
        return $this->hasMany(OrdenTrabajoDetalle::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
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

    public function getEstaEntregadaAttribute()
    {
        return $this->estado === 'Entregada';
    }
}
