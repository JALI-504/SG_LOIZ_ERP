<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePagoComprasTable extends Migration
{
    public function up()
    {
        Schema::create('pago_compras', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('compra_id');

            $table->date('fecha');
            $table->time('hora')->nullable();

            $table->decimal('monto', 12, 2)->default(0);
            $table->string('metodo_pago', 50)->default('Efectivo');

            $table->string('referencia', 100)->nullable();
            $table->text('observacion')->nullable();

            $table->timestamps();

            $table->foreign('compra_id')
                ->references('id')
                ->on('compras')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('pago_compras');
    }
}
