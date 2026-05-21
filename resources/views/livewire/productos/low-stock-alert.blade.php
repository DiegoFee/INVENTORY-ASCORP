<?php

use App\Repositories\ProductoRepositoryInterface;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;

/**
 * // Autor: Diego Méndez - Fecha: 20/05/2026
 */
new class extends Component {
    public int $limit = 5;

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function getLowStockProperty(): Collection
    {
        return app(ProductoRepositoryInterface::class)
            ->getLowStockProducts($this->limit);
    }
}; ?>

<div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-900 dark:border-amber-900/60 dark:bg-amber-950/40 dark:text-amber-200">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <p class="text-xs font-semibold uppercase">Stock bajo</p>
            <p class="text-sm text-amber-800 dark:text-amber-200">
                {{ $this->lowStock->count() }} producto(s) por debajo del minimo.
            </p>
        </div>
        <a
            href="{{ route('productos.index') }}"
            class="text-xs font-semibold text-amber-900 underline decoration-amber-400/70 underline-offset-4 hover:decoration-amber-900 dark:text-amber-100"
        >
            Ver detalle
        </a>
    </div>

    @if ($this->lowStock->isNotEmpty())
        <div class="mt-3 grid gap-2 text-sm">
            @foreach ($this->lowStock as $producto)
                <div class="flex items-center justify-between gap-3">
                    <span class="font-medium">{{ $producto->nombre }}</span>
                    <span class="text-xs text-amber-700 dark:text-amber-300">
                        {{ $producto->stock_actual }} / {{ $producto->stock_minimo }}
                    </span>
                </div>
            @endforeach
        </div>
    @endif
</div>
