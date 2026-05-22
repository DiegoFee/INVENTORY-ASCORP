<?php

use App\Models\Venta;
use App\Services\DevolucionService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;

new class extends Component {
    public ?int $venta_id = null;

    public ?string $motivo = null;

    public array $detalles = [];

    public ?Venta $ventaSeleccionada = null;

    public function getVentasProperty(): Collection
    {
        return Venta::query()
            ->whereIn('estado', [Venta::EstadoConfirmada, Venta::EstadoCerrada])
            ->orderByDesc('id')
            ->get();
    }

    public function getTotalProperty(): float
    {
        return (float) collect($this->detalles)
            ->filter(fn (array $d): bool => $d['selected'] && (int) $d['cantidad'] > 0)
            ->sum(fn (array $d): float => (int) $d['cantidad'] * (float) $d['precio_unitario']);
    }

    public function updatedVentaId(): void
    {
        $this->loadVentaDetalles();
    }

    public function loadVentaDetalles(): void
    {
        $this->detalles = [];
        $this->ventaSeleccionada = null;

        if ($this->venta_id === null) {
            return;
        }

        $venta = Venta::with('detalles.producto')->find((int) $this->venta_id);

        if ($venta === null) {
            return;
        }

        $this->ventaSeleccionada = $venta;

        $this->detalles = $venta->detalles->map(function ($detalle): array {
            return [
                'producto_id' => $detalle->producto_id,
                'nombre_producto' => $detalle->producto?->nombre ?? 'Producto eliminado',
                'cantidad_vendida' => $detalle->cantidad,
                'cantidad' => 0,
                'precio_unitario' => (float) $detalle->precio_unitario,
                'descuento' => 0,
                'selected' => false,
            ];
        })->toArray();
    }

    public function save(DevolucionService $service): void
    {
        $validated = $this->validate();

        $selectedDetalles = collect($this->detalles)
            ->filter(fn (array $detalle): bool => $detalle['selected'] && (int) $detalle['cantidad'] > 0)
            ->map(fn (array $detalle): array => [
                'producto_id' => $detalle['producto_id'],
                'cantidad' => (int) $detalle['cantidad'],
                'precio_unitario' => (float) $detalle['precio_unitario'],
                'descuento' => (float) ($detalle['descuento'] ?? 0),
            ])
            ->values()
            ->all();

        if (empty($selectedDetalles)) {
            $this->addError('detalles', 'Debes seleccionar al menos un producto con cantidad mayor a cero.');

            return;
        }

        foreach ($selectedDetalles as $det) {
            $original = collect($this->detalles)->firstWhere('producto_id', $det['producto_id']);

            if ($original && $det['cantidad'] > (int) $original['cantidad_vendida']) {
                $this->addError('detalles', "El producto {$original['nombre_producto']} excede las unidades vendidas ({$original['cantidad_vendida']}).");

                return;
            }
        }

        $validated['user_id'] = Auth::id();
        $validated['detalles'] = $selectedDetalles;

        $devolucion = $service->createDevolucion($validated, $validated['detalles']);

        $this->redirectRoute('devoluciones.show', $devolucion, navigate: true);
    }

    protected function rules(): array
    {
        return [
            'venta_id' => ['required', 'integer', 'exists:ventas,id'],
            'motivo' => ['nullable', 'string', 'max:1000'],
        ];
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-1">
            <flux:heading>Nueva devolución</flux:heading>
            <flux:subheading>Selecciona una venta y los productos a devolver.</flux:subheading>
        </div>
        <a href="{{ route('devoluciones.index') }}" class="text-sm font-medium text-zinc-600 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-white" wire:navigate>
            Volver
        </a>
    </div>
    <form wire:submit="save" class="max-w-4xl space-y-6">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-zinc-800 dark:text-zinc-200" for="venta_id">Venta</label>
                <select
                    id="venta_id"
                    wire:model="venta_id"
                    wire:change="loadVentaDetalles"
                    class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                >
                    <option value="">Seleccionar venta</option>
                    @foreach ($this->ventas as $venta)
                        <option value="{{ $venta->id }}">#{{ $venta->id }} — {{ $venta->cliente?->nombre ?? 'Consumidor final' }} ({{ ucfirst($venta->estado) }})</option>
                    @endforeach
                </select>
                @error('venta_id') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-2">
                <label class="text-sm font-medium text-zinc-800 dark:text-zinc-200" for="motivo">Motivo</label>
                <textarea
                    id="motivo"
                    rows="3"
                    wire:model="motivo"
                    class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                ></textarea>
                @error('motivo') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>
        </div>

        @if ($ventaSeleccionada)
            <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/50">
                <div class="flex flex-wrap items-center justify-between gap-2 text-sm">
                    <span class="font-semibold text-zinc-800 dark:text-zinc-100">
                        Venta #{{ $ventaSeleccionada->id }} — {{ $ventaSeleccionada->cliente?->nombre ?? 'Consumidor final' }}
                    </span>
                    <span class="text-zinc-600 dark:text-zinc-400">
                        Total venta: {{ number_format((float) $ventaSeleccionada->total, 2) }}
                    </span>
                </div>
            </div>

            @if (count($detalles) > 0)
                <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                            <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                                <tr>
                                    <th class="px-4 py-3">Devolver</th>
                                    <th class="px-4 py-3">Producto</th>
                                    <th class="px-4 py-3">Vendido</th>
                                    <th class="px-4 py-3">Unidades a devolver</th>
                                    <th class="px-4 py-3">Precio U.</th>
                                    <th class="px-4 py-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                                @foreach ($detalles as $index => $detalle)
                                    <tr class="text-zinc-800 dark:text-zinc-100 {{ $detalle['selected'] ? 'bg-zinc-50 dark:bg-zinc-800/30' : '' }}">
                                        <td class="px-4 py-3">
                                            <input
                                                type="checkbox"
                                                wire:model="detalles.{{ $index }}.selected"
                                                class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                                            >
                                        </td>
                                        <td class="px-4 py-3 font-medium">
                                            {{ $detalle['nombre_producto'] }}
                                        </td>
                                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                            {{ $detalle['cantidad_vendida'] }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <input
                                                type="number"
                                                min="0"
                                                max="{{ $detalle['cantidad_vendida'] }}"
                                                wire:model.live="detalles.{{ $index }}.cantidad"
                                                class="w-24 rounded-md border {{ (int) $detalle['cantidad'] > (int) $detalle['cantidad_vendida'] ? 'border-red-500' : 'border-zinc-300' }} bg-white px-2 py-1.5 text-sm text-zinc-900 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                                                {{ $detalle['selected'] ? '' : 'disabled' }}
                                            >
                                            @if ((int) $detalle['cantidad'] > (int) $detalle['cantidad_vendida'])
                                                <p class="mt-1 text-xs text-red-600">Máx. {{ $detalle['cantidad_vendida'] }}</p>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                            {{ number_format((float) $detalle['precio_unitario'], 2) }}
                                        </td>
                                        <td class="px-4 py-3 text-right text-zinc-600 dark:text-zinc-400">
                                            {{ number_format((int) $detalle['cantidad'] * (float) $detalle['precio_unitario'], 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="flex items-center justify-end rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="text-lg font-bold text-zinc-900 dark:text-white">
                        Total a devolver: {{ number_format($this->total, 2) }}
                    </div>
                </div>
            @endif

            @error('detalles') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        @endif

        <div class="flex items-center gap-3">
            <flux:button variant="primary" type="submit">Registrar devolución</flux:button>
            <a href="{{ route('devoluciones.index') }}" class="text-sm font-medium text-zinc-600 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-white" wire:navigate>Cancelar</a>
        </div>
    </form>
</div>
