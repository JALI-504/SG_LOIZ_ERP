<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DepreciacionActivo extends Model
{
    use HasFactory;

    protected $table = 'depreciaciones_activos';

    protected $fillable = [
        'codigo',
        'activo_fijo_id',
        'periodo',
        'fecha_depreciacion',
        'monto',
        'depreciacion_acumulada_anterior',
        'depreciacion_acumulada_nueva',
        'valor_en_libros_anterior',
        'valor_en_libros_nuevo',
        'estado',
        'observacion',
        'user_id',
        'fecha_anulacion',
        'anulado_por',
        'motivo_anulacion',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($depreciacion) {
            if (!$depreciacion->codigo) {
                $ultimoId = self::max('id') + 1;
                $depreciacion->codigo = 'DEP-ACT-' . str_pad($ultimoId, 6, '0', STR_PAD_LEFT);
            }

            if (!$depreciacion->estado) {
                $depreciacion->estado = 'Registrada';
            }
        });
    }

    public function activoFijo()
    {
        return $this->belongsTo(ActivoFijo::class, 'activo_fijo_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function usuarioAnulacion()
    {
        return $this->belongsTo(User::class, 'anulado_por');
    }
}
