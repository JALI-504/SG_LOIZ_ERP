<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AgregarModoFiscalYFacturacionAConfiguracionYVentas extends Migration
{
    public function up()
    {
        Schema::table('configuracion_empresas', function (Blueprint $table) {
            $table->string('modo_fiscal', 30)
                ->default('Interno')
                ->after('usa_facturacion_fiscal');

            $table->string('documento_venta_activo', 50)
                ->default('Recibo interno')
                ->after('modo_fiscal');

            $table->boolean('usa_impuestos')
                ->default(false)
                ->after('documento_venta_activo');

            $table->boolean('usa_retenciones')
                ->default(false)
                ->after('usa_impuestos');

            $table->boolean('precios_incluyen_isv')
                ->default(true)
                ->after('usa_retenciones');

            $table->decimal('porcentaje_isv_general', 5, 2)
                ->default(15)
                ->after('precios_incluyen_isv');

            $table->string('establecimiento', 3)
                ->default('000')
                ->after('porcentaje_isv_general');

            $table->string('punto_emision', 3)
                ->default('001')
                ->after('establecimiento');

            $table->string('tipo_documento_fiscal', 2)
                ->default('01')
                ->after('punto_emision');

            $table->unsignedInteger('numero_actual_factura')
                ->default(0)
                ->after('numero_actual_recibo');
        });

        Schema::table('ventas', function (Blueprint $table) {
            $table->boolean('es_fiscal')
                ->default(false)
                ->after('tipo_comprobante');

            $table->string('cai', 100)
                ->nullable()
                ->after('es_fiscal');

            $table->string('rango_autorizado_desde', 50)
                ->nullable()
                ->after('cai');

            $table->string('rango_autorizado_hasta', 50)
                ->nullable()
                ->after('rango_autorizado_desde');

            $table->date('fecha_limite_emision')
                ->nullable()
                ->after('rango_autorizado_hasta');
        });
    }

    public function down()
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn([
                'es_fiscal',
                'cai',
                'rango_autorizado_desde',
                'rango_autorizado_hasta',
                'fecha_limite_emision',
            ]);
        });

        Schema::table('configuracion_empresas', function (Blueprint $table) {
            $table->dropColumn([
                'modo_fiscal',
                'documento_venta_activo',
                'usa_impuestos',
                'usa_retenciones',
                'precios_incluyen_isv',
                'porcentaje_isv_general',
                'establecimiento',
                'punto_emision',
                'tipo_documento_fiscal',
                'numero_actual_factura',
            ]);
        });
    }
}
