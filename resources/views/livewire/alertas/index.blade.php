<?php

use App\Models\Alerta;
use App\Services\AlertaService;
use Livewire\Volt\Component;

/**
 * // Autor: Diego Méndez - Fecha: 26/05/2026
 */
new class extends Component {
    /**
     * // Autor: Diego Méndez - Fecha: 26/05/2026
     */
    public function getAlertasProperty(): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return app(AlertaService::class)->getHistory(perPage: 20);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 26/05/2026
     */
    public function getPendientesProperty(): int
    {
        return app(AlertaService::class)->getUnreadCount();
    }

    /**
     * // Autor: Diego Méndez - Fecha: 26/05/2026
     */
    public function marcarLeida(int $alertaId): void
    {
        app(AlertaService::class)->markAsRead($alertaId);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 26/05/2026
     */
    public function marcarTodasLeidas(): void
    {
        app(AlertaService::class)->markAllAsRead();
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Historial de Alertas</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                Registro de alertas de stock bajo y productos criticos.
                @if ($this->pendientes > 0)
                    <span class="font-medium text-amber-600 dark:text-amber-400">{{ $this->pendientes }} pendiente(s)</span>
                @endif
            </p>
        </div>

        @if ($this->pendientes > 0)
            <button
                wire:click="marcarTodasLeidas"
                class="inline-flex items-center gap-2 rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
            >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                </svg>
                Marcar todas como leidas
            </button>
        @endif
    </div>

    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                    <tr>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3">Mensaje</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Accion</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->alertas as $alerta)
                        <tr wire:key="alerta-{{ $alerta->id }}" class="{{ $alerta->leida ? 'text-zinc-500 dark:text-zinc-400' : 'text-zinc-800 dark:text-zinc-100' }}">
                            <td class="px-4 py-3">
                                @if ($alerta->leida)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-3">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
                                        </svg>
                                        Leida
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-700 dark:bg-amber-950 dark:text-amber-200">
                                        <span class="size-1.5 rounded-full bg-amber-600 dark:bg-amber-400"></span>
                                        Pendiente
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $alerta->tipo === 'critico' ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-200' : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-200' }}">
                                    {{ ucfirst($alerta->tipo) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-medium">{{ $alerta->producto?->nombre ?? 'Producto eliminado' }}</td>
                            <td class="max-w-xs truncate px-4 py-3">{{ $alerta->mensaje }}</td>
                            <td class="whitespace-nowrap px-4 py-3">{{ $alerta->created_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3">
                                @if (! $alerta->leida)
                                    <button
                                        wire:click="marcarLeida({{ $alerta->id }})"
                                        class="rounded-md px-2 py-1 text-xs font-medium text-zinc-600 transition hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800"
                                    >
                                        Marcar leida
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No hay alertas registradas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($this->alertas->hasPages())
        <div class="mt-4">
            {{ $this->alertas->links() }}
        </div>
    @endif
</div>
