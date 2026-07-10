<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    protected $table = 'productos';

    protected $fillable = [
        'codigo',
        'codigo_barra',
        'nombre',
        'categoria',
        'tipo_producto',
        'unidad_venta',
        'maneja_inventario',
        'usa_receta',
        'ancho_cm',
        'largo_cm',
        'espesor_mm',
        'stock_actual',
        'stock_minimo',
        'costo_compra',
        'costo_unitario',
        'precio_venta',
        'descripcion',
        'activo',
    ];

    public function insumos()
    {
        return $this->belongsToMany(Insumo::class, 'producto_insumos')
            ->withPivot('cantidad_por_unidad')
            ->withTimestamps();
    }

    public function recetas()
    {
        return $this->hasMany(ProductoInsumo::class);
    }

    public function getUtilidadAttribute()
    {
        return $this->precio_venta - $this->costo_unitario;
    }

    public function getMargenAttribute()
    {
        if ($this->precio_venta <= 0) {
            return 0;
        }

        return (($this->precio_venta - $this->costo_unitario) / $this->precio_venta) * 100;
    }

    public function getStockBajoAttribute()
    {
        return $this->maneja_inventario && $this->stock_actual <= $this->stock_minimo;
    }
}
