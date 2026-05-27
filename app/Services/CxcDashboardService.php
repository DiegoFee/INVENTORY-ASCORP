<?php

namespace App\Services;

use App\Models\CuentaPorCobrar;
use Carbon\Carbon;

class CxcDashboardService
{
    public function getIndicadores(): array
    {
        $hoy = Carbon::today();

        $cuentasPendientes = CuentaPorCobrar::where('estado', 'pendiente');

        $totalCuentas = $cuentasPendientes->count();
        $totalAdeudado = $cuentasPendientes->sum('saldo');

        $cuentasVencidas = CuentaPorCobrar::where('estado', 'pendiente')
            ->whereDate('fecha_vencimiento', '<', $hoy);

        $totalVencidas = $cuentasVencidas->count();
        $montoVencido = $cuentasVencidas->sum('saldo');

        $ultimasCuentas = CuentaPorCobrar::with('cliente')
            ->where('estado', 'pendiente')
            ->orderBy('fecha_vencimiento', 'asc')
            ->limit(5)
            ->get();

        return [
            'total_cuentas' => $totalCuentas,
            'total_adeudado' => $totalAdeudado,
            'total_vencidas' => $totalVencidas,
            'monto_vencido' => $montoVencido,
            'ultimas_cuentas' => $ultimasCuentas,
        ];
    }
}
