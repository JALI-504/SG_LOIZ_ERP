<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AgregarCodigoYRequisitosACategoriasActivos extends Migration
{
    public function up()
    {
        Schema::table('categorias_activos', function (Blueprint $table) {
            $table->string('prefijo_codigo', 10)->nullable()->after('codigo');
            $table->boolean('requiere_numero_serie')->default(false)->after('metodo_depreciacion');
            $table->boolean('requiere_marca_modelo')->default(false)->after('requiere_numero_serie');
            $table->boolean('requiere_responsable')->default(false)->after('requiere_marca_modelo');
        });
    }

    public function down()
    {
        Schema::table('categorias_activos', function (Blueprint $table) {
            $table->dropColumn([
                'prefijo_codigo',
                'requiere_numero_serie',
                'requiere_marca_modelo',
                'requiere_responsable',
            ]);
        });
    }
}
