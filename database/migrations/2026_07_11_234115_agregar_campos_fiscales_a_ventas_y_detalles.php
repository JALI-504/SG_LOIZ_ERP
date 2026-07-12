<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AgregarCamposFiscalesAVentasYDetalles extends Migration
{
    public function up()
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->decimal('subtotal_gravado', 12, 2)
                ->default(0)
                ->after('descuento');

            $table->decimal('subtotal_exento', 12, 2)
                ->default(0)
                ->after('subtotal_gravado');

            $table->decimal('subtotal_no_sujeto', 12, 2)
                ->default(0)
                ->after('subtotal_exento');

            $table->decimal('isv_15', 12, 2)
                ->default(0)
                ->after('subtotal_no_sujeto');

            $table->decimal('retencion', 12, 2)
                ->default(0)
                ->after('total');

            $table->decimal('neto_recibido', 12, 2)
                ->default(0)
                ->after('retencion');
        });

        Schema::table('venta_detalles', function (Blueprint $table) {
            $table->string('tipo_impuesto', 50)
                ->default('Gravado 15%')
                ->after('costo_unitario');

            $table->decimal('porcentaje_isv', 5, 2)
                ->default(15)
                ->after('tipo_impuesto');

            $table->decimal('subtotal_gravado', 12, 2)
                ->default(0)
                ->after('descuento');

            $table->decimal('subtotal_exento', 12, 2)
                ->default(0)
                ->after('subtotal_gravado');

            $table->decimal('subtotal_no_sujeto', 12, 2)
                ->default(0)
                ->after('subtotal_exento');

            $table->decimal('impuesto', 12, 2)
                ->default(0)
                ->after('subtotal_no_sujeto');
        });
    }

    public function down()
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn([
                'subtotal_gravado',
                'subtotal_exento',
                'subtotal_no_sujeto',
                'isv_15',
                'retencion',
                'neto_recibido',
            ]);
        });

        Schema::table('venta_detalles', function (Blueprint $table) {
            $table->dropColumn([
                'tipo_impuesto',
                'porcentaje_isv',
                'subtotal_gravado',
                'subtotal_exento',
                'subtotal_no_sujeto',
                'impuesto',
            ]);
        });
    }
}
