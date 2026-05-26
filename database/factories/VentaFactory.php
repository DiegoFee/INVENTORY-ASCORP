<?php

namespace Database\Factories;

use App\Models\Caja;
use App\Models\Cliente;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Venta>
 */
class VentaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'caja_id' => Caja::factory(),
            'user_id' => User::factory(),
            'cliente_id' => Cliente::factory(),
            'total' => $this->faker->randomFloat(2, 50, 2000),
            'descuento' => $this->faker->randomFloat(2, 0, 150),
            'estado' => Venta::EstadoCerrada,
            'observaciones' => null,
            'opened_at' => now()->subHours(2),
            'closed_at' => now()->subHour(),
            'tipo_pago' => $this->faker->randomElement(['contado', 'credito']),
        ];
    }
}
