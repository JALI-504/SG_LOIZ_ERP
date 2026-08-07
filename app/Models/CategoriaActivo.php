<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaActivo extends Model
{
    use HasFactory;

    protected $table = 'categorias_activos';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'depreciable',
        'vida_util_meses',
        'porcentaje_depreciacion_anual',
        'metodo_depreciacion',
        'activo',
        'user_id',
        'prefijo_codigo',
        'requiere_numero_serie',
        'requiere_marca_modelo',
        'requiere_responsable',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($categoria) {
            if (!$categoria->codigo) {
                $ultimoId = self::max('id') + 1;
                $categoria->codigo = 'CAT-ACT-' . str_pad($ultimoId, 5, '0', STR_PAD_LEFT);
            }
        });
    }

    public function activosFijos()
    {
        return $this->hasMany(ActivoFijo::class, 'categoria_activo_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getEstadoTextoAttribute()
    {
        return $this->activo ? 'Activa' : 'Inactiva';
    }
}
