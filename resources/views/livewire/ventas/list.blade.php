<?php

use App\Models\Cliente;
use App\Models\Venta;
use App\Repositories\VentaRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function getVentasProperty(): LengthAwarePaginator
    {
        return app(VentaRepositoryInterface::class)
            ->paginateForList($this->search);
    }
}; ?>

<div class="space-y-5">
    <div class="max-w-xl">
        <flux:input
            wire:model.debounce.300ms="search"
            label="Buscar"
            name="search"
            placeholder="Buscar por id o cliente"
        />
    </div>

    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Cliente</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Total</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->ventas as $venta)
                        <tr class="text-zinc-800 dark:text-zinc-100">
                            <td class="px-4 py-3 font-medium">{{ $venta->id }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                {{ $venta->cliente?->nombre ?? 'Consumidor final' }}
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                {{ $venta->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $venta->estado === \App\Models\Venta::EstadoCerrada ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-200' : ($venta->estado === \App\Models\Venta::EstadoConfirmada ? 'bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-200' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200') }}">
                                    {{ ucfirst($venta->estado) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                {{ number_format((float) $venta->total, 2) }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <flux:button
                                        href="{{ route('ventas.show', $venta) }}"
                                        wire:navigate
                                        class="px-3 py-1.5 text-xs"
                                    >
                                        Ver
                                    </flux:button>
                                    @if ($venta->estado !== \App\Models\Venta::EstadoCerrada)
                                        <flux:button
                                            href="{{ route('ventas.edit', $venta) }}"
                                            wire:navigate
                                            class="px-3 py-1.5 text-xs"
                                        >
                                            Editar
                                        </flux:button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No hay ventas registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $this->ventas->links() }}
</div>
