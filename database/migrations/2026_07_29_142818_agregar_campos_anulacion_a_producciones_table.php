<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AgregarCamposAnulacionAProduccionesTable extends Migration
{
    public function up()
    {
        Schema::table('producciones', function (Blueprint $table) {
            $table->dateTime('fecha_anulacion')->nullable()->after('estado');
            $table->unsignedBigInteger('anulado_por')->nullable()->after('fecha_anulacion');
            $table->text('motivo_anulacion')->nullable()->after('anulado_por');

            $table->foreign('anulado_por')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down()
    {
        Schema::table('producciones', function (Blueprint $table) {
            $table->dropForeign(['anulado_por']);
            $table->dropColumn([
                'fecha_anulacion',
                'anulado_por',
                'motivo_anulacion',
            ]);
        });
    }
}
