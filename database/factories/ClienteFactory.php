<?php

namespace Database\Factories;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Factories\Factory;

// Autor: Celvin
// Descripcion: Factory para clientes.
class ClienteFactory extends Factory
{
    protected $model = Cliente::class;

    // Autor: Celvin
    // Descripcion: Define el estado por defecto del cliente.
    public function definition(): array
    {
        return [
            'nit' => fake()->randomElement(['CF', fake()->numerify('########')]),
            'nombre' => fake()->name(),
            'direccion' => fake()->streetAddress(),
            'telefono' => fake()->numerify('########'),
        ];
    }
}
