<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearOrdenTrabajoDetallesTable extends Migration
{
    public function up()
    {
        Schema::create('orden_trabajo_detalles', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('orden_trabajo_id');

            $table->string('tipo_item', 30); // Producto, Servicio, Otro

            $table->unsignedBigInteger('producto_id')->nullable();
            $table->unsignedBigInteger('servicio_id')->nullable();

            $table->string('descripcion', 200);
            $table->decimal('cantidad', 12, 2)->default(1);
            $table->decimal('precio_unitario', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);

            $table->text('observacion')->nullable();

            $table->timestamps();

            $table->foreign('orden_trabajo_id')
                ->references('id')
                ->on('ordenes_trabajo')
                ->cascadeOnDelete();

            $table->foreign('producto_id')
                ->references('id')
                ->on('productos')
                ->nullOnDelete();

            $table->foreign('servicio_id')
                ->references('id')
                ->on('servicios')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orden_trabajo_detalles');
    }
}
