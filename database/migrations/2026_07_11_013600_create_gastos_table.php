<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGastosTable extends Migration
{
    public function up()
    {
        Schema::create('gastos', function (Blueprint $table) {
            $table->id();

            $table->date('fecha');
            $table->time('hora')->nullable();

            $table->string('categoria', 100);
            $table->string('descripcion', 200);

            $table->decimal('monto', 12, 2)->default(0);

            $table->string('metodo_pago', 50)->default('Efectivo');
            $table->string('referencia', 100)->nullable();

            $table->string('proveedor', 150)->nullable();
            $table->text('observacion')->nullable();

            $table->string('estado', 30)->default('Registrado');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('gastos');
    }
}
