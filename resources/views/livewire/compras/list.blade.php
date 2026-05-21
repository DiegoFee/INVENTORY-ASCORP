<?php

use App\Repositories\CompraRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Volt\Component;
use Livewire\WithPagination;

/**
 * // Autor: Diego Méndez - Fecha: 21/05/2026
 */
new class extends Component {
    use WithPagination;

    public string $search = '';

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function getComprasProperty(): LengthAwarePaginator
    {
        return app(CompraRepositoryInterface::class)
            ->paginateForList($this->search);
    }
}; ?>

<div class="space-y-5">
    <div class="max-w-xl">
        <flux:input
            wire:model.debounce.300ms="search"
            label="Buscar"
            name="search"
            placeholder="Buscar por codigo o proveedor"
        />
    </div>

    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                    <tr>
                        <th class="px-4 py-3">Codigo</th>
                        <th class="px-4 py-3">Proveedor</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->compras as $compra)
                        <tr class="text-zinc-800 dark:text-zinc-100">
                            <td class="px-4 py-3 font-medium">{{ $compra->codigo }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                {{ $compra->proveedor?->nombre ?? 'Sin proveedor' }}
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                {{ $compra->fecha_compra?->format('d/m/Y') ?? 'Sin fecha' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $compra->estado === 'recibida' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-200' : ($compra->estado === 'confirmada' ? 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-200' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200') }}">
                                    {{ ucfirst($compra->estado) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                {{ number_format((float) $compra->total, 2) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <flux:button
                                        href="{{ route('compras.show', $compra) }}"
                                        wire:navigate
                                        class="px-3 py-1.5 text-xs"
                                    >
                                        Ver
                                    </flux:button>
                                    <flux:button
                                        href="{{ route('compras.edit', $compra) }}"
                                        wire:navigate
                                        class="px-3 py-1.5 text-xs"
                                    >
                                        Editar
                                    </flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No hay compras registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $this->compras->links() }}
</div>
