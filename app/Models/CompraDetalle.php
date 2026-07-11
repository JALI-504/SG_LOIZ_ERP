<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompraDetalle extends Model
{
    use HasFactory;

    protected $table = 'compra_detalles';

    protected $fillable = [
        'compra_id',
        'tipo_item',
        'item_id',
        'codigo',
        'descripcion',
        'cantidad',
        'costo_unitario',
        'subtotal',
        'descuento',
        'total',
    ];

    public function compra()
    {
        return $this->belongsTo(Compra::class);
    }

    public function getEsInsumoAttribute()
    {
        return $this->tipo_item === 'Insumo';
    }

    public function getEsProductoAttribute()
    {
        return $this->tipo_item === 'Producto';
    }
}
