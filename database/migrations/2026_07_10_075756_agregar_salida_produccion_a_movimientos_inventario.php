<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AgregarSalidaProduccionAMovimientosInventario extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE movimientos_inventario MODIFY tipo_movimiento ENUM(
            'Entrada compra',
            'Entrada ajuste',
            'Salida venta',
            'Salida produccion',
            'Salida daño',
            'Salida prueba',
            'Salida ajuste',
            'Devolucion'
        ) NOT NULL");
    }

    public function down()
    {
        // No se revierte automáticamente para evitar errores si ya existen movimientos de producción.
    }
}
