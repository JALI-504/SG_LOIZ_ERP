<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVentaDetallesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('venta_detalles', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('venta_id');

            // Producto o Servicio
            $table->string('tipo_item', 30);

            // ID del producto o servicio vendido
            $table->unsignedBigInteger('item_id')->nullable();

            $table->string('codigo', 50)->nullable();
            $table->string('descripcion', 200);

            $table->decimal('cantidad', 12, 2)->default(1);
            $table->decimal('precio_unitario', 12, 2)->default(0);
            $table->decimal('costo_unitario', 12, 4)->default(0);

            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->timestamps();

            $table->foreign('venta_id')
                ->references('id')
                ->on('ventas')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('venta_detalles');
    }
}
