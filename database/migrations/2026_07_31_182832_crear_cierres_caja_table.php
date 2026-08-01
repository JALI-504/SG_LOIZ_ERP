<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearCierresCajaTable extends Migration
{
    public function up()
    {
        Schema::create('cierres_caja', function (Blueprint $table) {
            $table->id();

            $table->string('codigo', 30)->unique();

            $table->date('fecha');

            $table->unsignedBigInteger('user_id')->nullable();

            $table->decimal('monto_inicial', 12, 2)->default(0);

            $table->decimal('ventas_efectivo', 12, 2)->default(0);
            $table->decimal('ventas_transferencia', 12, 2)->default(0);
            $table->decimal('ventas_tarjeta', 12, 2)->default(0);
            $table->decimal('ventas_otros', 12, 2)->default(0);

            $table->decimal('total_ingresos_ventas', 12, 2)->default(0);

            $table->decimal('gastos_registrados', 12, 2)->default(0);
            $table->decimal('pagos_proveedores', 12, 2)->default(0);

            $table->decimal('otros_ingresos', 12, 2)->default(0);
            $table->decimal('otros_egresos', 12, 2)->default(0);

            $table->decimal('total_ingresos', 12, 2)->default(0);
            $table->decimal('total_egresos', 12, 2)->default(0);

            $table->decimal('efectivo_esperado', 12, 2)->default(0);
            $table->decimal('efectivo_contado', 12, 2)->default(0);
            $table->decimal('diferencia', 12, 2)->default(0);

            $table->integer('cantidad_pagos_ventas')->default(0);
            $table->integer('cantidad_gastos')->default(0);
            $table->integer('cantidad_pagos_proveedores')->default(0);

            $table->text('observacion')->nullable();

            $table->string('estado', 30)->default('Cerrado');

            $table->dateTime('fecha_anulacion')->nullable();
            $table->unsignedBigInteger('anulado_por')->nullable();
            $table->text('motivo_anulacion')->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('anulado_por')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('cierres_caja');
    }
}
