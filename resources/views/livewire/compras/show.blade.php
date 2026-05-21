<?php

use App\Models\Compra;
use App\Services\CompraService;
use Livewire\Volt\Component;

/**
 * // Autor: Diego Méndez - Fecha: 21/05/2026
 */
new class extends Component {
    public Compra $compra;

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function mount(Compra $compra): void
    {
        $this->compra = $compra->load(['proveedor', 'detalles.producto']);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function confirm(CompraService $service): void
    {
        $this->compra = $service->confirmCompra($this->compra);

        session()->flash('success', 'Compra confirmada correctamente.');
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function receive(CompraService $service): void
    {
        $this->compra = $service->receiveCompra($this->compra);

        session()->flash('success', 'Mercaderia recibida y stock actualizado.');
    }
}; ?>

<div class="space-y-6">
    @if (session('success'))
        <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">{{ $compra->codigo }}</h2>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Proveedor: {{ $compra->proveedor?->nombre ?? 'Sin proveedor' }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if ($compra->estado === \App\Models\Compra::EstadoBorrador)
                <flux:button type="button" wire:click="confirm" class="px-3 py-1.5 text-xs">
                    Confirmar compra
                </flux:button>
            @endif

            @if ($compra->estado === \App\Models\Compra::EstadoConfirmada)
                <flux:button type="button" wire:click="receive" class="px-3 py-1.5 text-xs">
                    Recibir mercaderia
                </flux:button>
            @endif
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Estado</dt>
            <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ ucfirst($compra->estado) }}</dd>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Fecha</dt>
            <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ $compra->fecha_compra?->format('d/m/Y') ?? 'Sin fecha' }}</dd>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Total</dt>
            <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ number_format((float) $compra->total, 2) }}</dd>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Recepcion</dt>
            <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ $compra->fecha_recepcion?->format('d/m/Y H:i') ?? 'Pendiente' }}</dd>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700 sm:col-span-2">
            <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Observaciones</dt>
            <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ $compra->observaciones ?? 'Sin observaciones' }}</dd>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                    <tr>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3">Cantidad</th>
                        <th class="px-4 py-3">Precio costo</th>
                        <th class="px-4 py-3">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($compra->detalles as $detalle)
                        <tr class="text-zinc-800 dark:text-zinc-100">
                            <td class="px-4 py-3 font-medium">{{ $detalle->producto?->nombre ?? 'Producto eliminado' }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $detalle->cantidad }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ number_format((float) $detalle->precio_costo, 2) }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ number_format((float) $detalle->subtotal, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">No hay detalles registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
