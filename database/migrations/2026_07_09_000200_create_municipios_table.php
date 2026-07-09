<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMunicipiosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('municipios', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('departamento_id');

            $table->string('codigo', 4)->unique();
            $table->string('nombre', 100);
            $table->boolean('activo')->default(true);

            $table->timestamps();

            $table->foreign('departamento_id')
                ->references('id')
                ->on('departamentos')
                ->onUpdate('cascade')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('municipios');
    }
}
