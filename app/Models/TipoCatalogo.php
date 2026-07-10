<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class TipoCatalogo extends Model
{
    use HasFactory;

    protected $table = 'tipos_catalogo';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'orden',
        'activo',
    ];

    public static function opciones()
    {
        return self::where('activo', true)
            ->orderBy('nombre')
            ->get();
    }

    public function catalogos()
    {
        return $this->hasMany(Catalogo::class, 'tipo', 'codigo');
    }
}
