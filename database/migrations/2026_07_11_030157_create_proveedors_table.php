<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProveedorsTable extends Migration
{
    public function up()
    {
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();

            $table->string('codigo', 30)->unique();
            $table->string('nombre_comercial', 150);
            $table->string('nombre_legal', 150)->nullable();

            $table->string('tipo_proveedor', 80)->default('General');

            $table->string('rtn', 30)->nullable();
            $table->string('dni', 30)->nullable();

            $table->string('telefono', 30)->nullable();
            $table->string('whatsapp', 30)->nullable();
            $table->string('correo', 100)->nullable();

            $table->string('persona_contacto', 150)->nullable();
            $table->string('telefono_contacto', 30)->nullable();

            $table->text('direccion')->nullable();
            $table->text('observacion')->nullable();

            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('proveedores');
    }
}
