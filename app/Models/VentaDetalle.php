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
        'tipo_impuesto',
        'porcentaje_isv',
        'subtotal_gravado',
        'subtotal_exento',
        'subtotal_no_sujeto',
        'impuesto',
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

    public function getEsGravadoAttribute()
    {
        return $this->tipo_impuesto === 'Gravado 15%';
    }

    public function getEsExentoAttribute()
    {
        return $this->tipo_impuesto === 'Exento';
    }

    public function getEsNoSujetoAttribute()
    {
        return $this->tipo_impuesto === 'No sujeto';
    }
}
