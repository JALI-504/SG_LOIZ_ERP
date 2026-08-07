<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivoFijosTable extends Migration
{
    public function up()
    {
        Schema::create('activos_fijos', function (Blueprint $table) {
            $table->id();

            $table->string('codigo', 30)->unique()->nullable();

            $table->unsignedBigInteger('categoria_activo_id');

            $table->string('nombre', 180);
            $table->text('descripcion')->nullable();

            $table->date('fecha_compra')->nullable();
            $table->date('fecha_inicio_uso')->nullable();

            $table->decimal('valor_compra', 14, 2)->default(0);
            $table->decimal('valor_residual', 14, 2)->default(0);
            $table->decimal('valor_depreciable', 14, 2)->default(0);

            $table->integer('vida_util_meses')->default(60);
            $table->decimal('depreciacion_mensual', 14, 2)->default(0);
            $table->decimal('depreciacion_acumulada', 14, 2)->default(0);
            $table->decimal('valor_en_libros', 14, 2)->default(0);

            $table->string('ubicacion', 150)->nullable();
            $table->string('responsable', 150)->nullable();

            $table->string('proveedor', 150)->nullable();
            $table->string('documento_compra', 150)->nullable();

            $table->string('numero_serie', 150)->nullable();
            $table->string('marca', 100)->nullable();
            $table->string('modelo', 100)->nullable();

            $table->string('estado', 50)->default('Activo');
            // Activo, En mantenimiento, Dañado, Vendido, Dado de baja

            $table->date('fecha_baja')->nullable();
            $table->text('motivo_baja')->nullable();

            $table->text('observacion')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();

            $table->timestamps();

            $table->foreign('categoria_activo_id')
                ->references('id')
                ->on('categorias_activos')
                ->onDelete('restrict');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->index('codigo');
            $table->index('categoria_activo_id');
            $table->index('estado');
            $table->index('fecha_compra');
            $table->index('fecha_inicio_uso');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('activos_fijos');
    }
}
