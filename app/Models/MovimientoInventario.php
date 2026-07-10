<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    use HasFactory;

    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'insumo_id',
        'tipo_movimiento',
        'cantidad',
        'costo_unitario',
        'total',
        'referencia',
        'observacion',
    ];

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }

    public function detalleLotes()
    {
        return $this->hasMany(MovimientoInventarioLote::class, 'movimiento_inventario_id');
    }
}
