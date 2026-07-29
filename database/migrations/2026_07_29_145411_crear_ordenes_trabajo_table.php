<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearOrdenesTrabajoTable extends Migration
{
    public function up()
    {
        Schema::create('ordenes_trabajo', function (Blueprint $table) {
            $table->id();

            $table->string('codigo', 30)->unique();

            $table->date('fecha');
            $table->date('fecha_entrega')->nullable();

            $table->unsignedBigInteger('cliente_id')->nullable();

            $table->string('cliente_nombre', 150)->nullable();
            $table->string('cliente_telefono', 30)->nullable();

            $table->string('titulo', 150);
            $table->text('descripcion')->nullable();

            $table->string('estado', 30)->default('Pendiente');
            $table->string('prioridad', 30)->default('Normal');

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('abono', 12, 2)->default(0);
            $table->decimal('saldo', 12, 2)->default(0);

            $table->unsignedBigInteger('venta_id')->nullable();

            $table->text('observacion')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();

            $table->dateTime('fecha_anulacion')->nullable();
            $table->unsignedBigInteger('anulado_por')->nullable();
            $table->text('motivo_anulacion')->nullable();

            $table->timestamps();

            $table->foreign('cliente_id')
                ->references('id')
                ->on('clientes')
                ->nullOnDelete();

            $table->foreign('venta_id')
                ->references('id')
                ->on('ventas')
                ->nullOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->foreign('anulado_por')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ordenes_trabajo');
    }
}
