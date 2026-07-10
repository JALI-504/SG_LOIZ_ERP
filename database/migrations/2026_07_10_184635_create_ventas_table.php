<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVentasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();

            $table->string('numero', 30)->unique();

            $table->unsignedBigInteger('cliente_id')->nullable();

            $table->date('fecha');
            $table->time('hora')->nullable();

            $table->string('tipo_comprobante', 50)->default('Recibo interno');
            $table->string('metodo_pago', 50)->default('Efectivo');

            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('descuento', 12, 2)->default(0);
            $table->decimal('impuesto', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);

            $table->string('estado', 30)->default('Pagada');

            $table->text('observacion')->nullable();

            $table->timestamps();

            $table->foreign('cliente_id')
                ->references('id')
                ->on('clientes')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ventas');
    }
}
