<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nombre' => fake()->company(),
            'nit' => fake()->unique()->numerify('########-#'),
            'telefono' => fake()->phoneNumber(),
            'email' => fake()->unique()->companyEmail(),
            'direccion' => fake()->address(),
        ];
    }
}
