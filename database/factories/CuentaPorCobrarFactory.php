<?php

/** Autor: Arandi Hurtado, Fecha: 21/05/2026, Descripción: Factory para cuentas por cobrar. */

namespace Database\Factories;

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
        $ventaId = Venta::query()->inRandomOrder()->value('id') ?? 1;

        return [
            'id_venta' => $ventaId,
            'monto_original' => 100,
            'saldo' => 100,
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
