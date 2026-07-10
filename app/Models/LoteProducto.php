<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoteProducto extends Model
{
    use HasFactory;

    protected $table = 'lotes_productos';

    protected $fillable = [
        'producto_id',
        'codigo_lote',
        'fecha_entrada',
        'cantidad_inicial',
        'cantidad_disponible',
        'costo_unitario',
        'total',
        'referencia',
        'observacion',
        'activo',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoProductoLote::class);
    }
}
