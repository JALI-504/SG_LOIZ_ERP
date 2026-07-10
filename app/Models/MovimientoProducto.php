<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoProducto extends Model
{
    use HasFactory;

    protected $table = 'movimientos_producto';

    protected $fillable = [
        'producto_id',
        'tipo_movimiento',
        'cantidad',
        'costo_unitario',
        'total',
        'referencia',
        'observacion',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function detalleLotes()
    {
        return $this->hasMany(MovimientoProductoLote::class, 'movimiento_producto_id');
    }
}
