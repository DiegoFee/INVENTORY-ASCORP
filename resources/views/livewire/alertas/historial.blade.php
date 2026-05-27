<?php

use App\Models\Alerta;
use App\Services\AlertaService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Livewire\Volt\Component;
use Livewire\WithPagination;

/**
 * // Autor: Diego Méndez - Fecha: 27/05/2026
 */
new class extends Component {
    use WithPagination;

    public string $filtroTipo = '';

    public string $filtroLeida = '';

    /**
     * // Autor: Diego Méndez - Fecha: 27/05/2026
     */
    public function updatingFiltroTipo(): void
    {
        $this->resetPage();
    }

    /**
     * // Autor: Diego Méndez - Fecha: 27/05/2026
     */
    public function updatingFiltroLeida(): void
    {
        $this->resetPage();
    }

    /**
     * // Autor: Diego Méndez - Fecha: 27/05/2026
     */
    public function getAlertasProperty(): LengthAwarePaginator
    {
        $tipo = $this->filtroTipo !== '' ? $this->filtroTipo : null;
        $leida = $this->filtroLeida !== '' ? (bool) $this->filtroLeida : null;

        return app(AlertaService::class)->obtenerTodas($tipo, $leida);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 27/05/2026
     */
    public function marcarLeida(int $alertaId): void
    {
        $alerta = Alerta::query()->find($alertaId);

        if ($alerta) {
            app(AlertaService::class)->marcarComoLeida($alerta);
        }
    }

    /**
     * // Autor: Diego Méndez - Fecha: 27/05/2026
     */
    public function marcarTodas(): void
    {
        app(AlertaService::class)->marcarTodasComoLeidas();
    }
}; ?>

<div class="space-y-5">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Historial de Alertas</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Todas las alertas generadas en el sistema.</p>
        </div>
        <flux:button type="button" wire:click="marcarTodas" class="px-3 py-1.5 text-xs">
            Marcar todas como leidas
        </flux:button>
    </div>

    <div class="flex flex-wrap gap-3">
        <div class="w-full sm:w-48">
            <flux:select wire:model.live="filtroTipo" placeholder="Todos los tipos">
                <flux:select.option value="stock_bajo">Stock bajo</flux:select.option>
                <flux:select.option value="critico">Critico</flux:select.option>
            </flux:select>
        </div>
        <div class="w-full sm:w-48">
            <flux:select wire:model.live="filtroLeida" placeholder="Todos los estados">
                <flux:select.option value="0">No leidas</flux:select.option>
                <flux:select.option value="1">Leidas</flux:select.option>
            </flux:select>
        </div>
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
                        <th class="px-4 py-3">Tipo</th>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3">Mensaje</th>
                        <th class="px-4 py-3">Fecha</th>
                        <th class="px-4 py-3">Estado</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->alertas as $alerta)
                        <tr class="text-zinc-800 dark:text-zinc-100">
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold {{ $alerta->tipo === 'critico' ? 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-200' : 'bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-200' }}">
                                    {{ $alerta->tipo === 'critico' ? 'Critico' : 'Stock bajo' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-medium">
                                {{ $alerta->producto?->nombre ?? '—' }}
                                <span class="text-xs text-zinc-500 dark:text-zinc-400">
                                    ({{ $alerta->producto?->sku ?? '—' }})
                                </span>
                            </td>
                            <td class="max-w-xs truncate px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                {{ $alerta->mensaje }}
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                {{ $alerta->created_at->format('d/m/Y H:i') }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($alerta->leida)
                                    <span class="inline-flex rounded-full bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-950 dark:text-emerald-200">
                                        Leida
                                    </span>
                                @else
                                    <span class="inline-flex rounded-full bg-amber-100 px-2 py-1 text-xs font-medium text-amber-700 dark:bg-amber-950 dark:text-amber-200">
                                        Pendiente
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right">
                                @if (! $alerta->leida)
                                    <flux:button
                                        type="button"
                                        wire:click="marcarLeida({{ $alerta->id }})"
                                        class="px-3 py-1.5 text-xs"
                                    >
                                        Marcar leida
                                    </flux:button>
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

    {{ $this->alertas->links() }}
</div>
