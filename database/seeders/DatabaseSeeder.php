<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            HondurasUbicacionSeeder::class,
            TipoCatalogoSeeder::class,
            CatalogoSeeder::class,
            CatalogoVentasSeeder::class,
            ProductosPruebaSeeder::class,
            InsumosRecetasPruebaSeeder::class,
            ConfiguracionEmpresaSeeder::class,
        ]);
    }
}
