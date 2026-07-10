<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovimientoProductoLotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movimiento_producto_lotes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('movimiento_producto_id');
            $table->unsignedBigInteger('lote_producto_id');

            $table->decimal('cantidad', 12, 2)->default(0);
            $table->decimal('costo_unitario', 12, 4)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->timestamps();

            $table->foreign('movimiento_producto_id')
                ->references('id')
                ->on('movimientos_producto')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('lote_producto_id')
                ->references('id')
                ->on('lotes_productos')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('movimiento_producto_lotes');
    }
}
