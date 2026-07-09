<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoteInsumo extends Model
{
    use HasFactory;

    protected $table = 'lotes_insumos';

    protected $fillable = [
        'insumo_id',
        'codigo_lote',
        'fecha_entrada',
        'cantidad_inicial',
        'cantidad_disponible',
        'costo_unitario',
        'total',
        'referencia',
        'observacion',
        'activo',
    ];

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }

    public function movimientos()
    {
        return $this->hasMany(MovimientoInventarioLote::class);
    }
}
