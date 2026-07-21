<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\ConfiguracionEmpresa;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas';

    protected $fillable = [
        'numero',
        'cliente_id',
        'fecha',
        'hora',
        'tipo_comprobante',
        'metodo_pago',
        'subtotal',
        'descuento',
        'impuesto',
        'total',
        'estado',
        'observacion',
        'monto_pagado',
        'saldo_pendiente',
        'subtotal_gravado',
        'subtotal_exento',
        'subtotal_no_sujeto',
        'isv_15',
        'retencion',
        'neto_recibido',
        'es_fiscal',
        'cai',
        'rango_autorizado_desde',
        'rango_autorizado_hasta',
        'fecha_limite_emision',
    ];

    protected static function booted()
    {
        static::creating(function ($venta) {
            $configuracion = ConfiguracionEmpresa::actual();

            if (empty($venta->tipo_comprobante)) {
                $venta->tipo_comprobante = $configuracion->esta_en_modo_fiscal
                    ? 'Factura'
                    : 'Recibo interno';
            }

            $venta->es_fiscal = $configuracion->esta_en_modo_fiscal;

            if ($venta->es_fiscal) {
                $venta->cai = $configuracion->cai;
                $venta->rango_autorizado_desde = $configuracion->rango_desde;
                $venta->rango_autorizado_hasta = $configuracion->rango_hasta;
                $venta->fecha_limite_emision = $configuracion->fecha_limite_emision;
            }

            if (empty($venta->numero)) {
                $venta->numero = self::generarNumero($configuracion);
            }

            if (empty($venta->fecha)) {
                $venta->fecha = now()->format('Y-m-d');
            }

            if (empty($venta->hora)) {
                $venta->hora = now()->format('H:i:s');
            }

            if (empty($venta->estado)) {
                $venta->estado = 'Pagada';
            }

            if (empty($venta->metodo_pago)) {
                $venta->metodo_pago = 'Efectivo';
            }

            if (empty($venta->neto_recibido)) {
                $venta->neto_recibido = $venta->total - $venta->retencion;
            }
        });
    }

    public static function generarNumero($configuracion = null)
    {
        $configuracion = $configuracion ?: ConfiguracionEmpresa::actual();

        if ($configuracion->esta_en_modo_fiscal) {
            return self::generarNumeroFactura($configuracion);
        }

        return self::generarNumeroRecibo($configuracion);
    }

    private static function generarNumeroRecibo($configuracion)
    {
        $prefijo = $configuracion->prefijo_recibo ?: 'REC';

        $ultimaVenta = self::where('numero', 'like', $prefijo . '-%')
            ->orderByDesc('id')
            ->first();

        $ultimoNumeroVentas = 0;

        if ($ultimaVenta) {
            $ultimoNumeroVentas = (int) str_replace($prefijo . '-', '', $ultimaVenta->numero);
        }

        $ultimoNumeroConfiguracion = (int) $configuracion->numero_actual_recibo;

        $nuevoNumero = max($ultimoNumeroVentas, $ultimoNumeroConfiguracion) + 1;

        $configuracion->numero_actual_recibo = $nuevoNumero;
        $configuracion->save();

        return $prefijo . '-' . str_pad($nuevoNumero, 6, '0', STR_PAD_LEFT);
    }

    private static function generarNumeroFactura($configuracion)
    {
        self::validarConfiguracionFiscal($configuracion);

        $desde = self::extraerNumeroFinal($configuracion->rango_desde);
        $hasta = self::extraerNumeroFinal($configuracion->rango_hasta);

        $establecimiento = str_pad($configuracion->establecimiento ?: '000', 3, '0', STR_PAD_LEFT);
        $puntoEmision = str_pad($configuracion->punto_emision ?: '001', 3, '0', STR_PAD_LEFT);
        $tipoDocumento = str_pad($configuracion->tipo_documento_fiscal ?: '01', 2, '0', STR_PAD_LEFT);

        $prefijoFactura = $establecimiento . '-' . $puntoEmision . '-' . $tipoDocumento . '-';

        $ultimaFactura = self::where('es_fiscal', true)
            ->where('numero', 'like', $prefijoFactura . '%')
            ->orderByDesc('id')
            ->first();

        $ultimoNumeroVentas = 0;

        if ($ultimaFactura) {
            $ultimoNumeroVentas = self::extraerNumeroFinal($ultimaFactura->numero);
        }

        $ultimoNumeroConfiguracion = (int) $configuracion->numero_actual_factura;

        /*
    |--------------------------------------------------------------------------
    | Determinar siguiente número real
    |--------------------------------------------------------------------------
    | Toma el número más alto entre:
    | - número actual guardado en configuración
    | - última factura existente en ventas
    |
    | Así evitamos duplicados si la configuración quedó atrasada.
    */

        $base = max($ultimoNumeroConfiguracion, $ultimoNumeroVentas);

        if ($base <= 0) {
            $nuevoNumero = $desde;
        } else {
            $nuevoNumero = $base + 1;
        }

        if ($nuevoNumero < $desde) {
            $nuevoNumero = $desde;
        }

        if ($hasta > 0 && $nuevoNumero > $hasta) {
            throw new \Exception('No se puede emitir la factura. El rango autorizado ya fue agotado.');
        }

        $configuracion->numero_actual_factura = $nuevoNumero;
        $configuracion->save();

        return self::formatearNumeroFactura($configuracion, $nuevoNumero);
    }

    private static function validarConfiguracionFiscal($configuracion)
    {
        if (!$configuracion->cai) {
            throw new \Exception('No se puede emitir factura fiscal. Falta configurar el CAI.');
        }

        if (!$configuracion->rango_desde || !$configuracion->rango_hasta) {
            throw new \Exception('No se puede emitir factura fiscal. Falta configurar el rango autorizado.');
        }

        if (!$configuracion->fecha_limite_emision) {
            throw new \Exception('No se puede emitir factura fiscal. Falta configurar la fecha límite de emisión.');
        }

        if (now()->format('Y-m-d') > $configuracion->fecha_limite_emision) {
            throw new \Exception('No se puede emitir factura fiscal. La fecha límite de emisión ya venció.');
        }

        if (!$configuracion->rtn) {
            throw new \Exception('No se puede emitir factura fiscal. Falta configurar el RTN del negocio.');
        }

        if (!$configuracion->nombre_legal && !$configuracion->nombre_comercial) {
            throw new \Exception('No se puede emitir factura fiscal. Falta configurar el nombre legal o comercial del negocio.');
        }
    }

    private static function formatearNumeroFactura($configuracion, $numero)
    {
        $establecimiento = str_pad($configuracion->establecimiento ?: '000', 3, '0', STR_PAD_LEFT);
        $puntoEmision = str_pad($configuracion->punto_emision ?: '001', 3, '0', STR_PAD_LEFT);
        $tipoDocumento = str_pad($configuracion->tipo_documento_fiscal ?: '01', 2, '0', STR_PAD_LEFT);
        $correlativo = str_pad($numero, 8, '0', STR_PAD_LEFT);

        return $establecimiento . '-' . $puntoEmision . '-' . $tipoDocumento . '-' . $correlativo;
    }

    private static function extraerNumeroFinal($numeroDocumento)
    {
        $limpio = preg_replace('/[^0-9]/', '', $numeroDocumento);

        if (!$limpio) {
            return 0;
        }

        return (int) substr($limpio, -8);
    }

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function detalles()
    {
        return $this->hasMany(VentaDetalle::class);
    }

    public function getEsAnuladaAttribute()
    {
        return $this->estado === 'Anulada';
    }

    public function pagos()
    {
        return $this->hasMany(PagoVenta::class);
    }

    public function getEstaPagadaAttribute()
    {
        return $this->saldo_pendiente <= 0 && $this->estado !== 'Anulada';
    }

    public function getEstaPendienteAttribute()
    {
        return $this->saldo_pendiente > 0 && $this->estado !== 'Anulada';
    }
}
