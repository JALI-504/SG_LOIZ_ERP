<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicioInsumo extends Model
{
    use HasFactory;

    protected $table = 'servicio_insumos';

    protected $fillable = [
        'servicio_id',
        'insumo_id',
        'cantidad_por_unidad',
    ];

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }

    public function insumo()
    {
        return $this->belongsTo(Insumo::class);
    }
}
