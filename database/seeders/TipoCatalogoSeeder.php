<?php

namespace Database\Seeders;

use App\Models\TipoCatalogo;
use Illuminate\Database\Seeder;

class TipoCatalogoSeeder extends Seeder
{
    public function run()
    {
        $tipos = [
            [
                'codigo' => 'categoria_insumo',
                'nombre' => 'Categoría de insumo',
                'orden' => 1,
            ],
            [
                'codigo' => 'unidad_compra',
                'nombre' => 'Unidad de compra',
                'orden' => 2,
            ],
            [
                'codigo' => 'unidad_consumo',
                'nombre' => 'Unidad de consumo',
                'orden' => 3,
            ],
            [
                'codigo' => 'tipo_servicio',
                'nombre' => 'Tipo de servicio',
                'orden' => 4,
            ],
            [
                'codigo' => 'tamano_papel',
                'nombre' => 'Tamaño de papel',
                'orden' => 5,
            ],
            [
                'codigo' => 'color_servicio',
                'nombre' => 'Color de servicio',
                'orden' => 6,
            ],
            [
                'codigo' => 'caras_servicio',
                'nombre' => 'Caras de servicio',
                'orden' => 7,
            ],
            [
                'codigo' => 'unidad_cobro',
                'nombre' => 'Unidad de cobro',
                'orden' => 8,
            ],
            [
                'codigo' => 'categoria_producto',
                'nombre' => 'Categoría de producto',
                'orden' => 9,
            ],
            [
                'codigo' => 'tipo_producto',
                'nombre' => 'Tipo de producto',
                'orden' => 10,
            ],
            [
                'codigo' => 'unidad_venta',
                'nombre' => 'Unidad de venta',
                'orden' => 11,
            ],
        ];

        foreach ($tipos as $tipo) {
            TipoCatalogo::updateOrCreate(
                [
                    'codigo' => $tipo['codigo'],
                ],
                [
                    'nombre' => $tipo['nombre'],
                    'descripcion' => $tipo['descripcion'] ?? null,
                    'orden' => $tipo['orden'],
                    'activo' => true,
                ]
            );
        }
    }
}
