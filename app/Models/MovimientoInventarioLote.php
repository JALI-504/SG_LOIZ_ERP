<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventarioLote extends Model
{
    use HasFactory;

    protected $table = 'movimiento_inventario_lotes';

    protected $fillable = [
        'movimiento_inventario_id',
        'lote_insumo_id',
        'cantidad',
        'costo_unitario',
        'total',
    ];

    public function movimiento()
    {
        return $this->belongsTo(MovimientoInventario::class, 'movimiento_inventario_id');
    }

    public function lote()
    {
        return $this->belongsTo(LoteInsumo::class, 'lote_insumo_id');
    }

    public function detalleLotes()
    {
        return $this->hasMany(MovimientoInventarioLote::class, 'movimiento_inventario_id');
    }
}
