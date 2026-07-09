<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovimientoInventarioLotesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movimiento_inventario_lotes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('movimiento_inventario_id');
            $table->unsignedBigInteger('lote_insumo_id');

            $table->decimal('cantidad', 12, 2)->default(0);
            $table->decimal('costo_unitario', 12, 4)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->timestamps();

            $table->foreign('movimiento_inventario_id')
                ->references('id')
                ->on('movimientos_inventario')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('lote_insumo_id')
                ->references('id')
                ->on('lotes_insumos')
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
        Schema::dropIfExists('movimiento_inventario_lotes');
    }
}
