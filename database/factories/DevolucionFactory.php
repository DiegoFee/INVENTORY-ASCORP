<?php

namespace Database\Factories;

use App\Models\Devolucion;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Devolucion>
 */
class DevolucionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'venta_id' => Venta::factory(),
            'user_id' => User::factory(),
            'monto' => fake()->randomFloat(2, 10, 1000),
            'motivo' => fake()->sentence(),
            'estado' => Devolucion::EstadoPendiente,
        ];
    }
}
