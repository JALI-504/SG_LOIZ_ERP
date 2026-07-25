<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AgregarEstadoAnulacionAPagosComprasTable extends Migration
{
    public function up()
    {
        Schema::table('pago_compras', function (Blueprint $table) {
            $table->string('estado', 20)->default('Activo')->after('observacion');
            $table->dateTime('fecha_anulacion')->nullable()->after('estado');
            $table->text('observacion_anulacion')->nullable()->after('fecha_anulacion');
        });
    }

    public function down()
    {
        Schema::table('pago_compras', function (Blueprint $table) {
            $table->dropColumn([
                'estado',
                'fecha_anulacion',
                'observacion_anulacion',
            ]);
        });
    }
}
