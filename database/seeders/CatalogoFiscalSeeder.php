<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Catalogo;

class CatalogoFiscalSeeder extends Seeder
{
    public function run()
    {
        $this->crearCatalogo(
            'tipo_impuesto',
            'Gravado 15%',
            'Producto o servicio gravado con ISV general del 15%',
            1
        );

        $this->crearCatalogo(
            'tipo_impuesto',
            'Exento',
            'Producto o servicio exento de ISV',
            2
        );

        $this->crearCatalogo(
            'tipo_impuesto',
            'No sujeto',
            'Operación no sujeta a ISV',
            3
        );

        $this->crearCatalogo(
            'tipo_retencion',
            'Sin retención',
            'No aplica retención',
            1
        );

        $this->crearCatalogo(
            'tipo_retencion',
            'Retención ISR',
            'Retención de Impuesto Sobre la Renta',
            2
        );

        $this->crearCatalogo(
            'tipo_retencion',
            'Retención ISV',
            'Retención relacionada con Impuesto Sobre Ventas',
            3
        );
    }

    private function crearCatalogo($tipo, $nombre, $descripcion = null, $orden = 0)
    {
        Catalogo::updateOrCreate(
            [
                'tipo' => $tipo,
                'nombre' => $nombre,
            ],
            [
                'descripcion' => $descripcion,
                'orden' => $orden,
                'activo' => true,
            ]
        );
    }
}
