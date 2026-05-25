<?php

/** Autor: Arandi Hurtado, Fecha: 21/05/2026, Descripción: Seeder de cuentas por cobrar para ventas existentes. */

namespace Database\Seeders;

use App\Models\CuentaPorCobrar;
use App\Models\Venta;
use Illuminate\Database\Seeder;

class CuentaPorCobrarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $ventas = Venta::query()->get(['id', 'total']);

        if ($ventas->isEmpty()) {
            return;
        }

        foreach ($ventas as $venta) {
            CuentaPorCobrar::query()->firstOrCreate(
                ['id_venta' => $venta->id],
                [
                    'monto_original' => (float) $venta->total,
                    'saldo' => (float) $venta->total,
                    'estado' => CuentaPorCobrar::EstadoPendiente,
                    'observaciones' => 'Cuenta generada por seeder.',
                ]
            );
        }
    }

    /**
     * Funcionamiento: crea cuentas por cobrar faltantes para ventas existentes.
     * Tablas: cuenta_por_cobrar, ventas.
     * Flujo: recorre ventas y crea saldo pendiente si no existe registro.
     */
}
