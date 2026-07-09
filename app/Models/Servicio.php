<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Insumo;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicios';

    protected $fillable = [
        'codigo',
        'nombre',
        'tipo_servicio',
        'tamano_papel',
        'color',
        'caras',
        'unidad_cobro',
        'costo_unitario',
        'precio_unitario',
        'descripcion',
        'activo',
    ];

    public function insumos()
    {
        return $this->belongsToMany(Insumo::class, 'servicio_insumos')
            ->withPivot('cantidad_por_unidad')
            ->withTimestamps();
    }

    public function getUtilidadUnitaraAttribute()
    {
        return $this->precio_unitario - $this->costo_unitario;
    }

    public function getMargenPorcentajeAttribute()
    {
        if ($this->costo_unitario <= 0) {
            return 0;
        }

        return (($this->precio_unitario - $this->costo_unitario) / $this->costo_unitario) * 100;
    }
}
