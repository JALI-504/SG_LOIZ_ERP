<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateComprasTable extends Migration
{
    public function up()
    {
        Schema::create('compras', function (Blueprint $table) {
            $table->id();

            $table->string('numero', 30)->unique();

            $table->unsignedBigInteger('proveedor_id')->nullable();

            $table->date('fecha');
            $table->time('hora')->nullable();

            $table->string('numero_comprobante', 100)->nullable();
            $table->string('tipo_comprobante', 50)->default('Recibo');

            $table->string('tipo_pago', 30)->default('Contado'); // Contado / Crédito
            $table->string('metodo_pago', 50)->default('Efectivo');

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('impuesto', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->decimal('monto_pagado', 12, 2)->default(0);
            $table->decimal('saldo_pendiente', 12, 2)->default(0);

            $table->string('estado', 30)->default('Registrada'); // Registrada / Anulada

            $table->text('observacion')->nullable();

            $table->timestamps();

            $table->foreign('proveedor_id')
                ->references('id')
                ->on('proveedores')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('compras');
    }
}
