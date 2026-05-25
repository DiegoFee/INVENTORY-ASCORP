<?php

/** Autor: Arandi Hurtado, Fecha: 22/05/2026, Descripción: Vista de detalle de venta interactiva con acciones PDF y cierre transaccional. */

use App\Models\Venta;
use App\Services\VentaService;
use Livewire\Volt\Component;

new class extends Component {
    public Venta $venta;

    public function mount(Venta $venta): void
    {
        $this->venta = $venta->load(['usuario', 'cliente', 'caja', 'detalles.producto']);
    }

    /**
     * Funcionamiento: Cierra la venta de forma segura resolviendo el servicio desde el contenedor de Laravel.
     * Tablas: ventas, detalles_venta, movimientos_inventario, cuenta_por_cobrar.
     * Flujo: Resuelve la dependencia, ejecuta lógica de negocio, actualiza estado en UI y despacha alerta.
     */
    public function close(): void
    {
        // Resolución segura del servicio para evitar fallos de inyección en Livewire Volt
        $service = app(VentaService::class);
        
        $this->venta = $service->closeVenta($this->venta);

        session()->flash('success', 'Venta cerrada y stock descontado correctamente.');
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
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Venta #{{ $venta->id }}</h2>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
                Cliente: {{ $venta->cliente?->nombre ?? 'Consumidor final' }}
            </p>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if ($venta->estado !== \App\Models\Venta::EstadoBorrador)
                <flux:button
                    href="{{ route('ventas.factura', $venta) }}"
                    class="px-3 py-1.5 text-xs"
                    target="_blank"
                >
                    Factura PDF
                </flux:button>
                <flux:button
                    href="{{ route('ventas.comprobante', $venta) }}"
                    class="px-3 py-1.5 text-xs"
                    target="_blank"
                >
                    Comprobante PDF
                </flux:button>
            @endif

            @if ($venta->estado === \App\Models\Venta::EstadoConfirmada)
                <flux:button type="button" wire:click="close" class="px-3 py-1.5 text-xs">
                    Cerrar venta
                </flux:button>
            @endif

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
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Estado</dt>
            <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ ucfirst($venta->estado) }}</dd>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Caja</dt>
            <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">#{{ $venta->caja_id }}</dd>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Total</dt>
            <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ number_format((float) $venta->total, 2) }}</dd>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Descuento</dt>
            <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ number_format((float) ($venta->descuento ?? 0), 2) }}</dd>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Apertura</dt>
            <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ $venta->opened_at?->format('d/m/Y H:i') ?? 'Sin registro' }}</dd>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
            <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Cierre</dt>
            <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ $venta->closed_at?->format('d/m/Y H:i') ?? 'Pendiente' }}</dd>
        </div>
        <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700 sm:col-span-2">
            <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Observaciones</dt>
            <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ $venta->observaciones ?? 'Sin observaciones' }}</dd>
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
                        <th class="px-4 py-3">Dcto</th>
                        <th class="px-4 py-3">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($venta->detalles as $detalle)
                        <tr class="text-zinc-800 dark:text-zinc-100">
                            <td class="px-4 py-3 font-medium">{{ $detalle->producto?->nombre ?? 'Producto eliminado' }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $detalle->cantidad }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ number_format((float) $detalle->precio_unitario, 2) }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ number_format((float) ($detalle->descuento ?? 0), 2) }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ number_format((float) $detalle->subtotal, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">No hay detalles registrados.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
