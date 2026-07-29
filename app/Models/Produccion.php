<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produccion extends Model
{
    use HasFactory;

    protected $table = 'producciones';

    protected $fillable = [
        'codigo',
        'fecha',
        'producto_id',
        'cantidad',
        'costo_total',
        'costo_unitario',
        'movimiento_producto_id',
        'estado',
        'observacion',
        'user_id',
    ];

    protected static function booted()
    {
        static::creating(function ($produccion) {
            if (empty($produccion->codigo)) {
                $produccion->codigo = self::generarCodigo();
            }
        });
    }

    public static function generarCodigo()
    {
        $ultimo = self::orderByDesc('id')->first();

        $numero = $ultimo ? $ultimo->id + 1 : 1;

        return 'PRODCC-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function insumos()
    {
        return $this->hasMany(ProduccionInsumo::class);
    }

    public function movimientoProducto()
    {
        return $this->belongsTo(MovimientoProducto::class, 'movimiento_producto_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getEstaAnuladaAttribute()
    {
        return $this->estado === 'Anulada';
    }
}
