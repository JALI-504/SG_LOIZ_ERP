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
            $table->string('codigo_barra', 100)->nullable()->unique();

            $table->string('nombre', 150);

            // Estos valores vendrán desde catálogos
            $table->string('categoria', 50)->default('Personalizado');
            $table->string('tipo_producto', 50)->default('Fabricado');
            $table->string('unidad_venta', 50)->default('Unidad');

            // Control del comportamiento del producto
            $table->boolean('maneja_inventario')->default(true);
            $table->boolean('usa_receta')->default(false);

            // Medidas opcionales del producto terminado
            $table->decimal('ancho_cm', 10, 2)->nullable();
            $table->decimal('largo_cm', 10, 2)->nullable();
            $table->decimal('espesor_mm', 10, 2)->nullable();

            // Inventario de producto terminado
            $table->decimal('stock_actual', 12, 2)->default(0);
            $table->decimal('stock_minimo', 12, 2)->default(0);

            // Costos y precio
            $table->decimal('costo_compra', 12, 2)->default(0);
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
