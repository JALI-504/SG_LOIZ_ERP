<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
    use HasFactory;

    protected $table = 'clientes';

    protected $fillable = [
        'primer_nombre',
        'segundo_nombre',
        'primer_apellido',
        'segundo_apellido',
        'codigo_pais',
        'telefono',
        'correo',
        'dni',
        'rtn',
        'tipo_cliente',
        'departamento_id',
        'municipio_id',
        'direccion_referencia',
        'notas',
        'activo',
    ];

    public function departamento()
    {
        return $this->belongsTo(Departamento::class);
    }

    public function municipio()
    {
        return $this->belongsTo(Municipio::class);
    }

    public function getNombreCompletoAttribute()
    {
        return trim(
            $this->primer_nombre . ' ' .
                $this->segundo_nombre . ' ' .
                $this->primer_apellido . ' ' .
                $this->segundo_apellido
        );
    }

    public function getDniFormateadoAttribute()
    {
        if (!$this->dni || strlen($this->dni) !== 13) {
            return $this->dni;
        }

        return substr($this->dni, 0, 4) . '-' .
            substr($this->dni, 4, 4) . '-' .
            substr($this->dni, 8, 5);
    }

    public function getTelefonoFormateadoAttribute()
    {
        if (!$this->telefono || strlen($this->telefono) !== 8) {
            return $this->telefono;
        }

        return substr($this->telefono, 0, 4) . '-' .
            substr($this->telefono, 4, 4);
    }

    public function getTelefonoCompletoAttribute()
    {
        return $this->codigo_pais . ' ' . $this->telefono_formateado;
    }

    public function setDniAttribute($value)
    {
        $this->attributes['dni'] = preg_replace('/\D/', '', $value);
    }

    public function setTelefonoAttribute($value)
    {
        $this->attributes['telefono'] = preg_replace('/\D/', '', $value);
    }

    public function setRtnAttribute($value)
    {
        $this->attributes['rtn'] = $value ? preg_replace('/\D/', '', $value) : null;
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }
}
