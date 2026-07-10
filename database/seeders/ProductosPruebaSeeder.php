<?php

namespace Database\Seeders;

use App\Models\Catalogo;
use App\Models\LoteProducto;
use App\Models\MovimientoProducto;
use App\Models\MovimientoProductoLote;
use App\Models\Producto;
use App\Models\TipoCatalogo;
use Illuminate\Database\Seeder;

class ProductosPruebaSeeder extends Seeder
{
    public function run()
    {
        $this->crearTiposCatalogo();
        $this->crearCatalogosProducto();
        $this->crearProductos();
    }

    private function crearTiposCatalogo()
    {
        $tipos = [
            [
                'codigo' => 'categoria_producto',
                'nombre' => 'Categoría de producto',
                'descripcion' => 'Categorías generales para productos físicos.',
            ],
            [
                'codigo' => 'tipo_producto',
                'nombre' => 'Tipo de producto',
                'descripcion' => 'Define si un producto es reventa, fabricado, mixto o personalizado.',
            ],
            [
                'codigo' => 'unidad_venta',
                'nombre' => 'Unidad de venta',
                'descripcion' => 'Unidad en la que se vende el producto.',
            ],
        ];

        foreach ($tipos as $tipo) {
            TipoCatalogo::updateOrCreate(
                ['codigo' => $tipo['codigo']],
                [
                    'nombre' => $tipo['nombre'],
                    'descripcion' => $tipo['descripcion'],
                    'orden' => 0,
                    'activo' => true,
                ]
            );
        }
    }

    private function crearCatalogosProducto()
    {
        $catalogos = [
            'categoria_producto' => [
                'Tecnología',
                'Accesorio',
                'Llavero',
                'Taza',
                'Placa',
                'Personalizado',
                'Otro',
            ],

            'tipo_producto' => [
                'Reventa',
                'Fabricado',
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

    private function crearProductos()
    {
        $usb = Producto::updateOrCreate(
            ['codigo' => 'USB-32'],
            [
                'codigo_barra' => null,
                'nombre' => 'Memoria USB 32GB',
                'categoria' => 'Tecnología',
                'tipo_producto' => 'Reventa',
                'unidad_venta' => 'Unidad',
                'maneja_inventario' => true,
                'usa_receta' => false,
                'ancho_cm' => null,
                'largo_cm' => null,
                'espesor_mm' => null,
                'stock_minimo' => 2,
                'costo_compra' => 100,
                'costo_unitario' => 100,
                'precio_venta' => 200,
                'descripcion' => 'Memoria USB de 32GB para venta directa.',
                'activo' => true,
            ]
        );

        $mouse = Producto::updateOrCreate(
            ['codigo' => 'MOU-INAL-001'],
            [
                'codigo_barra' => null,
                'nombre' => 'Mouse inalámbrico',
                'categoria' => 'Tecnología',
                'tipo_producto' => 'Reventa',
                'unidad_venta' => 'Unidad',
                'maneja_inventario' => true,
                'usa_receta' => false,
                'ancho_cm' => null,
                'largo_cm' => null,
                'espesor_mm' => null,
                'stock_minimo' => 3,
                'costo_compra' => 150,
                'costo_unitario' => 150,
                'precio_venta' => 250,
                'descripcion' => 'Mouse inalámbrico para reventa.',
                'activo' => true,
            ]
        );

        Producto::updateOrCreate(
            ['codigo' => 'LLA-MDF-001'],
            [
                'codigo_barra' => null,
                'nombre' => 'Llavero MDF personalizado',
                'categoria' => 'Llavero',
                'tipo_producto' => 'Fabricado',
                'unidad_venta' => 'Unidad',
                'maneja_inventario' => true,
                'usa_receta' => true,
                'ancho_cm' => 5,
                'largo_cm' => 4,
                'espesor_mm' => 3,
                'stock_actual' => 0,
                'stock_minimo' => 5,
                'costo_compra' => 0,
                'costo_unitario' => 0,
                'precio_venta' => 80,
                'descripcion' => 'Llavero personalizado fabricado en MDF.',
                'activo' => true,
            ]
        );

        Producto::updateOrCreate(
            ['codigo' => 'TAZ-PER-001'],
            [
                'codigo_barra' => null,
                'nombre' => 'Taza personalizada',
                'categoria' => 'Taza',
                'tipo_producto' => 'Mixto',
                'unidad_venta' => 'Unidad',
                'maneja_inventario' => true,
                'usa_receta' => true,
                'ancho_cm' => null,
                'largo_cm' => null,
                'espesor_mm' => null,
                'stock_actual' => 0,
                'stock_minimo' => 3,
                'costo_compra' => 0,
                'costo_unitario' => 0,
                'precio_venta' => 180,
                'descripcion' => 'Taza personalizada con diseño del cliente.',
                'activo' => true,
            ]
        );

        Producto::updateOrCreate(
            ['codigo' => 'PLA-ACR-001'],
            [
                'codigo_barra' => null,
                'nombre' => 'Placa para mascota en acrílico',
                'categoria' => 'Placa',
                'tipo_producto' => 'Fabricado',
                'unidad_venta' => 'Unidad',
                'maneja_inventario' => true,
                'usa_receta' => true,
                'ancho_cm' => 4,
                'largo_cm' => 4,
                'espesor_mm' => 2,
                'stock_actual' => 0,
                'stock_minimo' => 5,
                'costo_compra' => 0,
                'costo_unitario' => 0,
                'precio_venta' => 100,
                'descripcion' => 'Placa personalizada para mascota en acrílico.',
                'activo' => true,
            ]
        );

        $this->crearEntradaInicialProducto($usb, 10, 100, 'Compra inicial USB');
        $this->crearEntradaInicialProducto($mouse, 5, 150, 'Compra inicial mouse');
    }

    private function crearEntradaInicialProducto($producto, $cantidad, $costoUnitario, $referencia)
    {
        $yaTieneMovimiento = MovimientoProducto::where('producto_id', $producto->id)
            ->where('referencia', $referencia)
            ->exists();

        if ($yaTieneMovimiento) {
            return;
        }

        $total = round($cantidad * $costoUnitario, 2);

        $movimiento = MovimientoProducto::create([
            'producto_id' => $producto->id,
            'tipo_movimiento' => 'Entrada compra',
            'cantidad' => $cantidad,
            'costo_unitario' => $costoUnitario,
            'total' => $total,
            'referencia' => $referencia,
            'observacion' => 'Entrada inicial generada desde seeder.',
        ]);

        $lote = LoteProducto::create([
            'producto_id' => $producto->id,
            'codigo_lote' => 'PROD-' . $producto->id . '-' . now()->format('YmdHis'),
            'fecha_entrada' => now()->format('Y-m-d'),
            'cantidad_inicial' => $cantidad,
            'cantidad_disponible' => $cantidad,
            'costo_unitario' => $costoUnitario,
            'total' => $total,
            'referencia' => $referencia,
            'observacion' => 'Lote inicial generado desde seeder.',
            'activo' => true,
        ]);

        MovimientoProductoLote::create([
            'movimiento_producto_id' => $movimiento->id,
            'lote_producto_id' => $lote->id,
            'cantidad' => $cantidad,
            'costo_unitario' => $costoUnitario,
            'total' => $total,
        ]);

        $producto->update([
            'stock_actual' => $cantidad,
            'costo_unitario' => $costoUnitario,
        ]);
    }
}
