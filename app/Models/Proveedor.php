<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
    use HasFactory;

    protected $table = 'proveedores';

    protected $fillable = [
        'codigo',
        'nombre_comercial',
        'nombre_legal',
        'tipo_proveedor',
        'rtn',
        'dni',
        'telefono',
        'whatsapp',
        'correo',
        'persona_contacto',
        'telefono_contacto',
        'direccion',
        'observacion',
        'activo',
    ];

    protected static function booted()
    {
        static::creating(function ($proveedor) {
            if (empty($proveedor->codigo)) {
                $proveedor->codigo = self::generarCodigo();
            }
        });
    }

    public static function generarCodigo()
    {
        $ultimo = self::orderByDesc('id')->first();

        $numero = $ultimo ? $ultimo->id + 1 : 1;

        return 'PROV-' . str_pad($numero, 5, '0', STR_PAD_LEFT);
    }

    public function getEstadoTextoAttribute()
    {
        return $this->activo ? 'Activo' : 'Inactivo';
    }
}
