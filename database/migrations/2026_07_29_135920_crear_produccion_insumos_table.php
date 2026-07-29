<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearProduccionInsumosTable extends Migration
{
    public function up()
    {
        Schema::create('produccion_insumos', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('produccion_id');
            $table->unsignedBigInteger('insumo_id');
            $table->unsignedBigInteger('movimiento_inventario_id')->nullable();

            $table->decimal('cantidad_por_unidad', 12, 4);
            $table->decimal('cantidad_total', 12, 4);

            $table->decimal('costo_unitario', 12, 4)->default(0);
            $table->decimal('costo_total', 12, 2)->default(0);

            $table->timestamps();

            $table->foreign('produccion_id')
                ->references('id')
                ->on('producciones')
                ->cascadeOnDelete();

            $table->foreign('insumo_id')
                ->references('id')
                ->on('insumos');

            $table->foreign('movimiento_inventario_id')
                ->references('id')
                ->on('movimientos_inventario')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('produccion_insumos');
    }
}
