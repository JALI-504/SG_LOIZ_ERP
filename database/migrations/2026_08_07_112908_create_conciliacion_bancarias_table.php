<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConciliacionBancariasTable extends Migration
{
    public function up()
    {
        Schema::create('conciliaciones_bancarias', function (Blueprint $table) {
            $table->id();

            $table->string('codigo', 30)->unique()->nullable();

            $table->unsignedBigInteger('cuenta_bancaria_id');

            $table->date('fecha_inicio');
            $table->date('fecha_fin');

            $table->decimal('saldo_inicial_sistema', 14, 2)->default(0);
            $table->decimal('total_entradas_sistema', 14, 2)->default(0);
            $table->decimal('total_salidas_sistema', 14, 2)->default(0);
            $table->decimal('saldo_final_sistema', 14, 2)->default(0);

            $table->decimal('saldo_final_banco', 14, 2)->default(0);
            $table->decimal('diferencia', 14, 2)->default(0);

            $table->integer('cantidad_movimientos')->default(0);

            $table->string('estado', 50)->default('Con diferencia');
            // Conciliada, Con diferencia, Anulada

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
            $table->index('fecha_inicio');
            $table->index('fecha_fin');
            $table->index('estado');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('conciliaciones_bancarias');
    }
}
