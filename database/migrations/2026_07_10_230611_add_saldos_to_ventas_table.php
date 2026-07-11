<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddSaldosToVentasTable extends Migration
{
    public function up()
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->decimal('monto_pagado', 12, 2)->default(0)->after('total');
            $table->decimal('saldo_pendiente', 12, 2)->default(0)->after('monto_pagado');
        });

        DB::table('ventas')
            ->where('estado', 'Pagada')
            ->update([
                'monto_pagado' => DB::raw('total'),
                'saldo_pendiente' => 0,
            ]);

        DB::table('ventas')
            ->where('estado', 'Pendiente')
            ->update([
                'monto_pagado' => 0,
                'saldo_pendiente' => DB::raw('total'),
            ]);

        DB::table('ventas')
            ->where('estado', 'Anulada')
            ->update([
                'monto_pagado' => 0,
                'saldo_pendiente' => 0,
            ]);
    }

    public function down()
    {
        Schema::table('ventas', function (Blueprint $table) {
            $table->dropColumn('monto_pagado');
            $table->dropColumn('saldo_pendiente');
        });
    }
}
