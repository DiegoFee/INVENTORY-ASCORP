<?php

namespace Database\Factories;

use App\Models\Producto;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * // Autor: Diego Méndez - Fecha: 20/05/2026
 */
class ProductoFactory extends Factory
{
    protected $model = Producto::class;

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function definition(): array
    {
        $precioCosto = fake()->randomFloat(2, 5, 250);

        return [
            'sku' => fake()->unique()->bothify('SKU-####'),
            'nombre' => fake()->words(3, true),
            'stock_actual' => fake()->numberBetween(0, 200),
            'stock_minimo' => fake()->numberBetween(5, 25),
            'precio_costo' => $precioCosto,
            'precio_venta' => $precioCosto + fake()->randomFloat(2, 1, 80),
            'unidad_medida' => fake()->randomElement(['unidad', 'caja', 'kg', 'litro']),
            'categoria' => fake()->randomElement(['General', 'Electronica', 'Hogar', 'Oficina']),
            'activo' => fake()->boolean(90),
        ];
    }
}
