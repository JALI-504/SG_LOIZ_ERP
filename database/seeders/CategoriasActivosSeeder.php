<?php

namespace Database\Seeders;

use App\Models\CategoriaActivo;
use Illuminate\Database\Seeder;

class CategoriasActivosSeeder extends Seeder
{
    public function run()
    {
        $categorias = [
            [
                'codigo' => 'CAT-ACT-00001',
                'prefijo_codigo' => 'TER',
                'nombre' => 'Terrenos',
                'descripcion' => 'Terrenos. Según método legal, no se deprecian.',
                'depreciable' => false,
                'vida_util_meses' => 1,
                'porcentaje_depreciacion_anual' => 0,
                'metodo_depreciacion' => 'No aplica',
                'requiere_numero_serie' => false,
                'requiere_marca_modelo' => false,
                'requiere_responsable' => false,
                'activo' => true,
            ],
            [
                'codigo' => 'CAT-ACT-00002',
                'prefijo_codigo' => 'EDF',
                'nombre' => 'Edificaciones',
                'descripcion' => 'Edificaciones con vida útil legal de 20 años.',
                'depreciable' => true,
                'vida_util_meses' => 240,
                'porcentaje_depreciacion_anual' => 5,
                'metodo_depreciacion' => 'Linea recta',
                'requiere_numero_serie' => false,
                'requiere_marca_modelo' => false,
                'requiere_responsable' => false,
                'activo' => true,
            ],
            [
                'codigo' => 'CAT-ACT-00003',
                'prefijo_codigo' => 'MAQ',
                'nombre' => 'Maquinaria y equipo',
                'descripcion' => 'Maquinaria y equipo con vida útil legal de 10 años.',
                'depreciable' => true,
                'vida_util_meses' => 120,
                'porcentaje_depreciacion_anual' => 10,
                'metodo_depreciacion' => 'Linea recta',
                'requiere_numero_serie' => true,
                'requiere_marca_modelo' => true,
                'requiere_responsable' => true,
                'activo' => true,
            ],
            [
                'codigo' => 'CAT-ACT-00004',
                'prefijo_codigo' => 'MUE',
                'nombre' => 'Muebles y enseres',
                'descripcion' => 'Muebles y enseres con vida útil legal de 10 años.',
                'depreciable' => true,
                'vida_util_meses' => 120,
                'porcentaje_depreciacion_anual' => 10,
                'metodo_depreciacion' => 'Linea recta',
                'requiere_numero_serie' => false,
                'requiere_marca_modelo' => false,
                'requiere_responsable' => true,
                'activo' => true,
            ],
            [
                'codigo' => 'CAT-ACT-00005',
                'prefijo_codigo' => 'EOF',
                'nombre' => 'Equipo de oficina',
                'descripcion' => 'Equipo de oficina con vida útil legal de 10 años.',
                'depreciable' => true,
                'vida_util_meses' => 120,
                'porcentaje_depreciacion_anual' => 10,
                'metodo_depreciacion' => 'Linea recta',
                'requiere_numero_serie' => true,
                'requiere_marca_modelo' => true,
                'requiere_responsable' => true,
                'activo' => true,
            ],
            [
                'codigo' => 'CAT-ACT-00006',
                'prefijo_codigo' => 'VEH',
                'nombre' => 'Vehículos',
                'descripcion' => 'Vehículos con vida útil legal de 5 años.',
                'depreciable' => true,
                'vida_util_meses' => 60,
                'porcentaje_depreciacion_anual' => 20,
                'metodo_depreciacion' => 'Linea recta',
                'requiere_numero_serie' => true,
                'requiere_marca_modelo' => true,
                'requiere_responsable' => true,
                'activo' => true,
            ],
            [
                'codigo' => 'CAT-ACT-00007',
                'prefijo_codigo' => 'COM',
                'nombre' => 'Equipo de computación',
                'descripcion' => 'Equipo de computación con vida útil legal de 3 años.',
                'depreciable' => true,
                'vida_util_meses' => 36,
                'porcentaje_depreciacion_anual' => 33,
                'metodo_depreciacion' => 'Linea recta',
                'requiere_numero_serie' => true,
                'requiere_marca_modelo' => true,
                'requiere_responsable' => true,
                'activo' => true,
            ],
        ];

        foreach ($categorias as $categoria) {
            CategoriaActivo::updateOrCreate(
                [
                    'nombre' => $categoria['nombre'],
                ],
                $categoria
            );
        }
    }
}
