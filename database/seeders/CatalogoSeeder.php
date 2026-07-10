<?php

namespace Database\Seeders;

use App\Models\Catalogo;
use Illuminate\Database\Seeder;

class CatalogoSeeder extends Seeder
{
    public function run()
    {
        $catalogos = [
            'categoria_insumo' => [
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
                'Otro',
            ],

            'unidad_compra' => [
                'Resma',
                'Pliego',
                'Lamina',
                'Paquete',
                'Caja',
                'Botella',
                'Rollo',
                'Unidad',
                'Otro',
            ],

            'unidad_consumo' => [
                'Hoja',
                'cm2',
                'cm',
                'ml',
                'Unidad',
                'Gramo',
                'Metro',
                'Otro',
            ],

            'tipo_servicio' => [
                'Impresion',
                'Fotocopia',
                'Escaneo',
                'Plastificado',
                'Laminado',
                'Diseno',
                'Otro',
            ],

            'tamano_papel' => [
                'Carta',
                'Oficio',
                'Legal',
                'A4',
                'Tabloide',
                'Personalizado',
                'No aplica',
            ],

            'color_servicio' => [
                'Blanco y negro',
                'Color',
                'No aplica',
            ],

            'caras_servicio' => [
                'Una cara',
                'Doble cara',
                'No aplica',
            ],

            'unidad_cobro' => [
                'Pagina',
                'Hoja',
                'Unidad',
                'Minuto',
                'Hora',
                'Trabajo',
            ],

            'categoria_producto' => [
                'Llavero',
                'Placa',
                'Dije',
                'Reconocimiento',
                'Adorno',
                'Souvenir',
                'Accesorio',
                'Papeleria',
                'Tecnologia',
                'Taza',
                'Camisa',
                'Personalizado',
                'Otro',
            ],

            'tipo_producto' => [
                'Fabricado',
                'Reventa',
                'Mixto',
                'Personalizado',
                'Otro',
            ],

            'unidad_venta' => [
                'Unidad',
                'Paquete',
                'Docena',
                'Trabajo',
            ],
        ];

        foreach ($catalogos as $tipo => $opciones) {
            foreach ($opciones as $index => $nombre) {
                Catalogo::updateOrCreate(
                    [
                        'tipo' => $tipo,
                        'nombre' => $nombre,
                    ],
                    [
                        'descripcion' => null,
                        'orden' => $index + 1,
                        'activo' => true,
                    ]
                );
            }
        }
    }
}
