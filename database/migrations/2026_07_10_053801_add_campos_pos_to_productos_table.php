<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCamposPosToProductosTable extends Migration
{
    public function up()
    {
        Schema::table('productos', function (Blueprint $table) {
            if (!Schema::hasColumn('productos', 'codigo_barra')) {
                $table->string('codigo_barra', 100)->nullable()->unique()->after('codigo');
            }

            if (!Schema::hasColumn('productos', 'maneja_inventario')) {
                $table->boolean('maneja_inventario')->default(true)->after('unidad_venta');
            }

            if (!Schema::hasColumn('productos', 'usa_receta')) {
                $table->boolean('usa_receta')->default(false)->after('maneja_inventario');
            }

            if (!Schema::hasColumn('productos', 'stock_actual')) {
                $table->decimal('stock_actual', 12, 2)->default(0)->after('espesor_mm');
            }

            if (!Schema::hasColumn('productos', 'stock_minimo')) {
                $table->decimal('stock_minimo', 12, 2)->default(0)->after('stock_actual');
            }

            if (!Schema::hasColumn('productos', 'costo_compra')) {
                $table->decimal('costo_compra', 12, 2)->default(0)->after('stock_minimo');
            }
        });
    }

    public function down()
    {
        Schema::table('productos', function (Blueprint $table) {
            if (Schema::hasColumn('productos', 'codigo_barra')) {
                $table->dropUnique(['codigo_barra']);
                $table->dropColumn('codigo_barra');
            }

            if (Schema::hasColumn('productos', 'maneja_inventario')) {
                $table->dropColumn('maneja_inventario');
            }

            if (Schema::hasColumn('productos', 'usa_receta')) {
                $table->dropColumn('usa_receta');
            }

            if (Schema::hasColumn('productos', 'stock_actual')) {
                $table->dropColumn('stock_actual');
            }

            if (Schema::hasColumn('productos', 'stock_minimo')) {
                $table->dropColumn('stock_minimo');
            }

            if (Schema::hasColumn('productos', 'costo_compra')) {
                $table->dropColumn('costo_compra');
            }
        });
    }
}
