<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAperturaCajasTable extends Migration
{
    public function up()
    {
        Schema::create('aperturas_caja', function (Blueprint $table) {
            $table->id();

            $table->string('codigo', 30)->unique()->nullable();

            $table->date('fecha');
            $table->time('hora_apertura');

            $table->unsignedBigInteger('user_id')->nullable();

            $table->decimal('monto_inicial', 12, 2)->default(0);

            $table->string('estado', 50)->default('Abierta');
            // Estados recomendados:
            // Abierta, Cerrada, Anulada

            $table->text('observacion')->nullable();

            $table->timestamp('fecha_anulacion')->nullable();
            $table->unsignedBigInteger('anulado_por')->nullable();
            $table->text('motivo_anulacion')->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('anulado_por')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->index('fecha');
            $table->index('estado');
            $table->index('user_id');
        });

        if (Schema::hasTable('cierres_caja') && !Schema::hasColumn('cierres_caja', 'apertura_caja_id')) {
            Schema::table('cierres_caja', function (Blueprint $table) {
                $table->unsignedBigInteger('apertura_caja_id')->nullable()->after('id');

                $table->foreign('apertura_caja_id', 'cierres_caja_apertura_caja_id_foreign')
                    ->references('id')
                    ->on('aperturas_caja')
                    ->onDelete('set null');

                $table->unique('apertura_caja_id', 'cierres_caja_apertura_caja_id_unique');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('cierres_caja') && Schema::hasColumn('cierres_caja', 'apertura_caja_id')) {
            Schema::table('cierres_caja', function (Blueprint $table) {
                $table->dropForeign('cierres_caja_apertura_caja_id_foreign');
                $table->dropUnique('cierres_caja_apertura_caja_id_unique');
                $table->dropColumn('apertura_caja_id');
            });
        }

        Schema::dropIfExists('aperturas_caja');
    }
}
