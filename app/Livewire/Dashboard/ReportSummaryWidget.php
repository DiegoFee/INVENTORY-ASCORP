<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Enums\Permission;
use App\Models\CuentaPorCobrar;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

/**
 * Autor: Arandi Hurtado
 * Descripcion: Widget de integracion de reportes estadisticos para el Dashboard.
 */
class ReportSummaryWidget extends Component
{
    public function mount(): void
    {
        Gate::authorize(Permission::ReportsVentasView->value);
    }

    public function render(): View
    {
        return view('livewire.dashboard.report-summary-widget', [
            'ventasMesActual' => $this->ventasMesActual,
            'valorTotalInventario' => $this->valorTotalInventario,
            'cantidadTotalInventario' => $this->cantidadTotalInventario,
            'productosStockCritico' => $this->productosStockCritico,
            'saldosCxcPorVencer' => $this->saldosCxcPorVencer,
        ]);
    }

    #[Computed]
    public function valorTotalInventario(): float
    {
        $total = Producto::query()
            ->selectRaw('COALESCE(SUM(precio_venta * stock_actual), 0) as total')
            ->value('total');

        if ((float) ($total ?? 0) <= 0) {
            Log::info('ReportSummaryWidget inventario total=0', [
                'db' => $this->databaseInfo(),
                'count' => Producto::query()->count(),
                'sample' => Producto::query()
                    ->select(['id', 'precio_venta', 'stock_actual'])
                    ->limit(3)
                    ->get()
                    ->toArray(),
            ]);
        }

        return (float) ($total ?? 0);
    }

    #[Computed]
    public function cantidadTotalInventario(): int
    {
        return Producto::query()->count();
    }

    #[Computed]
    public function productosStockCritico(): int
    {
        return Producto::query()->lowStock()->active()->count();
    }

    /**
     * @return array{total:float, change:?float, trend:'up'|'down'|'flat'|'neutral'}
     */
    #[Computed]
    public function ventasMesActual(): array
    {
        $now = Carbon::now();
        $currentStart = $now->copy()->startOfMonth();

        $currentTotal = (float) Venta::query()
            ->whereBetween('created_at', [$currentStart, $now])
            ->sum('total');

        $currentCount = Venta::query()
            ->whereBetween('created_at', [$currentStart, $now])
            ->count();

        $overallTotal = 0.0;
        $overallCount = 0;
        $fallbackToOverall = false;

        if ($currentTotal <= 0) {
            $overallTotal = (float) Venta::query()->sum('total');
            $overallCount = Venta::query()->count();

            Log::info('ReportSummaryWidget ventas mes=0', [
                'db' => $this->databaseInfo(),
                'current_start' => $currentStart->toDateTimeString(),
                'current_end' => $now->toDateTimeString(),
                'current_total' => $currentTotal,
                'current_count' => $currentCount,
                'overall_total' => $overallTotal,
                'overall_count' => $overallCount,
            ]);

            if ($overallTotal > 0) {
                $currentTotal = $overallTotal;
                $currentCount = $overallCount;
                $fallbackToOverall = true;
            }
        }

        $previousStart = $currentStart->copy()->subMonth();
        $previousEnd = $currentStart->copy()->subSecond();

        $ticketPromedio = $currentCount > 0
            ? $currentTotal / (float) $currentCount
            : 0.0;

        $change = null;
        $trend = 'neutral';

        if (! $fallbackToOverall) {
            $previousTotal = (float) Venta::query()
                ->whereBetween('created_at', [$previousStart, $previousEnd])
                ->sum('total');

            if ($previousTotal > 0) {
                $change = (($currentTotal - $previousTotal) / $previousTotal) * 100;
                if ($change > 0) {
                    $trend = 'up';
                } elseif ($change < 0) {
                    $trend = 'down';
                } else {
                    $trend = 'flat';
                }
            }
        }

        return [
            'total' => $currentTotal,
            'change' => $change,
            'trend' => $trend,
            'ticket_promedio' => $ticketPromedio,
        ];
    }

    #[Computed]
    public function saldosCxcPorVencer(): float
    {
        $today = Carbon::today();
        $limit = $today->copy()->addDays(7);

        $baseQuery = CuentaPorCobrar::query()->where('saldo', '>', 0);

        $total = (float) (clone $baseQuery)
            ->whereDate('fecha_vencimiento', '>=', $today->toDateString())
            ->whereDate('fecha_vencimiento', '<=', $limit->toDateString())
            ->sum('saldo');

        if ($total <= 0) {
            $overallTotal = (float) (clone $baseQuery)->sum('saldo');
            $overallCount = (clone $baseQuery)->count();

            Log::info('ReportSummaryWidget cxc rango=0', [
                'db' => $this->databaseInfo(),
                'range_start' => $today->toDateString(),
                'range_end' => $limit->toDateString(),
                'range_total' => $total,
                'overall_total' => $overallTotal,
                'overall_count' => $overallCount,
            ]);

            if ($overallTotal > 0) {
                $total = $overallTotal;
            }
        }

        return $total;
    }

    /**
     * @return array{default:string, database:string}
     */
    private function databaseInfo(): array
    {
        $default = config('database.default');
        $database = (string) config("database.connections.{$default}.database");

        return [
            'default' => (string) $default,
            'database' => $database,
        ];
    }
}
