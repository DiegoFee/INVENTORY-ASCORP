<?php

use App\Models\Producto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';
    public int $perPage = 12;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function getProductosProperty(): LengthAwarePaginator
    {
        return Producto::query()
            ->active()
            ->when($this->search !== '' && strlen($this->search) >= 2, function ($q) {
                $like = '%' . $this->search . '%';
                $q->where('nombre', 'like', $like)
                    ->orWhere('sku', 'like', $like);
            })
            ->orderBy('nombre')
            ->paginate($this->perPage);
    }

    public function addProduct(int $id): void
    {
        $producto = Producto::find($id);

        if ($producto === null) {
            return;
        }

        $this->dispatch('productSelected', productId: $producto->id);
        $this->search = '';
    }
} ?>

<div class="space-y-3">
    <flux:field>
        <flux:label>Buscar producto</flux:label>
        <flux:input
            type="text"
            placeholder="Buscar por nombre o SKU…"
            wire:model.live="search"
        />
    </flux:field>

    @if ($this->productos->isNotEmpty())
        <div class="grid grid-cols-2 gap-3 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
            @foreach ($this->productos as $producto)
                <div class="flex flex-col rounded-lg border border-zinc-200 bg-white p-3 shadow-sm transition hover:shadow-md dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-start gap-3">
                        <div class="flex size-10 shrink-0 items-center justify-center rounded-md bg-zinc-100 text-zinc-400 dark:bg-zinc-800">
                            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m8.25 3v6.75m0 0-3-3m3 3 3-3M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                            </svg>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate font-semibold text-zinc-900 dark:text-white">{{ $producto->nombre }}</p>
                            <p class="truncate text-xs text-zinc-500 dark:text-zinc-400">SKU: {{ $producto->sku }}</p>
                        </div>
                    </div>
                    <div class="mt-2 flex items-center justify-between">
                        <div>
                            <span class="text-sm font-bold text-emerald-600 dark:text-emerald-400">
                                ${{ number_format($producto->precio_venta, 2) }}
                            </span>
                            <span class="ml-2 text-xs {{ $producto->stock_actual > $producto->stock_minimo ? 'text-zinc-500' : 'text-red-600' }}">
                                Stock: {{ $producto->stock_actual }}
                            </span>
                        </div>
                        <flux:button
                            type="button"
                            size="sm"
                            wire:click="addProduct({{ $producto->id }})"
                        >
                            Agregar
                        </flux:button>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($this->productos->hasPages())
            <div class="pt-2">
                {{ $this->productos->links() }}
            </div>
        @endif
    @elseif ($this->search !== '' && strlen($this->search) >= 2)
        <p class="text-sm text-zinc-500 dark:text-zinc-400">Sin resultados para "{{ $this->search }}"</p>
    @else
        <p class="text-sm text-zinc-400 dark:text-zinc-500">Comienza a escribir para buscar productos</p>
    @endif
</div>
