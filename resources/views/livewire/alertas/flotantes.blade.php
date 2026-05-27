<?php

use App\Models\Alerta;
use App\Services\AlertaService;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;

/**
 * // Autor: Diego Méndez - Fecha: 27/05/2026
 */
new class extends Component {
    public bool $mostrar = true;

    public Collection $alertas;

    /**
     * // Autor: Diego Méndez - Fecha: 27/05/2026
     */
    public function mount(AlertaService $alertaService): void
    {
        if (! session()->has('alertas_initialized')) {
            $alertaService->generarAlertasStock();
            session()->put('alertas_initialized', true);
        }

        $this->alertas = $alertaService->obtenerActivas();

        if ($this->alertas->isEmpty() || session()->has('alertas_shown')) {
            $this->mostrar = false;
        }
    }

    /**
     * // Autor: Diego Méndez - Fecha: 27/05/2026
     */
    public function cerrar(AlertaService $alertaService, int $alertaId): void
    {
        $alerta = Alerta::query()->find($alertaId);

        if ($alerta) {
            $alertaService->marcarComoLeida($alerta);
            $this->alertas = $this->alertas->reject(fn (Alerta $a): bool => $a->id === $alertaId);
        }

        session()->put('alertas_shown', true);

        if ($this->alertas->isEmpty()) {
            $this->mostrar = false;
        }
    }

    /**
     * // Autor: Diego Méndez - Fecha: 27/05/2026
     */
    public function cerrarTodas(AlertaService $alertaService): void
    {
        $alertaService->marcarTodasComoLeidas();
        $this->alertas = collect();
        $this->mostrar = false;
        session()->put('alertas_shown', true);
    }
}; ?>

<div class="fixed bottom-4 right-4 z-50">
    @if ($mostrar && $this->alertas->isNotEmpty())
        <div
            class="flex w-96 flex-col gap-2"
            x-data="{ visible: true }"
            x-show="visible"
            x-transition:enter="transform transition ease-out duration-300"
            x-transition:enter-start="translate-y-4 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transform transition ease-in duration-200"
            x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="translate-y-4 opacity-0"
        >
            <div class="flex items-center justify-between rounded-t-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white dark:bg-amber-600">
                <span>Alertas de inventario</span>
                <button
                    type="button"
                    wire:click="cerrarTodas"
                    class="text-xs text-white/80 underline underline-offset-2 hover:text-white"
                >
                    Cerrar todas
                </button>
            </div>

            <div class="flex flex-col gap-2">
                @foreach ($this->alertas as $alerta)
                    <div
                        class="flex items-start gap-3 rounded-lg border border-zinc-200 bg-white p-4 shadow-lg dark:border-zinc-700 dark:bg-zinc-800"
                    >
                        <span class="mt-0.5 shrink-0 text-lg">
                            @if ($alerta->tipo === 'critico')
                                🚫
                            @else
                                ⚠️
                            @endif
                        </span>
                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
                                {{ $alerta->tipo === 'critico' ? 'Critico' : 'Stock bajo' }}
                            </p>
                            <p class="text-sm text-zinc-800 dark:text-zinc-200">
                                {{ $alerta->mensaje }}
                            </p>
                        </div>
                        <button
                            type="button"
                            wire:click="cerrar({{ $alerta->id }})"
                            class="shrink-0 rounded-full p-1 text-zinc-400 hover:bg-zinc-100 hover:text-zinc-600 dark:hover:bg-zinc-700 dark:hover:text-zinc-300"
                        >
                            <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M18 6 6 18" />
                                <path d="m6 6 12 12" />
                            </svg>
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
