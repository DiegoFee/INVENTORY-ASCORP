<?php

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Repositories\MovimientoInventarioRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Volt\Component;
use Livewire\WithPagination;

/**
 * // Autor: Diego Méndez - Fecha: 21/05/2026
 */
new class extends Component {
    use WithPagination;

    public Producto $producto;
    public string $tipo = 'todos';

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function mount(Producto $producto): void
    {
        $this->producto = $producto;
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function updatingTipo(): void
    {
        $this->resetPage();
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function getMovimientosProperty(): LengthAwarePaginator
    {
        $tipo = $this->tipo === 'todos' ? null : $this->tipo;

        return app(MovimientoInventarioRepositoryInterface::class)
            ->paginateByProducto($this->producto, $tipo);
    }
}; ?>

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $producto->nombre }}</h2>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">SKU: {{ $producto->sku }}</p>
        </div>
        <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300" for="tipo">Filtrar</label>
            <select
                id="tipo"
                wire:model="tipo"
                class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
            >
                <option value="todos">Todos</option>
                <option value="{{ \App\Models\MovimientoInventario::TipoEntrada }}">Entradas</option>
                <option value="{{ \App\Models\MovimientoInventario::TipoSalida }}">Salidas</option>
            </select>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                    <tr>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Cantidad</th>
                        <th class="px-4 py-3">Stock</th>
                        <th class="px-4 py-3">Origen</th>
                        <th class="px-4 py-3">Observaciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->movimientos as $movimiento)
                        <tr class="text-zinc-800 dark:text-zinc-100">
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $movimiento->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $movimiento->tipo === 'entrada' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-200' : 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-200' }}">
                                    {{ ucfirst($movimiento->tipo) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $movimiento->cantidad }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $movimiento->stock_anterior }} → {{ $movimiento->stock_nuevo }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ ucfirst($movimiento->origen) }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $movimiento->observaciones ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">No hay movimientos registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $this->movimientos->links() }}
</div>
