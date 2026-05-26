<?php

use App\Models\Alerta;
use App\Services\AlertaService;
use Livewire\Volt\Component;

/**
 * // Autor: Diego Méndez - Fecha: 26/05/2026
 */
new class extends Component {
    public bool $mostrar = false;

    /**
     * // Autor: Diego Méndez - Fecha: 26/05/2026
     */
    public function mount(): void
    {
        if (! session()->get('alertas_limpiadas', false)) {
            app(AlertaService::class)->cleanDuplicates();
            session()->put('alertas_limpiadas', true);
        }

        if (! session()->get('alertas_vistas', false)) {
            $this->mostrar = true;
            session()->put('alertas_vistas', true);
        }
    }

    /**
     * // Autor: Diego Méndez - Fecha: 26/05/2026
     */
    public function getAlertasProperty(): \Illuminate\Support\Collection
    {
        if (! $this->mostrar) {
            return collect();
        }

        return app(AlertaService::class)->getActiveAlerts();
    }

    /**
     * // Autor: Diego Méndez - Fecha: 26/05/2026
     */
    public function getTieneCriticasProperty(): bool
    {
        return $this->alertas->contains(fn (Alerta $a) => $a->tipo === Alerta::TipoCritico);
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

<div>
    @if ($this->alertas->isNotEmpty())
        <div
            wire:key="alertas-flotantes"
            class="pointer-events-none fixed inset-x-0 top-4 z-50 mx-auto flex max-w-md flex-col gap-2 px-4"
        >
            @foreach ($this->alertas as $alerta)
                <div
                    wire:key="alerta-{{ $alerta->id }}"
                    class="{{ $alerta->tipo === 'critico' ? 'border-red-400 bg-red-50 dark:border-red-800 dark:bg-red-950' : 'border-amber-400 bg-amber-50 dark:border-amber-800 dark:bg-amber-950' }} pointer-events-auto flex items-start gap-3 rounded-lg border p-4 shadow-lg"
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition.duration.300ms
                >
                    <div class="mt-0.5 shrink-0">
                        @if ($alerta->tipo === 'critico')
                            <span class="flex size-5 items-center justify-center text-red-600 dark:text-red-400">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 1 1-16 0 8 8 0 0 1 16 0Zm-7-4a1 1 0 1 0-2 0v4a1 1 0 1 0 2 0V6Zm-1 7a1 1 0 1 0 0 2h.01a1 1 0 1 0 0-2H10Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        @else
                            <span class="flex size-5 items-center justify-center text-amber-600 dark:text-amber-400">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-5">
                                    <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 6a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 6Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
                                </svg>
                            </span>
                        @endif
                    </div>

                    <div class="flex-1 text-sm">
                        <p class="{{ $alerta->tipo === 'critico' ? 'text-red-900 dark:text-red-200' : 'text-amber-900 dark:text-amber-200' }} font-medium">{{ $alerta->mensaje }}</p>
                        <p class="{{ $alerta->tipo === 'critico' ? 'text-red-700 dark:text-red-300' : 'text-amber-700 dark:text-amber-300' }} mt-1 text-xs opacity-75">{{ $alerta->created_at->diffForHumans() }}</p>
                    </div>

                    <button
                        wire:click="marcarLeida({{ $alerta->id }})"
                        class="{{ $alerta->tipo === 'critico' ? 'text-red-600 hover:bg-red-100 dark:text-red-400 dark:hover:bg-red-900/50' : 'text-amber-600 hover:bg-amber-100 dark:text-amber-400 dark:hover:bg-amber-900/50' }} shrink-0 rounded-md p-1 transition"
                        aria-label="Cerrar alerta"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-4">
                            <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                        </svg>
                    </button>
                </div>
            @endforeach

            <div class="pointer-events-auto flex justify-center">
                <button
                    wire:click="marcarTodasLeidas"
                    class="rounded-md bg-zinc-800 px-3 py-1.5 text-xs font-medium text-white transition hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
                >
                    Cerrar todas
                </button>
            </div>
        </div>
    @endif
</div>
