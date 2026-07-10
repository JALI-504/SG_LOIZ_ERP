<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfiguracionEmpresasTable extends Migration
{
    public function up()
    {
        Schema::create('configuracion_empresas', function (Blueprint $table) {
            $table->id();

            $table->string('nombre_comercial', 150);
            $table->string('nombre_legal', 150)->nullable();
            $table->string('rtn', 30)->nullable();

            $table->string('telefono', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('correo', 100)->nullable();
            $table->text('direccion')->nullable();

            $table->string('descripcion_negocio', 200)->nullable();
            $table->string('logo', 255)->nullable();

            $table->boolean('usa_facturacion_fiscal')->default(false);

            $table->string('cai', 100)->nullable();
            $table->string('rango_desde', 50)->nullable();
            $table->string('rango_hasta', 50)->nullable();
            $table->date('fecha_limite_emision')->nullable();

            $table->string('prefijo_recibo', 10)->default('REC');
            $table->unsignedInteger('numero_actual_recibo')->default(0);

            $table->text('mensaje_recibo')->nullable();

            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('configuracion_empresas');
    }
}
