<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductoInsumosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('producto_insumos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('producto_id');
            $table->unsignedBigInteger('insumo_id');

            // Cantidad del insumo que consume 1 producto terminado
            $table->decimal('cantidad_por_unidad', 12, 2)->default(1);

            $table->timestamps();

            $table->foreign('producto_id')
                ->references('id')
                ->on('productos')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('insumo_id')
                ->references('id')
                ->on('insumos')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->unique(['producto_id', 'insumo_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('producto_insumos');
    }
}
