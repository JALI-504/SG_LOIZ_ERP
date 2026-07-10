<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiciosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();

            $table->string('codigo', 20)->unique();
            $table->string('nombre', 150);

            $table->string('tipo_servicio', 50)->default('Impresion');
            $table->string('tamano_papel', 50)->default('Carta');
            $table->string('color', 50)->default('Blanco y negro');
            $table->string('caras', 50)->default('Una cara');
            $table->string('unidad_cobro', 50)->default('Pagina');

            $table->decimal('costo_unitario', 10, 2)->default(0);
            $table->decimal('precio_unitario', 10, 2)->default(0);

            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('servicios');
    }
}
