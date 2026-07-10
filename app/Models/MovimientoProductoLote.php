<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoProductoLote extends Model
{
    use HasFactory;

    protected $table = 'movimiento_producto_lotes';

    protected $fillable = [
        'movimiento_producto_id',
        'lote_producto_id',
        'cantidad',
        'costo_unitario',
        'total',
    ];

    public function movimiento()
    {
        return $this->belongsTo(MovimientoProducto::class, 'movimiento_producto_id');
    }

    public function lote()
    {
        return $this->belongsTo(LoteProducto::class, 'lote_producto_id');
    }
}
