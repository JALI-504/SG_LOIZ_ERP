<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CambiarCamposCatalogoProductosAString extends Migration
{
    public function up()
    {
        DB::statement("ALTER TABLE productos MODIFY categoria VARCHAR(50) NOT NULL DEFAULT 'Personalizado'");
        DB::statement("ALTER TABLE productos MODIFY tipo_producto VARCHAR(50) NOT NULL DEFAULT 'Fabricado'");
        DB::statement("ALTER TABLE productos MODIFY unidad_venta VARCHAR(50) NOT NULL DEFAULT 'Unidad'");
    }

    public function down()
    {
        // No se revierte a ENUM porque ahora estos campos dependen de catálogos dinámicos.
    }
}
