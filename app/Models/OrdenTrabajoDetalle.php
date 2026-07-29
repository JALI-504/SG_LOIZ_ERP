<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrdenTrabajoDetalle extends Model
{
    use HasFactory;

    protected $table = 'orden_trabajo_detalles';

    protected $fillable = [
        'orden_trabajo_id',
        'tipo_item',
        'producto_id',
        'servicio_id',
        'descripcion',
        'cantidad',
        'precio_unitario',
        'subtotal',
        'observacion',
    ];

    public function orden()
    {
        return $this->belongsTo(OrdenTrabajo::class, 'orden_trabajo_id');
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class);
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class);
    }
}
