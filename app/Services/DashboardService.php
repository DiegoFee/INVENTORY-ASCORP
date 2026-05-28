<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\Permission;
use App\Models\Compra;
use App\Models\CuentaPorCobrar;
use App\Models\Producto;
use App\Models\ServicioFoso;
use App\Models\Venta;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Gate;

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
        return [
            'kpis' => $this->getGeneralKPIs(),
            'recent_activities' => $this->getRecentActivities(),
            'quick_actions' => $this->getQuickActions(),
            'pending_tasks' => $this->getPendingTasks(),
            'sales_chart' => Gate::allows(Permission::VentasView->value)
                ? $this->statisticsService->getSalesByLast12Months()
                : [],
            'inventory_chart' => Gate::allows(Permission::InventarioView->value)
                ? $this->statisticsService->getInventoryStatusDistribution()
                : [],
            'alerts' => $this->getActiveAlerts(),
        ];
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
        if (Gate::denies(Permission::DashboardActivityView->value)) {
            return collect();
        }

        $latestVenta = Gate::allows(Permission::VentasView->value)
            ? Venta::query()->with('usuario')->latest('created_at')->first()
            : null;
        $latestCompra = Gate::allows(Permission::ComprasView->value)
            ? Compra::query()->with('proveedor')->latest('created_at')->first()
            : null;
        $latestServicio = Gate::allows(Permission::FosoView->value)
            ? ServicioFoso::query()->with('tecnico')->latest('created_at')->first()
            : null;
        $latestPago = Gate::allows(Permission::CxcView->value)
            ? CuentaPorCobrar::query()
                ->with('pagos')
                ->whereHas('pagos')
                ->latest('updated_at')
                ->first()
            : null;

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
        return collect([
            ['label' => 'Nueva Venta', 'route' => 'ventas.create', 'icon' => 'shopping-cart', 'permission' => Permission::VentasCreate],
            ['label' => 'Nuevo Cliente', 'route' => 'clientes.create', 'icon' => 'user-plus', 'permission' => Permission::ClientsView],
            ['label' => 'Nuevo Producto', 'route' => 'productos.create', 'icon' => 'archive-box', 'permission' => Permission::ProductosCreate],
            ['label' => 'Nueva Compra', 'route' => 'compras.create', 'icon' => 'truck', 'permission' => Permission::ComprasCreate],
            ['label' => 'Registrar Abono', 'route' => 'cxc.index', 'icon' => 'banknotes', 'permission' => Permission::CxcUpdate],
            ['label' => 'Generar Reporte', 'route' => 'reports.ventas', 'icon' => 'document-chart-bar', 'permission' => Permission::ReportsVentasView],
        ])
            ->filter(fn (array $action): bool => Gate::allows($action['permission']->value))
            ->map(fn (array $action): array => [
                'label' => $action['label'],
                'route' => $action['route'],
                'icon' => $action['icon'],
            ])
            ->values()
            ->all();
    }

    /**
     * @return list<array{label:string,count:int,route:string}>
     */
    public function getPendingTasks(): array
    {
        $tasks = [];

        if (Gate::allows(Permission::ProductosView->value)) {
            $tasks[] = [
                'label' => 'Productos por reabastecer',
                'count' => Producto::query()->lowStock()->active()->count(),
                'route' => 'productos.index',
            ];
        }

        if (Gate::allows(Permission::CxcView->value)) {
            $tasks[] = [
                'label' => 'CxC vencidas',
                'count' => CuentaPorCobrar::query()
                    ->whereDate('fecha_vencimiento', '<', Carbon::today()->toDateString())
                    ->where('saldo', '>', 0)
                    ->count(),
                'route' => 'cxc.index',
            ];
        }

        if (Gate::allows(Permission::FosoView->value)) {
            $tasks[] = [
                'label' => 'Servicios abiertos',
                'count' => ServicioFoso::query()
                    ->whereNotIn('estado', ['cerrado', 'finalizado'])
                    ->count(),
                'route' => 'foso.index',
            ];
        }

        return $tasks;
    }

    /**
     * @return Collection<int, array{tipo:string,mensaje:string,severidad:int}>
     */
    public function getActiveAlerts(): Collection
    {
        $stockAlerts = Gate::allows(Permission::InventarioView->value)
            ? Producto::query()
                ->lowStock()
                ->active()
                ->orderBy('stock_actual')
                ->limit(10)
                ->get(['id', 'nombre', 'stock_actual', 'stock_minimo'])
                ->map(fn (Producto $producto): array => [
                    'tipo' => 'inventario',
                    'mensaje' => "{$producto->nombre}: stock {$producto->stock_actual} / mínimo {$producto->stock_minimo}",
                    'severidad' => $producto->stock_actual === 0 ? 100 : 70,
                ])
            : collect();

        $cxcAlerts = Gate::allows(Permission::CxcView->value)
            ? CuentaPorCobrar::query()
                ->whereDate('fecha_vencimiento', '<', Carbon::today()->toDateString())
                ->where('saldo', '>', 0)
                ->orderByDesc('saldo')
                ->limit(10)
                ->get(['id', 'saldo'])
                ->map(fn (CuentaPorCobrar $cuenta): array => [
                    'tipo' => 'cxc',
                    'mensaje' => "Cuenta #{$cuenta->id} vencida con saldo ".number_format((float) $cuenta->saldo, 2),
                    'severidad' => 80,
                ])
            : collect();

        $serviceAlerts = Gate::allows(Permission::FosoView->value)
            ? ServicioFoso::query()
                ->whereNotIn('estado', ['cerrado', 'finalizado'])
                ->orderByDesc('created_at')
                ->limit(10)
                ->get(['id', 'estado'])
                ->map(fn (ServicioFoso $servicio): array => [
                    'tipo' => 'servicio',
                    'mensaje' => "Servicio de foso #{$servicio->id} pendiente ({$servicio->estado})",
                    'severidad' => 60,
                ])
            : collect();

        return $stockAlerts
            ->concat($cxcAlerts)
            ->concat($serviceAlerts)
            ->sortByDesc('severidad')
            ->take(10)
            ->values();
    }
}
