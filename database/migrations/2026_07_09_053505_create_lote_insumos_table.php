<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoteInsumosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lotes_insumos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('insumo_id');

            $table->string('codigo_lote', 50)->nullable();
            $table->date('fecha_entrada')->nullable();

            $table->decimal('cantidad_inicial', 12, 2)->default(0);
            $table->decimal('cantidad_disponible', 12, 2)->default(0);

            $table->decimal('costo_unitario', 12, 4)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->string('referencia', 100)->nullable();
            $table->text('observacion')->nullable();

            $table->boolean('activo')->default(true);

            $table->timestamps();

            $table->foreign('insumo_id')
                ->references('id')
                ->on('insumos')
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
        Schema::dropIfExists('lote_insumos');
    }
}
