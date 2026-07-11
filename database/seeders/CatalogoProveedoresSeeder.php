<?php

namespace Database\Seeders;

use App\Models\Catalogo;
use App\Models\TipoCatalogo;
use Illuminate\Database\Seeder;

class CatalogoProveedoresSeeder extends Seeder
{
    public function run()
    {
        TipoCatalogo::updateOrCreate(
            ['codigo' => 'tipo_proveedor'],
            [
                'nombre' => 'Tipo de proveedor',
                'descripcion' => 'Clasificación de proveedores del negocio.',
                'orden' => 0,
                'activo' => true,
            ]
        );

        $tipos = [
            'General',
            'Insumos',
            'Papelería',
            'Impresión',
            'Sublimación',
            'Láser',
            'Tecnología',
            'Mantenimiento',
            'Servicios',
            'Transporte',
            'Publicidad',
            'Otros',
        ];

        foreach ($tipos as $tipo) {
            Catalogo::updateOrCreate(
                [
                    'tipo' => 'tipo_proveedor',
                    'nombre' => $tipo,
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
