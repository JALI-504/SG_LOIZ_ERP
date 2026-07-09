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

            $table->enum('tipo_servicio', [
                'Impresion',
                'Fotocopia',
                'Escaneo',
                'Plastificado',
                'Laminado',
                'Diseno',
                'Otro'
            ])->default('Impresion');

            $table->enum('tamano_papel', [
                'Carta',
                'Oficio',
                'Legal',
                'A4',
                'Tabloide',
                'Personalizado',
                'fotografia 4x6"',
                'fotografia carta',
                'No aplica'
            ])->default('Carta');

            $table->enum('color', [
                'Blanco y negro',
                'Color',
                'No aplica'
            ])->default('Blanco y negro');

            $table->enum('caras', [
                'Una cara',
                'Doble cara',
                'No aplica'
            ])->default('Una cara');

            $table->enum('unidad_cobro', [
                'Pagina',
                'Hoja',
                'Unidad',
                'Minuto',
                'Hora',
                'Trabajo'
            ])->default('Pagina');

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
