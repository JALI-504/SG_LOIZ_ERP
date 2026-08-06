<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RespaldoSistema extends Model
{
    use HasFactory;

    protected $table = 'respaldo_sistemas';

    protected $fillable = [
        'user_id',
        'nombre_archivo',
        'ruta_archivo',
        'tipo',
        'tamano_mb',
        'estado',
        'observacion',
        'fecha_generacion',
    ];

    protected $dates = [
        'fecha_generacion',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
