<?php

namespace Database\Factories;

use App\Models\Caja;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Caja>
 */
class CajaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id_open' => User::factory(),
            'opened_at' => now()->subHours(2),
            'saldo_apertura' => $this->faker->randomFloat(2, 100, 1500),
            'user_id_close' => null,
            'closed_at' => null,
            'saldo_cierre' => null,
            'status' => 'open',
        ];
    }
}
