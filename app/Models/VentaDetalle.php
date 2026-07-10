<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentaDetalle extends Model
{
    use HasFactory;

    protected $table = 'venta_detalles';

    protected $fillable = [
        'venta_id',
        'tipo_item',
        'item_id',
        'codigo',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'costo_unitario',
        'descuento',
        'subtotal',
        'total',
    ];

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function getEsProductoAttribute()
    {
        return $this->tipo_item === 'Producto';
    }

    public function getEsServicioAttribute()
    {
        return $this->tipo_item === 'Servicio';
    }

    public function getUtilidadAttribute()
    {
        return $this->total - ($this->costo_unitario * $this->cantidad);
    }
}
