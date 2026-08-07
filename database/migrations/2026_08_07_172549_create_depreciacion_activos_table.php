<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepreciacionActivosTable extends Migration
{
    public function up()
    {
        Schema::create('depreciaciones_activos', function (Blueprint $table) {
            $table->id();

            $table->string('codigo', 30)->unique()->nullable();

            $table->unsignedBigInteger('activo_fijo_id');

            $table->string('periodo', 7);
            // Formato: 2026-08

            $table->date('fecha_depreciacion');

            $table->decimal('monto', 14, 2)->default(0);

            $table->decimal('depreciacion_acumulada_anterior', 14, 2)->default(0);
            $table->decimal('depreciacion_acumulada_nueva', 14, 2)->default(0);

            $table->decimal('valor_en_libros_anterior', 14, 2)->default(0);
            $table->decimal('valor_en_libros_nuevo', 14, 2)->default(0);

            $table->string('estado', 30)->default('Registrada');
            // Registrada, Anulada

            $table->text('observacion')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();

            $table->timestamp('fecha_anulacion')->nullable();
            $table->unsignedBigInteger('anulado_por')->nullable();
            $table->text('motivo_anulacion')->nullable();

            $table->timestamps();

            $table->foreign('activo_fijo_id')
                ->references('id')
                ->on('activos_fijos')
                ->onDelete('restrict');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('anulado_por')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->unique(['activo_fijo_id', 'periodo']);

            $table->index('periodo');
            $table->index('fecha_depreciacion');
            $table->index('estado');
        });
    }

    public function down()
    {
        Schema::dropIfExists('depreciaciones_activos');
    }
}
