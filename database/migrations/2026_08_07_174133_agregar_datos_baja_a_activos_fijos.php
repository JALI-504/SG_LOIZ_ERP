<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AgregarDatosBajaAActivosFijos extends Migration
{
    public function up()
    {
        Schema::table('activos_fijos', function (Blueprint $table) {
            $table->string('tipo_baja', 50)->nullable()->after('fecha_baja');
            $table->string('documento_baja', 150)->nullable()->after('tipo_baja');
            $table->decimal('valor_recuperado', 14, 2)->default(0)->after('documento_baja');
        });
    }

    public function down()
    {
        Schema::table('activos_fijos', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_baja',
                'documento_baja',
                'valor_recuperado',
            ]);
        });
    }
}
