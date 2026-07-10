<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Catalogo extends Model
{
    use HasFactory;

    protected $table = 'catalogos';

    protected $fillable = [
        'tipo',
        'nombre',
        'descripcion',
        'orden',
        'activo',
    ];

    public static function opciones($tipo)
    {
        return self::where('tipo', $tipo)
            ->where('activo', true)
            ->orderBy('nombre')
            ->get();
    }

    public function tipoCatalogo()
    {
        return $this->belongsTo(TipoCatalogo::class, 'tipo', 'codigo');
    }
}
