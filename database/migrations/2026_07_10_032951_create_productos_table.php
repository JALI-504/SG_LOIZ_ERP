<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('productos', function (Blueprint $table) {
            $table->id();

            $table->string('codigo', 30)->unique();
            $table->string('nombre', 150);

            $table->enum('categoria', [
                'Llavero',
                'Placa',
                'Dije',
                'Reconocimiento',
                'Adorno',
                'Souvenir',
                'Personalizado',
                'Otro'
            ])->default('Personalizado');

            $table->enum('tipo_producto', [
                'Laser',
                'Impresion',
                'Mixto',
                'Otro'
            ])->default('Laser');

            $table->enum('unidad_venta', [
                'Unidad',
                'Paquete',
                'Docena',
                'Trabajo'
            ])->default('Unidad');

            // Medidas opcionales del producto terminado
            $table->decimal('ancho_cm', 10, 2)->nullable();
            $table->decimal('largo_cm', 10, 2)->nullable();
            $table->decimal('espesor_mm', 10, 2)->nullable();

            // Costos y precio
            $table->decimal('costo_unitario', 12, 2)->default(0);
            $table->decimal('precio_venta', 12, 2)->default(0);

            $table->text('descripcion')->nullable();
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
        Schema::dropIfExists('productos');
    }
}
