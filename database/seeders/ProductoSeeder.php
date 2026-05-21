<?php

namespace Database\Seeders;

use App\Models\Producto;
use Illuminate\Database\Seeder;

/**
 * // Autor: Diego Méndez - Fecha: 21/05/2026
 */
class ProductoSeeder extends Seeder
{
    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function run(): void
    {
        Producto::factory()
            ->count(12)
            ->create();

        Producto::factory()
            ->count(3)
            ->state([
                'stock_actual' => 2,
                'stock_minimo' => 10,
            ])
            ->create();
    }
}
