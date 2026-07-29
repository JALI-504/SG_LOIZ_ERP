<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProduccionInsumo extends Model
{
    use HasFactory;

    protected $table = 'produccion_insumos';

    protected $fillable = [
        'produccion_id',
        'insumo_id',
        'movimiento_inventario_id',
        'cantidad_por_unidad',
        'cantidad_total',
        'costo_unitario',
        'costo_total',
    ];

    public function produccion()
    {
        return $this->belongsTo(Produccion::class);
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }

    public function movimientoInventario()
    {
        return $this->belongsTo(MovimientoInventario::class, 'movimiento_inventario_id');
    }
}
