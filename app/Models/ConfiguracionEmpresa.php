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

    protected $casts = [
        'usa_facturacion_fiscal' => 'boolean',
        'usa_impuestos' => 'boolean',
        'usa_retenciones' => 'boolean',
        'precios_incluyen_isv' => 'boolean',
        'activo' => 'boolean',
        'fecha_limite_emision' => 'date',
        'porcentaje_isv_general' => 'decimal:2',
        'numero_actual_recibo' => 'integer',
        'numero_actual_factura' => 'integer',
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
        return $this->esta_en_modo_fiscal
            && !empty($this->rtn)
            && !empty($this->cai)
            && !empty($this->rango_desde)
            && !empty($this->rango_hasta)
            && !empty($this->fecha_limite_emision);
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

    public function getProximoReciboEstimadoAttribute()
    {
        $prefijo = $this->prefijo_recibo ?: 'REC';
        $numero = ((int) $this->numero_actual_recibo) + 1;

        return strtoupper($prefijo) . '-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    public function getProximaFacturaEstimadaAttribute()
    {
        $numero = ((int) $this->numero_actual_factura) + 1;

        if ((int) $this->numero_actual_factura <= 0 && $this->rango_desde) {
            $numero = $this->extraerNumeroFinal($this->rango_desde);
        }

        return str_pad($this->establecimiento ?: '000', 3, '0', STR_PAD_LEFT) . '-' .
            str_pad($this->punto_emision ?: '001', 3, '0', STR_PAD_LEFT) . '-' .
            str_pad($this->tipo_documento_fiscal ?: '01', 2, '0', STR_PAD_LEFT) . '-' .
            str_pad($numero, 8, '0', STR_PAD_LEFT);
    }

    private function extraerNumeroFinal($numeroDocumento)
    {
        $limpio = preg_replace('/[^0-9]/', '', $numeroDocumento);

        if (!$limpio) {
            return 1;
        }

        return (int) substr($limpio, -8);
    }
}
