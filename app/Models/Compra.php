<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Compra extends Model
{
    use HasFactory;

    protected $table = 'compras';

    protected $fillable = [
        'numero',
        'proveedor_id',
        'fecha',
        'hora',
        'numero_comprobante',
        'tipo_comprobante',
        'tipo_pago',
        'metodo_pago',
        'subtotal',
        'descuento',
        'impuesto',
        'total',
        'monto_pagado',
        'saldo_pendiente',
        'estado',
        'observacion',
    ];

    protected static function booted()
    {
        static::creating(function ($compra) {
            if (empty($compra->numero)) {
                $compra->numero = self::generarNumero();
            }

            if (empty($compra->fecha)) {
                $compra->fecha = now()->format('Y-m-d');
            }

            if (empty($compra->hora)) {
                $compra->hora = now()->format('H:i:s');
            }

            if (empty($compra->estado)) {
                $compra->estado = 'Registrada';
            }
        });
    }

    public static function generarNumero()
    {
        $ultima = self::orderByDesc('id')->first();

        $numero = $ultima ? $ultima->id + 1 : 1;

        return 'COMP-' . str_pad($numero, 6, '0', STR_PAD_LEFT);
    }

    public function proveedor()
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function detalles()
    {
        return $this->hasMany(CompraDetalle::class);
    }

    public function getEsAnuladaAttribute()
    {
        return $this->estado === 'Anulada';
    }

    public function getEsCreditoAttribute()
    {
        return $this->tipo_pago === 'Crédito';
    }

    public function getEstaPagadaAttribute()
    {
        return $this->saldo_pendiente <= 0 && $this->estado !== 'Anulada';
    }
}
