<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCatalogosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('catalogos', function (Blueprint $table) {
            $table->id();

            $table->string('tipo', 50);
            $table->string('nombre', 100);

            $table->string('descripcion', 200)->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);

            $table->timestamps();

            $table->unique(['tipo', 'nombre']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('catalogos');
    }
}
