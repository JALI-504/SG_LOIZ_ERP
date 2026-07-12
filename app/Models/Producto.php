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
        'tipo_impuesto',
        'porcentaje_isv',
    ];

    protected static function booted()
    {
        static::creating(function ($producto) {
            if (empty($producto->codigo)) {
                $producto->codigo = self::generarCodigoProducto();
            }

            if (empty($producto->codigo_barra)) {
                $producto->codigo_barra = self::generarCodigoBarraInterno();
            } else {
                $producto->codigo_barra = trim($producto->codigo_barra);
            }
        });

        static::updating(function ($producto) {
            if (!empty($producto->codigo_barra)) {
                $producto->codigo_barra = trim($producto->codigo_barra);
            }
        });
    }

    public static function generarCodigoProducto()
    {
        $ultimo = self::orderByDesc('id')->first();

        $numero = $ultimo ? $ultimo->id + 1 : 1;

        return 'PROD-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    public static function generarCodigoBarraInterno()
    {
        $ultimo = self::orderByDesc('id')->first();

        $numero = $ultimo ? $ultimo->id + 1 : 1;

        return 'LOIZ-P-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

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

    public function movimientos()
    {
        return $this->hasMany(MovimientoProducto::class);
    }

    public function lotes()
    {
        return $this->hasMany(LoteProducto::class);
    }

    public function lotesDisponibles()
    {
        return $this->hasMany(LoteProducto::class)
            ->where('activo', true)
            ->where('cantidad_disponible', '>', 0);
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
