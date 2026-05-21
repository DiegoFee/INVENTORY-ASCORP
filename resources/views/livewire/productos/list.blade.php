<?php

use App\Models\Producto;
use App\Repositories\ProductoRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Volt\Component;
use Livewire\WithPagination;

/**
 * // Autor: Diego Méndez - Fecha: 20/05/2026
 */
new class extends Component {
    use WithPagination;

    public string $search = '';

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function getProductosProperty(): LengthAwarePaginator
    {
        return app(ProductoRepositoryInterface::class)
            ->paginateForList($this->search);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function delete(int $productoId): void
    {
        $producto = Producto::query()->whereKey($productoId)->first();

        if (! $producto) {
            return;
        }

        app(ProductoRepositoryInterface::class)->delete($producto);

        session()->flash('success', 'Producto eliminado correctamente.');
    }
}; ?>

<div class="space-y-5">
    <div class="max-w-xl">
        <flux:input
            wire:model.debounce.300ms="search"
            label="Buscar"
            name="search"
            placeholder="Buscar por SKU o nombre"
        />
    </div>

    @if (session('success'))
        <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                    <tr>
                        <th class="px-4 py-3">SKU</th>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Categoria</th>
                        <th class="px-4 py-3">Stock</th>
                        <th class="px-4 py-3">Precio venta</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->productos as $producto)
                        <tr class="text-zinc-800 dark:text-zinc-100">
                            <td class="px-4 py-3 font-medium">{{ $producto->sku }}</td>
                            <td class="px-4 py-3">{{ $producto->nombre }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $producto->categoria }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">
                                        {{ $producto->stock_actual }}
                                    </span>
                                    <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                        Min {{ $producto->stock_minimo }}
                                    </span>
                                    @if ($producto->stock_actual <= $producto->stock_minimo)
                                        <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700 dark:bg-red-950 dark:text-red-200">
                                            Bajo
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                {{ number_format((float) $producto->precio_venta, 2) }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $producto->activo ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-200' : 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-200' }}">
                                    {{ $producto->activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <flux:button
                                        href="{{ route('productos.show', $producto) }}"
                                        wire:navigate
                                        class="px-3 py-1.5 text-xs"
                                    >
                                        Ver
                                    </flux:button>
                                    <flux:button
                                        href="{{ route('productos.edit', $producto) }}"
                                        wire:navigate
                                        class="px-3 py-1.5 text-xs"
                                    >
                                        Editar
                                    </flux:button>
                                    <flux:button
                                        type="button"
                                        class="px-3 py-1.5 text-xs text-red-700 dark:text-red-300"
                                        onclick="return confirm('Confirmas eliminar este producto?')"
                                        wire:click="delete({{ $producto->id }})"
                                    >
                                        Eliminar
                                    </flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No hay productos registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $this->productos->links() }}
</div>
