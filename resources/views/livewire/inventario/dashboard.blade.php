<?php

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Repositories\MovimientoInventarioRepositoryInterface;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;

/**
 * // Autor: Diego Méndez - Fecha: 21/05/2026
 */
new class extends Component {
    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function getResumenProperty(): array
    {
        return [
            'total_productos' => Producto::query()->count(),
            'stock_bajo' => Producto::query()->lowStock()->count(),
            'movimientos_hoy' => MovimientoInventario::query()
                ->whereDate('created_at', now()->toDateString())
                ->count(),
        ];
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function getMovimientosRecientesProperty(): Collection
    {
        return app(MovimientoInventarioRepositoryInterface::class)
            ->getRecent(8);
    }
}; ?>

<div class="space-y-6">
    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Productos</p>
            <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">{{ $this->resumen['total_productos'] }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Stock bajo</p>
            <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">{{ $this->resumen['stock_bajo'] }}</p>
        </div>
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Movimientos hoy</p>
            <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">{{ $this->resumen['movimientos_hoy'] }}</p>
        </div>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('inventario.entrada') }}" class="rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
            Registrar entrada
        </a>
        <a href="{{ route('inventario.salida') }}" class="rounded-md border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-200 dark:hover:bg-zinc-800">
            Registrar salida
        </a>
    </div>

    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                    <tr>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Cantidad</th>
                        <th class="px-4 py-3">Stock</th>
                        <th class="px-4 py-3">Fecha</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->movimientosRecientes as $movimiento)
                        <tr class="text-zinc-800 dark:text-zinc-100">
                            <td class="px-4 py-3 font-medium">{{ $movimiento->producto?->nombre ?? 'Producto eliminado' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $movimiento->tipo->value === 'entrada' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-200' : 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-200' }}">
                                    {{ ucfirst($movimiento->tipo->value) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $movimiento->cantidad }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $movimiento->stock_anterior }} → {{ $movimiento->stock_nuevo }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $movimiento->created_at?->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No hay movimientos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
