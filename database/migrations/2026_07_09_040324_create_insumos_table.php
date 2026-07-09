<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInsumosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('insumos', function (Blueprint $table) {
            $table->id();

            $table->string('codigo', 30)->unique();
            $table->string('nombre', 150);

            $table->enum('categoria', [
                'Papel',
                'Tinta',
                'Toner',
                'Madera',
                'Acrilico',
                'Cuero',
                'Metal',
                'Herraje',
                'Empaque',
                'Adhesivo',
                'Laser',
                'Herramienta',
                'Otro'
            ])->default('Otro');

            // Forma en que compras el insumo
            $table->string('unidad_compra', 50);
            // Ejemplo: Resma, Pliego, Lamina, Paquete, Botella, Rollo

            // Cantidad que trae la compra en unidad de consumo
            $table->decimal('cantidad_por_compra', 12, 2)->default(0);
            // Ejemplo: 500 hojas, 29768 cm2, 100 unidades, 1000 ml

            // Forma en que se consume en producción
            $table->string('unidad_consumo', 50);
            // Ejemplo: Hoja, cm2, cm, ml, Unidad

            // Medidas opcionales para madera, acrílico, cartón, vinil, etc.
            $table->decimal('ancho_cm', 10, 2)->nullable();
            $table->decimal('largo_cm', 10, 2)->nullable();
            $table->decimal('espesor_mm', 10, 2)->nullable();

            // Costos
            $table->decimal('costo_compra', 12, 2)->default(0);
            $table->decimal('costo_unitario_base', 12, 4)->default(0);
            $table->decimal('porcentaje_merma', 5, 2)->default(0);
            $table->decimal('costo_unitario_real', 12, 4)->default(0);

            // Inventario en unidad de consumo
            $table->decimal('stock_actual', 12, 2)->default(0);
            $table->decimal('stock_minimo', 12, 2)->default(0);

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
        Schema::dropIfExists('insumos');
    }
}
