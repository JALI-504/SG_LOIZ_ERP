<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
    ];

    protected static function booted()
    {
        static::creating(function ($venta) {
            if (empty($venta->numero)) {
                $venta->numero = self::generarNumero();
            }

            if (empty($venta->fecha)) {
                $venta->fecha = now()->format('Y-m-d');
            }

            if (empty($venta->hora)) {
                $venta->hora = now()->format('H:i:s');
            }

            if (empty($venta->tipo_comprobante)) {
                $venta->tipo_comprobante = 'Recibo interno';
            }

            if (empty($venta->estado)) {
                $venta->estado = 'Pagada';
            }
        });
    }

    public static function generarNumero()
    {
        $configuracion = ConfiguracionEmpresa::actual();

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
