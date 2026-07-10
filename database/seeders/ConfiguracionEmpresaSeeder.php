<?php

namespace Database\Seeders;

use App\Models\ConfiguracionEmpresa;
use Illuminate\Database\Seeder;

class ConfiguracionEmpresaSeeder extends Seeder
{
    public function run()
    {
        ConfiguracionEmpresa::updateOrCreate(
            [
                'id' => 1,
            ],
            [
                'nombre_comercial' => 'Servicios Gráficos LOIZ',
                'nombre_legal' => null,
                'rtn' => null,

                'telefono' => null,
                'whatsapp' => null,
                'correo' => null,
                'direccion' => null,

                'descripcion_negocio' => 'Impresiones, productos personalizados y servicios gráficos',
                'logo' => null,

                'usa_facturacion_fiscal' => false,

                'cai' => null,
                'rango_desde' => null,
                'rango_hasta' => null,
                'fecha_limite_emision' => null,

                'prefijo_recibo' => 'REC',
                'numero_actual_recibo' => 0,

                'mensaje_recibo' => 'Gracias por su compra.',
                'activo' => true,
            ]
        );
    }
}
