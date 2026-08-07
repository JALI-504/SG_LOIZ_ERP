<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoriaActivosTable extends Migration
{
    public function up()
    {
        Schema::create('categorias_activos', function (Blueprint $table) {
            $table->id();

            $table->string('codigo', 30)->unique()->nullable();
            $table->string('nombre', 150);
            $table->text('descripcion')->nullable();

            $table->boolean('depreciable')->default(true);
            $table->integer('vida_util_meses')->default(60);
            $table->decimal('porcentaje_depreciacion_anual', 8, 2)->default(20);

            $table->string('metodo_depreciacion', 80)->default('Linea recta');

            $table->boolean('activo')->default(true);

            $table->unsignedBigInteger('user_id')->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->index('nombre');
            $table->index('activo');
            $table->index('depreciable');
        });
    }

    public function down()
    {
        Schema::dropIfExists('categorias_activos');
    }
}
