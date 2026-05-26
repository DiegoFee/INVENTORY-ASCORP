<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Carbon;

class StatisticsService
{
    /**
     * @return list<array{mes:string,total:float}>
     */
    public function getSalesByLast12Months(): array
    {
        $start = Carbon::now()->startOfMonth()->subMonths(11);

        $rows = Venta::query()
            ->where('created_at', '>=', $start)
            ->get(['created_at', 'total'])
            ->groupBy(fn (Venta $venta): string => $venta->created_at?->format('Y-m') ?? '')
            ->map(fn ($group): float => (float) $group->sum('total'));

        $series = [];

        for ($i = 0; $i < 12; $i++) {
            $date = $start->copy()->addMonths($i);
            $period = $date->format('Y-m');
            $series[] = [
                'mes' => $date->translatedFormat('F'),
                'total' => (float) ($rows->get($period) ?? 0),
            ];
        }

        return $series;
    }

    /**
     * @return array{normal:int,bajo_minimo:int,sin_stock:int}
     */
    public function getInventoryStatusDistribution(): array
    {
        $counts = Producto::query()
            ->selectRaw('SUM(CASE WHEN stock_actual = 0 THEN 1 ELSE 0 END) as sin_stock')
            ->selectRaw('SUM(CASE WHEN stock_actual > 0 AND stock_actual <= stock_minimo THEN 1 ELSE 0 END) as bajo_minimo')
            ->selectRaw('SUM(CASE WHEN stock_actual > stock_minimo THEN 1 ELSE 0 END) as normal')
            ->first();

        return [
            'normal' => (int) ($counts?->normal ?? 0),
            'bajo_minimo' => (int) ($counts?->bajo_minimo ?? 0),
            'sin_stock' => (int) ($counts?->sin_stock ?? 0),
        ];
    }
}
