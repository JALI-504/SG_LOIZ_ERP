<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMovimientoBancariosTable extends Migration
{
    public function up()
    {
        Schema::create('movimientos_bancarios', function (Blueprint $table) {
            $table->id();

            $table->string('codigo', 30)->unique()->nullable();

            $table->unsignedBigInteger('cuenta_bancaria_id');

            $table->date('fecha');
            $table->time('hora')->nullable();

            // Entrada o Salida
            $table->string('tipo', 30);

            // Depósito, Retiro, Transferencia, Ajuste, Otro
            $table->string('categoria', 80)->nullable();

            $table->string('referencia', 150)->nullable();
            $table->text('descripcion')->nullable();

            $table->decimal('monto', 14, 2)->default(0);

            $table->decimal('saldo_anterior', 14, 2)->default(0);
            $table->decimal('saldo_nuevo', 14, 2)->default(0);

            // Manual, Venta, Abono cliente, Gasto, Pago proveedor, etc.
            $table->string('origen', 80)->default('Manual');
            $table->unsignedBigInteger('origen_id')->nullable();

            $table->string('estado', 50)->default('Activo');
            // Activo, Anulado

            $table->text('observacion')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();

            $table->timestamp('fecha_anulacion')->nullable();
            $table->unsignedBigInteger('anulado_por')->nullable();
            $table->text('motivo_anulacion')->nullable();

            $table->timestamps();

            $table->foreign('cuenta_bancaria_id')
                ->references('id')
                ->on('cuentas_bancarias')
                ->onDelete('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('anulado_por')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->index('cuenta_bancaria_id');
            $table->index('fecha');
            $table->index('tipo');
            $table->index('categoria');
            $table->index('estado');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('movimientos_bancarios');
    }
}
