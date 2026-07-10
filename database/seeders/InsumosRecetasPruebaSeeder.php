<?php

namespace Database\Seeders;

use App\Models\Catalogo;
use App\Models\Insumo;
use App\Models\LoteInsumo;
use App\Models\MovimientoInventario;
use App\Models\MovimientoInventarioLote;
use App\Models\Producto;
use App\Models\ProductoInsumo;
use App\Models\TipoCatalogo;
use Illuminate\Database\Seeder;

class InsumosRecetasPruebaSeeder extends Seeder
{
    public function run()
    {
        // Si todavía no existen productos de prueba, ejecuta el seeder de productos.
        if (!Producto::where('codigo', 'LLA-MDF-001')->exists()) {
            $this->call(ProductosPruebaSeeder::class);
        }

        $this->crearTiposCatalogo();
        $this->crearCatalogosInsumos();
        $this->crearInsumos();
        $this->crearRecetasProductos();
    }

    private function crearTiposCatalogo()
    {
        $tipos = [
            [
                'codigo' => 'categoria_insumo',
                'nombre' => 'Categoría de insumo',
                'descripcion' => 'Categorías generales para materiales e insumos.',
            ],
            [
                'codigo' => 'unidad_compra',
                'nombre' => 'Unidad de compra',
                'descripcion' => 'Unidad en la que se compra el insumo.',
            ],
            [
                'codigo' => 'unidad_consumo',
                'nombre' => 'Unidad de consumo',
                'descripcion' => 'Unidad en la que se consume el insumo.',
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

    private function crearCatalogosInsumos()
    {
        $catalogos = [
            'categoria_insumo' => [
                'Papel',
                'Tinta',
                'Madera',
                'Acrilico',
                'Herraje',
                'Empaque',
                'Ceramica',
                'Otro',
            ],

            'unidad_compra' => [
                'Lamina',
                'Paquete',
                'Caja',
                'Botella',
                'Unidad',
                'Otro',
            ],

            'unidad_consumo' => [
                'cm2',
                'Hoja',
                'ml',
                'Unidad',
                'Otro',
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

    private function crearInsumos()
    {
        $this->crearInsumoConEntrada([
            'codigo' => 'MDF-3MM',
            'nombre' => 'MDF 3mm',
            'categoria' => 'Madera',
            'unidad_compra' => 'Lamina',
            'cantidad_por_compra' => 3600,
            'unidad_consumo' => 'cm2',
            'ancho_cm' => 60,
            'largo_cm' => 60,
            'espesor_mm' => 3,
            'costo_compra' => 180,
            'porcentaje_merma' => 0,
            'stock_minimo' => 500,
            'descripcion' => 'Lámina MDF 3mm de 60x60 cm.',
        ], 3600, 'Compra inicial MDF 3mm');

        $this->crearInsumoConEntrada([
            'codigo' => 'KIT-LLA',
            'nombre' => 'Kit llavero',
            'categoria' => 'Herraje',
            'unidad_compra' => 'Paquete',
            'cantidad_por_compra' => 100,
            'unidad_consumo' => 'Unidad',
            'ancho_cm' => null,
            'largo_cm' => null,
            'espesor_mm' => null,
            'costo_compra' => 150,
            'porcentaje_merma' => 0,
            'stock_minimo' => 20,
            'descripcion' => 'Kit metálico para llavero.',
        ], 100, 'Compra inicial kit llavero');

        $this->crearInsumoConEntrada([
            'codigo' => 'BOLSA-PEQ',
            'nombre' => 'Bolsa pequeña',
            'categoria' => 'Empaque',
            'unidad_compra' => 'Paquete',
            'cantidad_por_compra' => 100,
            'unidad_consumo' => 'Unidad',
            'ancho_cm' => null,
            'largo_cm' => null,
            'espesor_mm' => null,
            'costo_compra' => 50,
            'porcentaje_merma' => 0,
            'stock_minimo' => 20,
            'descripcion' => 'Bolsa pequeña para empaque de productos.',
        ], 100, 'Compra inicial bolsas pequeñas');

        $this->crearInsumoConEntrada([
            'codigo' => 'TAZA-BLANCA',
            'nombre' => 'Taza blanca',
            'categoria' => 'Ceramica',
            'unidad_compra' => 'Caja',
            'cantidad_por_compra' => 12,
            'unidad_consumo' => 'Unidad',
            'ancho_cm' => null,
            'largo_cm' => null,
            'espesor_mm' => null,
            'costo_compra' => 720,
            'porcentaje_merma' => 0,
            'stock_minimo' => 3,
            'descripcion' => 'Taza blanca para personalización.',
        ], 12, 'Compra inicial tazas blancas');

        $this->crearInsumoConEntrada([
            'codigo' => 'PAP-SUB',
            'nombre' => 'Papel sublimación',
            'categoria' => 'Papel',
            'unidad_compra' => 'Paquete',
            'cantidad_por_compra' => 100,
            'unidad_consumo' => 'Hoja',
            'ancho_cm' => null,
            'largo_cm' => null,
            'espesor_mm' => null,
            'costo_compra' => 300,
            'porcentaje_merma' => 0,
            'stock_minimo' => 20,
            'descripcion' => 'Papel para sublimación.',
        ], 100, 'Compra inicial papel sublimación');

        $this->crearInsumoConEntrada([
            'codigo' => 'TINT-SUB',
            'nombre' => 'Tinta sublimación',
            'categoria' => 'Tinta',
            'unidad_compra' => 'Botella',
            'cantidad_por_compra' => 1000,
            'unidad_consumo' => 'ml',
            'ancho_cm' => null,
            'largo_cm' => null,
            'espesor_mm' => null,
            'costo_compra' => 900,
            'porcentaje_merma' => 0,
            'stock_minimo' => 100,
            'descripcion' => 'Tinta para sublimación medida en ml.',
        ], 1000, 'Compra inicial tinta sublimación');

        $this->crearInsumoConEntrada([
            'codigo' => 'CAJA-TAZA',
            'nombre' => 'Caja para taza',
            'categoria' => 'Empaque',
            'unidad_compra' => 'Paquete',
            'cantidad_por_compra' => 50,
            'unidad_consumo' => 'Unidad',
            'ancho_cm' => null,
            'largo_cm' => null,
            'espesor_mm' => null,
            'costo_compra' => 400,
            'porcentaje_merma' => 0,
            'stock_minimo' => 10,
            'descripcion' => 'Caja individual para taza.',
        ], 50, 'Compra inicial cajas para taza');

        $this->crearInsumoConEntrada([
            'codigo' => 'ACR-2MM',
            'nombre' => 'Acrilico 2mm',
            'categoria' => 'Acrilico',
            'unidad_compra' => 'Lamina',
            'cantidad_por_compra' => 2400,
            'unidad_consumo' => 'cm2',
            'ancho_cm' => 60,
            'largo_cm' => 40,
            'espesor_mm' => 2,
            'costo_compra' => 300,
            'porcentaje_merma' => 0,
            'stock_minimo' => 300,
            'descripcion' => 'Lámina acrílica 2mm de 60x40 cm.',
        ], 2400, 'Compra inicial acrilico 2mm');

        $this->crearInsumoConEntrada([
            'codigo' => 'ARO-MET',
            'nombre' => 'Aro metálico',
            'categoria' => 'Herraje',
            'unidad_compra' => 'Paquete',
            'cantidad_por_compra' => 100,
            'unidad_consumo' => 'Unidad',
            'ancho_cm' => null,
            'largo_cm' => null,
            'espesor_mm' => null,
            'costo_compra' => 100,
            'porcentaje_merma' => 0,
            'stock_minimo' => 20,
            'descripcion' => 'Aro metálico para placas o llaveros.',
        ], 100, 'Compra inicial aros metálicos');
    }

    private function crearInsumoConEntrada($data, $stockInicial, $referencia)
    {
        $costoUnitarioBase = $data['cantidad_por_compra'] > 0
            ? $data['costo_compra'] / $data['cantidad_por_compra']
            : 0;

        $merma = (float) $data['porcentaje_merma'];

        if ($merma > 0 && $merma < 100) {
            $costoUnitarioReal = $costoUnitarioBase / (1 - ($merma / 100));
        } else {
            $costoUnitarioReal = $costoUnitarioBase;
        }

        $insumo = Insumo::firstOrNew([
            'codigo' => $data['codigo'],
        ]);

        $esNuevo = !$insumo->exists;

        $insumo->fill([
            'nombre' => $data['nombre'],
            'categoria' => $data['categoria'],

            'unidad_compra' => $data['unidad_compra'],
            'cantidad_por_compra' => $data['cantidad_por_compra'],
            'unidad_consumo' => $data['unidad_consumo'],

            'ancho_cm' => $data['ancho_cm'],
            'largo_cm' => $data['largo_cm'],
            'espesor_mm' => $data['espesor_mm'],

            'costo_compra' => $data['costo_compra'],
            'costo_unitario_base' => round($costoUnitarioBase, 4),
            'porcentaje_merma' => $data['porcentaje_merma'],
            'costo_unitario_real' => round($costoUnitarioReal, 4),

            'stock_minimo' => $data['stock_minimo'],
            'descripcion' => $data['descripcion'],
            'activo' => true,
        ]);

        if ($esNuevo) {
            $insumo->stock_actual = 0;
        }

        $insumo->save();

        $this->crearEntradaInicialInsumo(
            $insumo,
            $stockInicial,
            $costoUnitarioBase,
            $referencia
        );
    }

    private function crearEntradaInicialInsumo($insumo, $cantidad, $costoUnitario, $referencia)
    {
        $yaTieneMovimiento = MovimientoInventario::where('insumo_id', $insumo->id)
            ->where('referencia', $referencia)
            ->exists();

        if ($yaTieneMovimiento) {
            return;
        }

        $total = round($cantidad * $costoUnitario, 2);

        $movimiento = MovimientoInventario::create([
            'insumo_id' => $insumo->id,
            'tipo_movimiento' => 'Entrada compra',
            'cantidad' => $cantidad,
            'costo_unitario' => round($costoUnitario, 4),
            'total' => $total,
            'referencia' => $referencia,
            'observacion' => 'Entrada inicial generada desde seeder.',
        ]);

        $lote = LoteInsumo::create([
            'insumo_id' => $insumo->id,
            'codigo_lote' => 'INS-' . $insumo->id . '-' . now()->format('YmdHis'),
            'fecha_entrada' => now()->format('Y-m-d'),
            'cantidad_inicial' => $cantidad,
            'cantidad_disponible' => $cantidad,
            'costo_unitario' => round($costoUnitario, 4),
            'total' => $total,
            'referencia' => $referencia,
            'observacion' => 'Lote inicial generado desde seeder.',
            'activo' => true,
        ]);

        MovimientoInventarioLote::create([
            'movimiento_inventario_id' => $movimiento->id,
            'lote_insumo_id' => $lote->id,
            'cantidad' => $cantidad,
            'costo_unitario' => round($costoUnitario, 4),
            'total' => $total,
        ]);

        $insumo->update([
            'stock_actual' => round($insumo->stock_actual + $cantidad, 2),
            'costo_unitario_base' => round($costoUnitario, 4),
            'costo_unitario_real' => round($costoUnitario, 4),
        ]);
    }

    private function crearRecetasProductos()
    {
        $this->agregarInsumoAProducto('LLA-MDF-001', 'MDF-3MM', 20);
        $this->agregarInsumoAProducto('LLA-MDF-001', 'KIT-LLA', 1);
        $this->agregarInsumoAProducto('LLA-MDF-001', 'BOLSA-PEQ', 1);

        $this->agregarInsumoAProducto('TAZ-PER-001', 'TAZA-BLANCA', 1);
        $this->agregarInsumoAProducto('TAZ-PER-001', 'PAP-SUB', 1);
        $this->agregarInsumoAProducto('TAZ-PER-001', 'TINT-SUB', 5);
        $this->agregarInsumoAProducto('TAZ-PER-001', 'CAJA-TAZA', 1);

        $this->agregarInsumoAProducto('PLA-ACR-001', 'ACR-2MM', 16);
        $this->agregarInsumoAProducto('PLA-ACR-001', 'ARO-MET', 1);
        $this->agregarInsumoAProducto('PLA-ACR-001', 'BOLSA-PEQ', 1);

        $this->actualizarCostoProducto('LLA-MDF-001');
        $this->actualizarCostoProducto('TAZ-PER-001');
        $this->actualizarCostoProducto('PLA-ACR-001');
    }

    private function agregarInsumoAProducto($codigoProducto, $codigoInsumo, $cantidadPorUnidad)
    {
        $producto = Producto::where('codigo', $codigoProducto)->first();
        $insumo = Insumo::where('codigo', $codigoInsumo)->first();

        if (!$producto || !$insumo) {
            return;
        }

        ProductoInsumo::updateOrCreate(
            [
                'producto_id' => $producto->id,
                'insumo_id' => $insumo->id,
            ],
            [
                'cantidad_por_unidad' => $cantidadPorUnidad,
            ]
        );
    }

    private function actualizarCostoProducto($codigoProducto)
    {
        $producto = Producto::where('codigo', $codigoProducto)->first();

        if (!$producto) {
            return;
        }

        $recetas = ProductoInsumo::with('insumo')
            ->where('producto_id', $producto->id)
            ->get();

        $costoTotal = $recetas->sum(function ($receta) {
            return $receta->cantidad_por_unidad * $receta->insumo->costo_unitario_real;
        });

        // Si ya tiene stock producido, no alteramos el costo PEPS actual.
        if ($producto->stock_actual <= 0) {
            $producto->update([
                'costo_unitario' => round($costoTotal, 2),
            ]);
        }
    }
}
