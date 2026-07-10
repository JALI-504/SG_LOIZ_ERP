<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovimientoProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movimientos_producto', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('producto_id');

            $table->enum('tipo_movimiento', [
                'Entrada compra',
                'Entrada produccion',
                'Entrada ajuste',
                'Salida venta',
                'Salida daño',
                'Salida ajuste',
                'Devolucion'
            ]);

            $table->decimal('cantidad', 12, 2);
            $table->decimal('costo_unitario', 12, 4)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->string('referencia', 100)->nullable();
            $table->text('observacion')->nullable();

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
        Schema::dropIfExists('movimiento_productos');
    }
}
