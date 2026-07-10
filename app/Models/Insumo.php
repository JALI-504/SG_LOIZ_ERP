<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Insumo extends Model
{
    use HasFactory;

    protected $table = 'insumos';

    protected $fillable = [
        'codigo',
        'nombre',
        'categoria',
        'unidad_compra',
        'cantidad_por_compra',
        'unidad_consumo',
        'ancho_cm',
        'largo_cm',
        'espesor_mm',
        'costo_compra',
        'costo_unitario_base',
        'porcentaje_merma',
        'costo_unitario_real',
        'stock_actual',
        'stock_minimo',
        'descripcion',
        'activo',
    ];

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventario::class);
    }

    public function servicios()
    {
        return $this->belongsToMany(Servicio::class, 'servicio_insumos')
            ->withPivot('cantidad_por_unidad')
            ->withTimestamps();
    }

    public function getStockBajoAttribute()
    {
        return $this->stock_actual <= $this->stock_minimo;
    }

    public function calcularCostos()
    {
        if ($this->cantidad_por_compra > 0) {
            $this->costo_unitario_base = $this->costo_compra / $this->cantidad_por_compra;
        } else {
            $this->costo_unitario_base = 0;
        }

        if ($this->porcentaje_merma > 0 && $this->porcentaje_merma < 100) {
            $this->costo_unitario_real = $this->costo_unitario_base / (1 - ($this->porcentaje_merma / 100));
        } else {
            $this->costo_unitario_real = $this->costo_unitario_base;
        }
    }
    
    public function lotes()
    {
        return $this->hasMany(LoteInsumo::class);
    }

    public function lotesDisponibles()
    {
        return $this->hasMany(LoteInsumo::class)
            ->where('activo', true)
            ->where('cantidad_disponible', '>', 0);
    }

    public function productos()
    {
        return $this->belongsToMany(Producto::class, 'producto_insumos')
            ->withPivot('cantidad_por_unidad')
            ->withTimestamps();
    }

    public function productoRecetas()
    {
        return $this->hasMany(ProductoInsumo::class);
    }
}
