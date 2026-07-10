<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionEmpresa extends Model
{
    use HasFactory;

    protected $table = 'configuracion_empresas';

    protected $fillable = [
        'nombre_comercial',
        'nombre_legal',
        'rtn',
        'telefono',
        'whatsapp',
        'correo',
        'direccion',
        'descripcion_negocio',
        'logo',
        'usa_facturacion_fiscal',
        'cai',
        'rango_desde',
        'rango_hasta',
        'fecha_limite_emision',
        'prefijo_recibo',
        'numero_actual_recibo',
        'mensaje_recibo',
        'activo',
    ];

    public static function actual()
    {
        $configuracion = self::where('activo', true)
            ->orderBy('id')
            ->first();

        if (!$configuracion) {
            $configuracion = self::create([
                'nombre_comercial' => 'Servicios Gráficos LOIZ',
                'descripcion_negocio' => 'Impresiones, productos personalizados y servicios gráficos',
                'prefijo_recibo' => 'REC',
                'numero_actual_recibo' => 0,
                'usa_facturacion_fiscal' => false,
                'mensaje_recibo' => 'Gracias por su compra.',
                'activo' => true,
            ]);
        }

        return $configuracion;
    }

    public function getTieneLogoAttribute()
    {
        return !empty($this->logo);
    }

    public function getTieneFacturacionFiscalAttribute()
    {
        return $this->usa_facturacion_fiscal && $this->cai && $this->rtn;
    }
}
