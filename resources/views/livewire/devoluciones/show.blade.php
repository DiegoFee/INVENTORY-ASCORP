<?php

use App\Models\Devolucion;
use App\Services\DevolucionService;
use Livewire\Volt\Component;

new class extends Component {
    public Devolucion $devolucion;

    public function mount(Devolucion $devolucion): void
    {
        $this->devolucion = $devolucion->load(['venta', 'usuario', 'detalles.producto']);
    }

    public function approve(DevolucionService $service): void
    {
        $this->devolucion = $service->procesarDevolucion($this->devolucion);

        session()->flash('success', 'Devolución procesada y stock actualizado.');
    }

    public function reject(DevolucionService $service): void
    {
        $this->devolucion = $service->rejectDevolucion($this->devolucion);

        session()->flash('success', 'Devolución rechazada.');
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
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Devolución #{{ $devolucion->id }}</h2>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                Venta asociada: #{{ $devolucion->venta_id }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if ($devolucion->estado === \App\Models\Devolucion::EstadoPendiente)
                <flux:button type="button" wire:click="approve" class="px-3 py-1.5 text-xs">
                    Aprobar devolución
                </flux:button>
                <flux:button type="button" wire:click="reject" class="px-3 py-1.5 text-xs text-red-700 dark:text-red-300">
                    Rechazar devolución
                </flux:button>
            @endif
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Estado</dt>
            <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ ucfirst($devolucion->estado) }}</dd>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Monto</dt>
            <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ number_format((float) $devolucion->monto, 2) }}</dd>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Usuario</dt>
            <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ $devolucion->usuario?->name ?? 'N/A' }}</dd>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Fecha</dt>
            <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ $devolucion->created_at?->format('d/m/Y H:i') ?? 'Sin fecha' }}</dd>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700 sm:col-span-2">
            <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Motivo</dt>
            <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ $devolucion->motivo ?? 'Sin motivo especificado' }}</dd>
        </div>
    </div>

    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                    <tr>
                        <th class="px-4 py-3">Producto</th>
                        <th class="px-4 py-3">Cantidad</th>
                        <th class="px-4 py-3">Precio unitario</th>
                        <th class="px-4 py-3">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($devolucion->detalles as $detalle)
                        <tr class="text-zinc-800 dark:text-zinc-100">
                            <td class="px-4 py-3 font-medium">{{ $detalle->producto?->nombre ?? 'Producto eliminado' }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $detalle->cantidad }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ number_format((float) $detalle->precio_unitario, 2) }}</td>
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
