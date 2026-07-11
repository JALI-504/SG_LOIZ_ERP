<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompraDetallesTable extends Migration
{
    public function up()
    {
        Schema::create('compra_detalles', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('compra_id');

            $table->string('tipo_item', 30); // Insumo / Producto
            $table->unsignedBigInteger('item_id')->nullable();

            $table->string('codigo', 50)->nullable();
            $table->string('descripcion', 200);

            $table->decimal('cantidad', 12, 2)->default(1);
            $table->decimal('costo_unitario', 12, 4)->default(0);

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->timestamps();

            $table->foreign('compra_id')
                ->references('id')
                ->on('compras')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('compra_detalles');
    }
}
