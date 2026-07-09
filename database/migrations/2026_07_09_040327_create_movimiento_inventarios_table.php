<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovimientoInventariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('insumo_id');

            $table->enum('tipo_movimiento', [
                'Entrada compra',
                'Entrada ajuste',
                'Salida venta',
                'Salida daño',
                'Salida prueba',
                'Salida ajuste',
                'Devolucion'
            ]);

            $table->decimal('cantidad', 12, 2);
            $table->decimal('costo_unitario', 12, 4)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            // Para relacionarlo luego con ventas, cotizaciones u órdenes
            $table->string('referencia', 100)->nullable();

            $table->text('observacion')->nullable();

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
        Schema::dropIfExists('movimiento_inventarios');
    }
}
