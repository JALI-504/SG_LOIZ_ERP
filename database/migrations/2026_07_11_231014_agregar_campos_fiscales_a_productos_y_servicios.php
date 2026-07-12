<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AgregarCamposFiscalesAProductosYServicios extends Migration
{
    public function up()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->string('tipo_impuesto', 50)
                ->default('Gravado 15%')
                ->after('precio_venta');

            $table->decimal('porcentaje_isv', 5, 2)
                ->default(15)
                ->after('tipo_impuesto');
        });

        Schema::table('servicios', function (Blueprint $table) {
            $table->string('tipo_impuesto', 50)
                ->default('Gravado 15%')
                ->after('precio_unitario');

            $table->decimal('porcentaje_isv', 5, 2)
                ->default(15)
                ->after('tipo_impuesto');
        });
    }

    public function down()
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_impuesto',
                'porcentaje_isv',
            ]);
        });

        Schema::table('servicios', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_impuesto',
                'porcentaje_isv',
            ]);
        });
    }
}
