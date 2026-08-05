<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CrearBitacorasSistemaTable extends Migration
{
    public function up()
    {
        Schema::create('bitacoras_sistema', function (Blueprint $table) {
            $table->id();

            $table->date('fecha');
            $table->time('hora');

            $table->unsignedBigInteger('user_id')->nullable();

            $table->string('modulo', 100);
            $table->string('accion', 100);
            $table->text('descripcion')->nullable();

            $table->string('modelo', 150)->nullable();
            $table->unsignedBigInteger('modelo_id')->nullable();

            $table->string('url', 255)->nullable();
            $table->string('ip', 60)->nullable();
            $table->text('user_agent')->nullable();

            $table->longText('datos_anteriores')->nullable();
            $table->longText('datos_nuevos')->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->index('fecha');
            $table->index('modulo');
            $table->index('accion');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('bitacoras_sistema');
    }
}
