<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRespaldoSistemasTable extends Migration
{
    public function up()
    {
        Schema::create('respaldo_sistemas', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('nombre_archivo', 255);
            $table->string('ruta_archivo', 500);
            $table->string('tipo', 50)->default('Base de datos');

            $table->decimal('tamano_mb', 12, 2)->default(0);

            $table->string('estado', 50)->default('Generado');
            $table->text('observacion')->nullable();

            $table->timestamp('fecha_generacion')->nullable();

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();

            $table->index('user_id');
            $table->index('estado');
            $table->index('fecha_generacion');
        });
    }

    public function down()
    {
        Schema::dropIfExists('respaldo_sistemas');
    }
}
