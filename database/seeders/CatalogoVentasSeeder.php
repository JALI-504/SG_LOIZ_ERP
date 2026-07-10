<?php

namespace Database\Seeders;

use App\Models\Catalogo;
use App\Models\TipoCatalogo;
use Illuminate\Database\Seeder;

class CatalogoVentasSeeder extends Seeder
{
    public function run()
    {
        $this->crearTiposCatalogo();
        $this->crearCatalogos();
    }

    private function crearTiposCatalogo()
    {
        $tipos = [
            [
                'codigo' => 'metodo_pago',
                'nombre' => 'Método de pago',
                'descripcion' => 'Métodos disponibles para pagar una venta.',
            ],
            [
                'codigo' => 'estado_venta',
                'nombre' => 'Estado de venta',
                'descripcion' => 'Estados posibles de una venta.',
            ],
        ];

        foreach ($tipos as $tipo) {
            TipoCatalogo::updateOrCreate(
                [
                    'codigo' => $tipo['codigo'],
                ],
                [
                    'nombre' => $tipo['nombre'],
                    'descripcion' => $tipo['descripcion'],
                    'orden' => 0,
                    'activo' => true,
                ]
            );
        }
    }

    private function crearCatalogos()
    {
        $catalogos = [
            'metodo_pago' => [
                'Efectivo',
                'Transferencia',
                'Tarjeta',
                'Mixto',
                'Crédito',
            ],

            'estado_venta' => [
                'Pagada',
                'Pendiente',
                'Anulada',
            ],
        ];

        foreach ($catalogos as $tipo => $opciones) {
            foreach ($opciones as $nombre) {
                Catalogo::updateOrCreate(
                    [
                        'tipo' => $tipo,
                        'nombre' => $nombre,
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
}
