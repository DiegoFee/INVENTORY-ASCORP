<?php

/** Autor: Arandi Hurtado, Fecha: 21/05/2026, Descripción: Factory para cuentas por cobrar. */

namespace Database\Factories;

use App\Models\Cliente;
use App\Models\CuentaPorCobrar;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CuentaPorCobrar>
 */
class CuentaPorCobrarFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $total = $this->faker->randomFloat(2, 100, 2000);

        return [
            'venta_id' => Venta::factory(),
            'cliente_id' => Cliente::factory(),
            'total' => $total,
            'saldo' => $total,
            'fecha_vencimiento' => $this->faker->dateTimeBetween('now', '+30 days'),
            'estado' => CuentaPorCobrar::EstadoPendiente,
            'observaciones' => $this->faker->sentence(),
        ];
    }

    /**
     * Funcionamiento: genera datos de prueba para cuentas por cobrar.
     * Tablas: cuenta_por_cobrar, ventas.
     * Flujo: usa una venta existente si hay registros, o el id 1 como placeholder.
     */
}
