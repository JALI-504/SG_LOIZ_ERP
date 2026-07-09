<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServicioInsumosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servicio_insumos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('servicio_id');
            $table->unsignedBigInteger('insumo_id');

            // Cantidad del insumo que se consume por cada unidad vendida del servicio
            $table->decimal('cantidad_por_unidad', 12, 2)->default(1);

            $table->timestamps();

            $table->foreign('servicio_id')
                ->references('id')
                ->on('servicios')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->foreign('insumo_id')
                ->references('id')
                ->on('insumos')
                ->onUpdate('cascade')
                ->onDelete('restrict');

            $table->unique(['servicio_id', 'insumo_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('servicio_insumos');
    }
}
