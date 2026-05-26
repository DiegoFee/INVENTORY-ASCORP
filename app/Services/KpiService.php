<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Compra;
use App\Models\CuentaPorCobrar;
use App\Models\Pago;
use App\Models\Producto;
use App\Models\ServicioFoso;
use App\Models\Venta;
use Illuminate\Support\Carbon;

class KpiService
{
    /**
     * @return array<string, int|float>
     */
    public function getGeneralKPIs(): array
    {
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $ventasDelDia = Venta::query()
            ->whereDate('created_at', $today)
            ->sum('total');

        $cantidadVentasDelDia = Venta::query()
            ->whereDate('created_at', $today)
            ->count();

        $ventasDelMes = Venta::query()
            ->whereBetween('created_at', [$monthStart, Carbon::now()])
            ->sum('total');

        $ticketPromedio = $cantidadVentasDelDia > 0
            ? (float) $ventasDelDia / $cantidadVentasDelDia
            : 0.0;

        $totalProductos = Producto::query()->count();
        $productosActivos = Producto::query()->active()->count();
        $productosStockCritico = Producto::query()->lowStock()->active()->count();
        $valorInventario = (float) (Producto::query()
            ->selectRaw('COALESCE(SUM(stock_actual * precio_costo), 0) as total')
            ->value('total') ?? 0);

        $totalCuentasPorCobrar = CuentaPorCobrar::query()->count();
        $cuentasVencidas = CuentaPorCobrar::query()
            ->whereDate('fecha_vencimiento', '<', $today)
            ->where('saldo', '>', 0)
            ->count();

        $montoPendienteCobro = (float) CuentaPorCobrar::query()
            ->where('saldo', '>', 0)
            ->sum('saldo');

        $cobradoEsteMes = (float) Pago::query()
            ->whereBetween('fecha_pago', [$monthStart->toDateString(), Carbon::now()->toDateString()])
            ->sum('monto');

        $serviciosFosoHoy = ServicioFoso::query()
            ->whereDate('fecha', $today)
            ->count();

        $comprasMes = Compra::query()
            ->whereBetween('created_at', [$monthStart, Carbon::now()])
            ->count();

        return [
            'ventas_dia' => (float) $ventasDelDia,
            'ventas_mes' => (float) $ventasDelMes,
            'cantidad_ventas_dia' => $cantidadVentasDelDia,
            'ticket_promedio' => (float) $ticketPromedio,
            'total_productos' => $totalProductos,
            'productos_activos' => $productosActivos,
            'productos_stock_critico' => $productosStockCritico,
            'valor_total_inventario' => $valorInventario,
            'total_cuentas_por_cobrar' => $totalCuentasPorCobrar,
            'cuentas_vencidas' => $cuentasVencidas,
            'monto_pendiente_cobro' => $montoPendienteCobro,
            'cobrado_este_mes' => $cobradoEsteMes,
            'servicios_foso_hoy' => $serviciosFosoHoy,
            'compras_mes' => $comprasMes,
        ];
    }
}
