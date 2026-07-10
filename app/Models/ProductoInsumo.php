<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductoInsumo extends Model
{
    use HasFactory;

    protected $table = 'producto_insumos';

    protected $fillable = [
        'producto_id',
        'insumo_id',
        'cantidad_por_unidad',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }
}
