<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoteProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lotes_productos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('producto_id');

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

            $table->foreign('producto_id')
                ->references('id')
                ->on('productos')
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
        Schema::dropIfExists('lote_productos');
    }
}
