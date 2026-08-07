<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCuentaBancariasTable extends Migration
{
    public function up()
    {
        Schema::create('cuentas_bancarias', function (Blueprint $table) {
            $table->id();

            $table->string('codigo', 30)->unique()->nullable();

            $table->string('banco', 150);
            $table->string('nombre_cuenta', 150);
            $table->string('numero_cuenta', 100)->nullable();
            $table->string('tipo_cuenta', 50)->nullable();
            $table->string('moneda', 10)->default('HNL');

            $table->decimal('saldo_inicial', 14, 2)->default(0);
            $table->decimal('saldo_actual', 14, 2)->default(0);

            $table->boolean('activo')->default(true);

            $table->text('observacion')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();

            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');

            $table->index('banco');
            $table->index('activo');
            $table->index('user_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cuentas_bancarias');
    }
}
