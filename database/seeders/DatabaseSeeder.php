<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * // Autor: Diego Méndez - Fecha: 21/05/2026
 */
class DatabaseSeeder extends Seeder
{
    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
            ProductoSeeder::class,
        ]);
    }
}
