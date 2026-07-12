<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Catalogo;
use App\Models\Servicio;

class CatalogoServiciosLaserSeeder extends Seeder
{
    public function run()
    {
        /*
        |--------------------------------------------------------------------------
        | Catálogos para servicios láser
        |--------------------------------------------------------------------------
        */

        $this->crearCatalogo('tipo_servicio', 'Láser', 'Servicios realizados con máquina láser', 100);
        $this->crearCatalogo('tipo_servicio', 'Grabado láser', 'Grabado sobre materiales', 101);
        $this->crearCatalogo('tipo_servicio', 'Corte láser', 'Corte de materiales con láser', 102);
        $this->crearCatalogo('tipo_servicio', 'Diseño láser', 'Diseño o preparación de archivos para láser', 103);

        $this->crearCatalogo('tamano_papel', 'Personalizado', 'Tamaño personalizado o material del cliente', 100);
        $this->crearCatalogo('tamano_papel', 'No aplica', 'No aplica para este servicio', 101);
        $this->crearCatalogo('tamano_papel', 'Pequeño', 'Trabajo láser pequeño', 102);
        $this->crearCatalogo('tamano_papel', 'Mediano', 'Trabajo láser mediano', 103);
        $this->crearCatalogo('tamano_papel', 'Grande', 'Trabajo láser grande', 104);

        $this->crearCatalogo('color_servicio', 'No aplica', 'No aplica color para este servicio', 100);
        $this->crearCatalogo('color_servicio', 'Sin color', 'Servicio sin color', 101);

        $this->crearCatalogo('caras_servicio', 'No aplica', 'No aplica caras para este servicio', 100);

        $this->crearCatalogo('unidad_cobro', 'Pieza', 'Cobro por pieza trabajada', 100);
        $this->crearCatalogo('unidad_cobro', 'Minuto', 'Cobro por tiempo de máquina', 101);
        $this->crearCatalogo('unidad_cobro', 'Servicio', 'Cobro por servicio realizado', 102);
        $this->crearCatalogo('unidad_cobro', 'Centímetro cuadrado', 'Cobro por área trabajada', 103);

        /*
        |--------------------------------------------------------------------------
        | Servicios láser cuando el cliente trae el material
        | No llevan receta de insumos
        |--------------------------------------------------------------------------
        */

        $this->crearServicioLaser(
            'Grabado láser pequeño en material del cliente',
            'Grabado láser',
            'Pequeño',
            'No aplica',
            'No aplica',
            'Pieza',
            0,
            50,
            'Grabado láser pequeño cuando el cliente proporciona el material. No consume inventario.'
        );

        $this->crearServicioLaser(
            'Grabado láser mediano en material del cliente',
            'Grabado láser',
            'Mediano',
            'No aplica',
            'No aplica',
            'Pieza',
            0,
            80,
            'Grabado láser mediano cuando el cliente proporciona el material. No consume inventario.'
        );

        $this->crearServicioLaser(
            'Grabado láser grande en material del cliente',
            'Grabado láser',
            'Grande',
            'No aplica',
            'No aplica',
            'Pieza',
            0,
            120,
            'Grabado láser grande cuando el cliente proporciona el material. No consume inventario.'
        );

        $this->crearServicioLaser(
            'Corte láser en material del cliente',
            'Corte láser',
            'Personalizado',
            'No aplica',
            'No aplica',
            'Pieza',
            0,
            75,
            'Corte láser cuando el cliente proporciona el material. No consume inventario.'
        );

        $this->crearServicioLaser(
            'Servicio láser por minuto',
            'Láser',
            'No aplica',
            'No aplica',
            'No aplica',
            'Minuto',
            0,
            15,
            'Cobro por minuto de uso de máquina láser.'
        );

        $this->crearServicioLaser(
            'Prueba de grabado láser',
            'Grabado láser',
            'No aplica',
            'No aplica',
            'No aplica',
            'Servicio',
            0,
            25,
            'Prueba de grabado láser antes del trabajo final.'
        );

        $this->crearServicioLaser(
            'Diseño básico para láser',
            'Diseño láser',
            'No aplica',
            'No aplica',
            'No aplica',
            'Servicio',
            0,
            50,
            'Diseño básico o preparación sencilla de archivo para láser.'
        );

        $this->crearServicioLaser(
            'Vectorización para láser',
            'Diseño láser',
            'No aplica',
            'No aplica',
            'No aplica',
            'Servicio',
            0,
            100,
            'Vectorización o limpieza de logo/imagen para corte o grabado láser.'
        );

        /*
        |--------------------------------------------------------------------------
        | Servicios láser con material incluido
        | Estos sí deben llevar receta de insumos
        |--------------------------------------------------------------------------
        */

        $this->crearServicioLaser(
            'Grabado láser pequeño con material incluido',
            'Grabado láser',
            'Pequeño',
            'No aplica',
            'No aplica',
            'Pieza',
            0,
            80,
            'Grabado láser pequeño donde LOIZ proporciona el material. Debe tener receta de insumos.'
        );

        $this->crearServicioLaser(
            'Grabado láser mediano con material incluido',
            'Grabado láser',
            'Mediano',
            'No aplica',
            'No aplica',
            'Pieza',
            0,
            120,
            'Grabado láser mediano donde LOIZ proporciona el material. Debe tener receta de insumos.'
        );

        $this->crearServicioLaser(
            'Grabado láser grande con material incluido',
            'Grabado láser',
            'Grande',
            'No aplica',
            'No aplica',
            'Pieza',
            0,
            180,
            'Grabado láser grande donde LOIZ proporciona el material. Debe tener receta de insumos.'
        );

        $this->crearServicioLaser(
            'Corte láser con material incluido',
            'Corte láser',
            'Personalizado',
            'No aplica',
            'No aplica',
            'Pieza',
            0,
            100,
            'Corte láser donde LOIZ proporciona el material. Debe tener receta de insumos.'
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

    private function crearServicioLaser(
        $nombre,
        $tipoServicio,
        $tamanoPapel,
        $color,
        $caras,
        $unidadCobro,
        $costoUnitario,
        $precioUnitario,
        $descripcion
    ) {
        $servicio = Servicio::where('nombre', $nombre)->first();

        if (!$servicio) {
            Servicio::create([
                'codigo' => null,
                'nombre' => $nombre,
                'tipo_servicio' => $tipoServicio,
                'tamano_papel' => $tamanoPapel,
                'color' => $color,
                'caras' => $caras,
                'unidad_cobro' => $unidadCobro,
                'costo_unitario' => $costoUnitario,
                'precio_unitario' => $precioUnitario,
                'descripcion' => $descripcion,
                'activo' => true,
            ]);

            return;
        }

        $servicio->update([
            'tipo_servicio' => $tipoServicio,
            'tamano_papel' => $tamanoPapel,
            'color' => $color,
            'caras' => $caras,
            'unidad_cobro' => $unidadCobro,
            'costo_unitario' => $costoUnitario,
            'precio_unitario' => $precioUnitario,
            'descripcion' => $descripcion,
            'activo' => true,
        ]);
    }
}
