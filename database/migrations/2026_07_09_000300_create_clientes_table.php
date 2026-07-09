<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();

            $table->string('primer_nombre', 50);
            $table->string('segundo_nombre', 50)->nullable();

            $table->string('primer_apellido', 50);
            $table->string('segundo_apellido', 50)->nullable();

            $table->string('codigo_pais', 5)->default('+504');
            $table->string('telefono', 8);
            $table->string('correo', 150)->nullable();

            $table->string('dni', 13)->unique();
            $table->string('rtn', 14)->nullable();

            $table->enum('tipo_cliente', [
                'Natural',
                'Empresa',
                'Institucion',
                'Mayorista',
                'Corporativo'
            ])->default('Natural');

            $table->foreignId('departamento_id')
                ->constrained('departamentos')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreignId('municipio_id')
                ->constrained('municipios')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->text('direccion_referencia');

            $table->text('notas')->nullable();
            $table->boolean('activo')->default(true);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clientes');
    }
}
