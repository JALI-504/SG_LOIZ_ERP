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
        'modo_fiscal',
        'documento_venta_activo',
        'usa_impuestos',
        'usa_retenciones',
        'precios_incluyen_isv',
        'porcentaje_isv_general',
        'establecimiento',
        'punto_emision',
        'tipo_documento_fiscal',
        'numero_actual_factura',
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

                'usa_facturacion_fiscal' => false,
                'modo_fiscal' => 'Interno',
                'documento_venta_activo' => 'Recibo interno',
                'usa_impuestos' => false,
                'usa_retenciones' => false,
                'precios_incluyen_isv' => true,
                'porcentaje_isv_general' => 15,

                'establecimiento' => '000',
                'punto_emision' => '001',
                'tipo_documento_fiscal' => '01',

                'prefijo_recibo' => 'REC',
                'numero_actual_recibo' => 0,
                'numero_actual_factura' => 0,

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

    public function getEstaEnModoFiscalAttribute()
    {
        return $this->usa_facturacion_fiscal
            && $this->modo_fiscal === 'Fiscal'
            && $this->documento_venta_activo === 'Factura';
    }

    public function getEstaEnModoInternoAttribute()
    {
        return !$this->esta_en_modo_fiscal;
    }
}
