<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Compra;
use App\Models\CuentaPorCobrar;
use App\Models\Producto;
use App\Models\ServicioFoso;
use App\Models\Venta;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    public function __construct(
        private readonly KpiService $kpiService,
        private readonly StatisticsService $statisticsService,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function getDashboardData(): array
    {
        return Cache::remember(
            'dashboard_stats',
            now()->addMinutes(5),
            fn (): array => [
                'kpis' => $this->getGeneralKPIs(),
                'recent_activities' => $this->getRecentActivities(),
                'quick_actions' => $this->getQuickActions(),
                'pending_tasks' => $this->getPendingTasks(),
                'sales_chart' => $this->statisticsService->getSalesByLast12Months(),
                'inventory_chart' => $this->statisticsService->getInventoryStatusDistribution(),
                'alerts' => $this->getActiveAlerts(),
            ],
        );
    }

    /**
     * @return array<string, int|float>
     */
    public function getGeneralKPIs(): array
    {
        $kpis = $this->kpiService->getGeneralKPIs();
        $kpis['alertas_activas'] = $this->getActiveAlerts()->count();

        return $kpis;
    }

    /**
     * @return Collection<int, array{usuario:string,accion:string,fecha:string}>
     */
    public function getRecentActivities(): Collection
    {
        $latestVenta = Venta::query()->with('usuario')->latest('created_at')->first();
        $latestCompra = Compra::query()->with('proveedor')->latest('created_at')->first();
        $latestServicio = ServicioFoso::query()->with('tecnico')->latest('created_at')->first();
        $latestPago = CuentaPorCobrar::query()
            ->with('pagos')
            ->whereHas('pagos')
            ->latest('updated_at')
            ->first();

        return collect([
            $latestVenta ? [
                'usuario' => $latestVenta->usuario?->name ?? 'Sistema',
                'accion' => 'Registró una venta #'.$latestVenta->id,
                'fecha' => $latestVenta->created_at?->diffForHumans() ?? '-',
            ] : null,
            $latestPago ? [
                'usuario' => 'Tesorería',
                'accion' => 'Registró un pago en cuenta #'.$latestPago->id,
                'fecha' => $latestPago->updated_at?->diffForHumans() ?? '-',
            ] : null,
            $latestCompra ? [
                'usuario' => 'Compras',
                'accion' => 'Registró compra #'.$latestCompra->id,
                'fecha' => $latestCompra->created_at?->diffForHumans() ?? '-',
            ] : null,
            $latestServicio ? [
                'usuario' => $latestServicio->tecnico?->name ?? 'Técnico',
                'accion' => 'Registró servicio de foso #'.$latestServicio->id,
                'fecha' => $latestServicio->created_at?->diffForHumans() ?? '-',
            ] : null,
        ])->filter()->values();
    }

    /**
     * @return list<array{label:string,route:string,icon:string}>
     */
    public function getQuickActions(): array
    {
        return [
            ['label' => 'Nueva Venta', 'route' => 'ventas.create', 'icon' => 'shopping-cart'],
            ['label' => 'Nuevo Cliente', 'route' => 'clientes.create', 'icon' => 'user-plus'],
            ['label' => 'Nuevo Producto', 'route' => 'productos.create', 'icon' => 'archive-box'],
            ['label' => 'Nueva Compra', 'route' => 'compras.create', 'icon' => 'truck'],
            ['label' => 'Registrar Abono', 'route' => 'cxc.index', 'icon' => 'banknotes'],
            ['label' => 'Generar Reporte', 'route' => 'reports.ventas', 'icon' => 'document-chart-bar'],
        ];
    }

    /**
     * @return list<array{label:string,count:int,route:string}>
     */
    public function getPendingTasks(): array
    {
        $productosReabastecer = Producto::query()->lowStock()->active()->count();
        $cxcVencidas = CuentaPorCobrar::query()
            ->whereDate('fecha_vencimiento', '<', Carbon::today()->toDateString())
            ->where('saldo', '>', 0)
            ->count();
        $serviciosAbiertos = ServicioFoso::query()
            ->whereNotIn('estado', ['cerrado', 'finalizado'])
            ->count();

        return [
            ['label' => 'Productos por reabastecer', 'count' => $productosReabastecer, 'route' => 'productos.index'],
            ['label' => 'CxC vencidas', 'count' => $cxcVencidas, 'route' => 'cxc.index'],
            ['label' => 'Servicios abiertos', 'count' => $serviciosAbiertos, 'route' => 'foso.index'],
        ];
    }

    /**
     * @return Collection<int, array{tipo:string,mensaje:string,severidad:int}>
     */
    public function getActiveAlerts(): Collection
    {
        $stockAlerts = Producto::query()
            ->lowStock()
            ->active()
            ->orderBy('stock_actual')
            ->limit(10)
            ->get(['id', 'nombre', 'stock_actual', 'stock_minimo'])
            ->map(fn (Producto $producto): array => [
                'tipo' => 'inventario',
                'mensaje' => "{$producto->nombre}: stock {$producto->stock_actual} / mínimo {$producto->stock_minimo}",
                'severidad' => $producto->stock_actual === 0 ? 100 : 70,
            ]);

        $cxcAlerts = CuentaPorCobrar::query()
            ->whereDate('fecha_vencimiento', '<', Carbon::today()->toDateString())
            ->where('saldo', '>', 0)
            ->orderByDesc('saldo')
            ->limit(10)
            ->get(['id', 'saldo'])
            ->map(fn (CuentaPorCobrar $cuenta): array => [
                'tipo' => 'cxc',
                'mensaje' => "Cuenta #{$cuenta->id} vencida con saldo ".number_format((float) $cuenta->saldo, 2),
                'severidad' => 80,
            ]);

        $serviceAlerts = ServicioFoso::query()
            ->whereNotIn('estado', ['cerrado', 'finalizado'])
            ->orderByDesc('created_at')
            ->limit(10)
            ->get(['id', 'estado'])
            ->map(fn (ServicioFoso $servicio): array => [
                'tipo' => 'servicio',
                'mensaje' => "Servicio de foso #{$servicio->id} pendiente ({$servicio->estado})",
                'severidad' => 60,
            ]);

        return $stockAlerts
            ->concat($cxcAlerts)
            ->concat($serviceAlerts)
            ->sortByDesc('severidad')
            ->take(10)
            ->values();
    }
}
