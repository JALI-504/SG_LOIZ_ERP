<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagoVentasTable extends Migration
{
    public function up()
    {
        Schema::create('pago_ventas', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('venta_id');

            $table->date('fecha');
            $table->time('hora')->nullable();

            $table->decimal('monto', 12, 2)->default(0);
            $table->string('metodo_pago', 50)->default('Efectivo');

            $table->string('referencia', 100)->nullable();
            $table->text('observacion')->nullable();

            $table->timestamps();

            $table->foreign('venta_id')
                ->references('id')
                ->on('ventas')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pago_ventas');
    }
}