<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AgregarCamposBancariosAPagosGastos extends Migration
{
    public function up()
    {
        if (Schema::hasTable('pago_ventas')) {
            Schema::table('pago_ventas', function (Blueprint $table) {
                if (!Schema::hasColumn('pago_ventas', 'cuenta_bancaria_id')) {
                    $table->unsignedBigInteger('cuenta_bancaria_id')->nullable()->after('metodo_pago');
                    $table->index('cuenta_bancaria_id', 'pago_ventas_cuenta_bancaria_id_index');
                }

                if (!Schema::hasColumn('pago_ventas', 'movimiento_bancario_id')) {
                    $table->unsignedBigInteger('movimiento_bancario_id')->nullable()->after('cuenta_bancaria_id');
                    $table->index('movimiento_bancario_id', 'pago_ventas_movimiento_bancario_id_index');
                }
            });

            Schema::table('pago_ventas', function (Blueprint $table) {
                if (Schema::hasColumn('pago_ventas', 'cuenta_bancaria_id')) {
                    $table->foreign('cuenta_bancaria_id', 'pago_ventas_cuenta_bancaria_id_foreign')
                        ->references('id')
                        ->on('cuentas_bancarias')
                        ->onDelete('set null');
                }

                if (Schema::hasColumn('pago_ventas', 'movimiento_bancario_id')) {
                    $table->foreign('movimiento_bancario_id', 'pago_ventas_movimiento_bancario_id_foreign')
                        ->references('id')
                        ->on('movimientos_bancarios')
                        ->onDelete('set null');
                }
            });
        }

        if (Schema::hasTable('pago_compras')) {
            Schema::table('pago_compras', function (Blueprint $table) {
                if (!Schema::hasColumn('pago_compras', 'cuenta_bancaria_id')) {
                    $table->unsignedBigInteger('cuenta_bancaria_id')->nullable()->after('metodo_pago');
                    $table->index('cuenta_bancaria_id', 'pago_compras_cuenta_bancaria_id_index');
                }

                if (!Schema::hasColumn('pago_compras', 'movimiento_bancario_id')) {
                    $table->unsignedBigInteger('movimiento_bancario_id')->nullable()->after('cuenta_bancaria_id');
                    $table->index('movimiento_bancario_id', 'pago_compras_movimiento_bancario_id_index');
                }
            });

            Schema::table('pago_compras', function (Blueprint $table) {
                if (Schema::hasColumn('pago_compras', 'cuenta_bancaria_id')) {
                    $table->foreign('cuenta_bancaria_id', 'pago_compras_cuenta_bancaria_id_foreign')
                        ->references('id')
                        ->on('cuentas_bancarias')
                        ->onDelete('set null');
                }

                if (Schema::hasColumn('pago_compras', 'movimiento_bancario_id')) {
                    $table->foreign('movimiento_bancario_id', 'pago_compras_movimiento_bancario_id_foreign')
                        ->references('id')
                        ->on('movimientos_bancarios')
                        ->onDelete('set null');
                }
            });
        }

        if (Schema::hasTable('gastos')) {
            Schema::table('gastos', function (Blueprint $table) {
                if (!Schema::hasColumn('gastos', 'cuenta_bancaria_id')) {
                    $table->unsignedBigInteger('cuenta_bancaria_id')->nullable()->after('metodo_pago');
                    $table->index('cuenta_bancaria_id', 'gastos_cuenta_bancaria_id_index');
                }

                if (!Schema::hasColumn('gastos', 'movimiento_bancario_id')) {
                    $table->unsignedBigInteger('movimiento_bancario_id')->nullable()->after('cuenta_bancaria_id');
                    $table->index('movimiento_bancario_id', 'gastos_movimiento_bancario_id_index');
                }
            });

            Schema::table('gastos', function (Blueprint $table) {
                if (Schema::hasColumn('gastos', 'cuenta_bancaria_id')) {
                    $table->foreign('cuenta_bancaria_id', 'gastos_cuenta_bancaria_id_foreign')
                        ->references('id')
                        ->on('cuentas_bancarias')
                        ->onDelete('set null');
                }

                if (Schema::hasColumn('gastos', 'movimiento_bancario_id')) {
                    $table->foreign('movimiento_bancario_id', 'gastos_movimiento_bancario_id_foreign')
                        ->references('id')
                        ->on('movimientos_bancarios')
                        ->onDelete('set null');
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('gastos')) {
            Schema::table('gastos', function (Blueprint $table) {
                if (Schema::hasColumn('gastos', 'movimiento_bancario_id')) {
                    $table->dropForeign('gastos_movimiento_bancario_id_foreign');
                    $table->dropIndex('gastos_movimiento_bancario_id_index');
                    $table->dropColumn('movimiento_bancario_id');
                }

                if (Schema::hasColumn('gastos', 'cuenta_bancaria_id')) {
                    $table->dropForeign('gastos_cuenta_bancaria_id_foreign');
                    $table->dropIndex('gastos_cuenta_bancaria_id_index');
                    $table->dropColumn('cuenta_bancaria_id');
                }
            });
        }

        if (Schema::hasTable('pago_compras')) {
            Schema::table('pago_compras', function (Blueprint $table) {
                if (Schema::hasColumn('pago_compras', 'movimiento_bancario_id')) {
                    $table->dropForeign('pago_compras_movimiento_bancario_id_foreign');
                    $table->dropIndex('pago_compras_movimiento_bancario_id_index');
                    $table->dropColumn('movimiento_bancario_id');
                }

                if (Schema::hasColumn('pago_compras', 'cuenta_bancaria_id')) {
                    $table->dropForeign('pago_compras_cuenta_bancaria_id_foreign');
                    $table->dropIndex('pago_compras_cuenta_bancaria_id_index');
                    $table->dropColumn('cuenta_bancaria_id');
                }
            });
        }

        if (Schema::hasTable('pago_ventas')) {
            Schema::table('pago_ventas', function (Blueprint $table) {
                if (Schema::hasColumn('pago_ventas', 'movimiento_bancario_id')) {
                    $table->dropForeign('pago_ventas_movimiento_bancario_id_foreign');
                    $table->dropIndex('pago_ventas_movimiento_bancario_id_index');
                    $table->dropColumn('movimiento_bancario_id');
                }

                if (Schema::hasColumn('pago_ventas', 'cuenta_bancaria_id')) {
                    $table->dropForeign('pago_ventas_cuenta_bancaria_id_foreign');
                    $table->dropIndex('pago_ventas_cuenta_bancaria_id_index');
                    $table->dropColumn('cuenta_bancaria_id');
                }
            });
        }
    }
}
