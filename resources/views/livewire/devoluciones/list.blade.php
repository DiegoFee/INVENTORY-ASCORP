<?php

use App\Models\Devolucion;
use App\Repositories\DevolucionRepositoryInterface;
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

    public function getDevolucionesProperty(): LengthAwarePaginator
    {
        return app(DevolucionRepositoryInterface::class)
            ->paginateForList($this->search, auth()->user());
    }
}; ?>

<div class="space-y-5">
    <div class="max-w-xl">
        <flux:input
            wire:model.debounce.300ms="search"
            label="Buscar"
            name="search"
            placeholder="Buscar por id"
        />
    </div>

    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">Venta</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Monto</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->devoluciones as $devolucion)
                        <tr class="text-zinc-800 dark:text-zinc-100">
                            <td class="px-4 py-3 font-medium">{{ $devolucion->id }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">#{{ $devolucion->venta_id }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $devolucion->estado === \App\Models\Devolucion::EstadoProcesada ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-200' : ($devolucion->estado === \App\Models\Devolucion::EstadoRechazada ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-200' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-950 dark:text-yellow-200') }}">
                                    {{ ucfirst($devolucion->estado) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ number_format((float) $devolucion->monto, 2) }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $devolucion->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <flux:button
                                        href="{{ route('devoluciones.show', $devolucion) }}"
                                        wire:navigate
                                        class="px-3 py-1.5 text-xs"
                                    >
                                        Ver
                                    </flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No hay devoluciones registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $this->devoluciones->links() }}
</div>
