<?php

namespace Database\Seeders;

use App\Models\Catalogo;
use App\Models\TipoCatalogo;
use Illuminate\Database\Seeder;

class CatalogoGastosSeeder extends Seeder
{
    public function run()
    {
        TipoCatalogo::updateOrCreate(
            ['codigo' => 'categoria_gasto'],
            [
                'nombre' => 'Categoría de gasto',
                'descripcion' => 'Categorías utilizadas para clasificar los gastos del negocio.',
                'orden' => 0,
                'activo' => true,
            ]
        );

        $categorias = [
            'Alquiler',
            'Energía eléctrica',
            'Agua',
            'Internet',
            'Telefonía',
            'Transporte',
            'Combustible',
            'Mantenimiento',
            'Repuestos',
            'Publicidad',
            'Papelería',
            'Compras menores',
            'Comisiones bancarias',
            'Sueldos',
            'Honorarios',
            'Impuestos',
            'Limpieza',
            'Seguridad',
            'Otros gastos',
        ];

        foreach ($categorias as $categoria) {
            Catalogo::updateOrCreate(
                [
                    'tipo' => 'categoria_gasto',
                    'nombre' => $categoria,
                ],
                [
                    'descripcion' => null,
                    'orden' => 0,
                    'activo' => true,
                ]
            );
        }
    }
}
