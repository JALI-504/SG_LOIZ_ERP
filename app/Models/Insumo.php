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

    protected static function booted()
    {
        static::creating(function ($insumo) {
            if (empty($insumo->codigo)) {
                $insumo->codigo = self::generarCodigoPorCategoria($insumo->categoria);
            } else {
                $insumo->codigo = strtoupper(trim($insumo->codigo));
            }
        });

        static::updating(function ($insumo) {
            if (empty($insumo->codigo)) {
                $insumo->codigo = self::generarCodigoPorCategoria($insumo->categoria);
            } else {
                $insumo->codigo = strtoupper(trim($insumo->codigo));
            }
        });
    }

    public static function generarCodigoPorCategoria($categoria)
    {
        $prefijo = self::prefijoCategoria($categoria);

        $ultimo = self::where('codigo', 'like', $prefijo . '-%')
            ->orderByDesc('id')
            ->first();

        $numero = 1;

        if ($ultimo) {
            $partes = explode('-', $ultimo->codigo);
            $ultimoNumero = end($partes);

            if (is_numeric($ultimoNumero)) {
                $numero = (int) $ultimoNumero + 1;
            } else {
                $numero = $ultimo->id + 1;
            }
        }

        return $prefijo . '-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    public static function prefijoCategoria($categoria)
    {
        $categoria = strtolower(trim($categoria));

        $mapa = [
            'papel' => 'PAP',
            'tinta' => 'TIN',
            'toner' => 'TON',
            'tóner' => 'TON',
            'madera' => 'MAD',
            'mdf' => 'MDF',
            'acrilico' => 'ACR',
            'acrílico' => 'ACR',
            'vinil' => 'VIN',
            'vinilo' => 'VIN',
            'sublimacion' => 'SUB',
            'sublimación' => 'SUB',
            'herrajes' => 'HER',
            'bolsas' => 'BOL',
            'empaque' => 'EMP',
            'limpieza' => 'LIM',
        ];

        foreach ($mapa as $clave => $prefijo) {
            if (strpos($categoria, $clave) !== false) {
                return $prefijo;
            }
        }

        return 'INS';
    }

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
