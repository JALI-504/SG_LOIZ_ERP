<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearProduccionesTable extends Migration
{
    public function up()
    {
        Schema::create('producciones', function (Blueprint $table) {
            $table->id();

            $table->string('codigo', 30)->unique();
            $table->date('fecha');

            $table->unsignedBigInteger('producto_id');
            $table->decimal('cantidad', 12, 2);

            $table->decimal('costo_total', 12, 2)->default(0);
            $table->decimal('costo_unitario', 12, 4)->default(0);

            $table->unsignedBigInteger('movimiento_producto_id')->nullable();

            $table->string('estado', 30)->default('Registrada');
            $table->text('observacion')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();

            $table->timestamps();

            $table->foreign('producto_id')
                ->references('id')
                ->on('productos');

            $table->foreign('movimiento_producto_id')
                ->references('id')
                ->on('movimientos_producto')
                ->nullOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('producciones');
    }
}
