<?php

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

new class extends Component {
    public ?Venta $venta = null;
    public ?int $cliente_id = null;
    public string $estado = Venta::EstadoBorrador;
    public ?string $descuento = '0';
    public ?string $observaciones = null;
    public array $detalles = [];

    public function mount(?Venta $venta = null): void
    {
        $this->venta = $venta && $venta->exists ? $venta->load('detalles') : null;

        if ($this->venta) {
            $this->cliente_id = $this->venta->cliente_id;
            $this->estado = $this->venta->estado ?: Venta::EstadoBorrador;
            $this->descuento = (string) ($this->venta->descuento ?: 0);
            $this->observaciones = $this->venta->observaciones;
            $this->detalles = $this->venta->detalles->map(function ($detalle): array {
                return [
                    'producto_id' => $detalle->producto_id,
                    'cantidad' => $detalle->cantidad,
                    'precio_unitario' => $detalle->precio_unitario,
                    'descuento' => $detalle->descuento,
                ];
            })->toArray();
        } else {
            $this->detalles = [$this->detalleVacio()];
        }
    }

    public function getClientesProperty(): Collection
    {
        return Cliente::query()->orderBy('nombre')->get();
    }

    public function getProductosProperty(): Collection
    {
        return Producto::query()->active()->orderBy('nombre')->get();
    }

    public function getTotalProperty(): float
    {
        $subtotal = collect($this->detalles)->sum(function (array $detalle): float {
            $cantidad = max(0, (int) ($detalle['cantidad'] ?? 0));
            $precio = max(0, (float) ($detalle['precio_unitario'] ?? 0));
            $desc = max(0, (float) ($detalle['descuento'] ?? 0));
            return $cantidad * $precio - $desc;
        });

        $descuentoGlobal = max(0, (float) ($this->descuento ?? 0));

        return max(0, $subtotal - $descuentoGlobal);
    }

    public function cargarPrecio(int $index): void
    {
        $productoId = $this->detalles[$index]['producto_id'] ?? null;

        if ($productoId === null) {
            return;
        }

        $producto = Producto::find((int) $productoId);

        if ($producto !== null) {
            $this->detalles[$index]['precio_unitario'] = (float) $producto->precio_venta;
        }
    }

    public function updatedDetalles(mixed $value, mixed $key): void
    {
        if (! is_string($key)) {
            return;
        }

        $parts = explode('.', $key);

        if (count($parts) < 2) {
            return;
        }

        [$index, $field] = $parts;

        if (in_array($field, ['cantidad', 'producto_id', 'descuento'], true)) {
            $this->subtotalLine((int) $index);
        }
    }

    public function subtotalLine(int $index): float
    {
        $detalle = $this->detalles[$index] ?? [];
        $cantidad = max(0, (int) ($detalle['cantidad'] ?? 0));
        $precio = max(0, (float) ($detalle['precio_unitario'] ?? 0));
        $desc = max(0, (float) ($detalle['descuento'] ?? 0));

        return max(0, $cantidad * $precio - $desc);
    }

    public function addDetalle(): void
    {
        $this->detalles[] = $this->detalleVacio();
    }

    public function removeDetalle(int $index): void
    {
        unset($this->detalles[$index]);
        $this->detalles = array_values($this->detalles);
    }

    public function save(VentaService $service): void
    {
        $validated = $this->validate();
        $validated['user_id'] = Auth::id();

        $venta = $service->createVenta($validated, $validated['detalles']);

        $this->redirectRoute('ventas.show', $venta, navigate: true);
    }

    public function update(VentaService $service): void
    {
        $validated = $this->validate();

        $venta = $service->updateVenta($this->venta, $validated, $validated['detalles']);

        $this->redirectRoute('ventas.show', $venta, navigate: true);
    }

    protected function rules(): array
    {
        return [
            'cliente_id' => ['nullable', 'integer', 'exists:clientes,id'],
            'estado' => ['required', 'string', Rule::in([Venta::EstadoBorrador, Venta::EstadoConfirmada])],
            'descuento' => ['nullable', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.producto_id' => ['required', 'integer', 'exists:productos,id'],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
            'detalles.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'detalles.*.descuento' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    private function detalleVacio(): array
    {
        return [
            'producto_id' => null,
            'cantidad' => 1,
            'precio_unitario' => 0,
            'descuento' => 0,
        ];
    }
}; ?>

<form wire:submit="{{ $venta && $venta->exists ? 'update' : 'save' }}" class="max-w-5xl space-y-6">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="grid gap-2">
            <label class="text-sm font-medium text-zinc-800 dark:text-zinc-200" for="cliente_id">Cliente</label>
            <select
                id="cliente_id"
                wire:model="cliente_id"
                class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
            >
                <option value="">Consumidor final</option>
                @foreach ($this->clientes as $cliente)
                    <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                @endforeach
            </select>
            @error('cliente_id') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-2">
            <label class="text-sm font-medium text-zinc-800 dark:text-zinc-200" for="estado">Estado</label>
            <select
                id="estado"
                wire:model="estado"
                class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
            >
                <option value="{{ \App\Models\Venta::EstadoBorrador }}">Borrador</option>
                <option value="{{ \App\Models\Venta::EstadoConfirmada }}">Confirmada</option>
            </select>
            @error('estado') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-2">
            <label class="text-sm font-medium text-zinc-800 dark:text-zinc-200" for="descuento">Descuento global</label>
            <input
                id="descuento"
                type="number"
                min="0"
                step="0.01"
                wire:model="descuento"
                class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
            >
            @error('descuento') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-2 md:col-span-2">
            <label class="text-sm font-medium text-zinc-800 dark:text-zinc-200" for="observaciones">Observaciones</label>
            <textarea
                id="observaciones"
                rows="3"
                wire:model="observaciones"
                class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
            ></textarea>
            @error('observaciones') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">Detalle de productos</h3>
            <flux:button type="button" wire:click="addDetalle" class="px-3 py-1.5 text-xs">
                Agregar linea
            </flux:button>
        </div>

        @error('detalles') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror

        <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                        <tr>
                            <th class="px-4 py-3">Producto</th>
                            <th class="px-4 py-3">Cantidad</th>
                            <th class="px-4 py-3">Precio U.</th>
                            <th class="px-4 py-3">Subtotal</th>
                            <th class="px-4 py-3">Dcto línea</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($detalles as $index => $detalle)
                            <tr wire:key="detalle-{{ $index }}">
                                <td class="px-4 py-3">
                                    <select
                                        wire:model="detalles.{{ $index }}.producto_id"
                                        wire:change="cargarPrecio({{ $index }})"
                                        class="w-full rounded-md border border-zinc-300 bg-white px-2 py-1.5 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                                    >
                                        <option value="">Seleccionar</option>
                                        @foreach ($this->productos as $producto)
                                            <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('detalles.'.$index.'.producto_id') <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-4 py-3">
                                    <input
                                        type="number"
                                        min="1"
                                        wire:model.live="detalles.{{ $index }}.cantidad"
                                        class="w-full rounded-md border border-zinc-300 bg-white px-2 py-1.5 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                                    >
                                    @error('detalles.'.$index.'.cantidad') <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm text-zinc-800 dark:text-zinc-100">
                                        {{ number_format(max(0, (float) ($detalle['precio_unitario'] ?? 0)), 2) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                                        {{ number_format($this->subtotalLine($index), 2) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        wire:model.live="detalles.{{ $index }}.descuento"
                                        class="w-full rounded-md border border-zinc-300 bg-white px-2 py-1.5 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                                    >
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <flux:button type="button" wire:click="removeDetalle({{ $index }})" class="px-2 py-1 text-xs text-red-700 dark:text-red-300">
                                        Quitar
                                    </flux:button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="text-sm text-zinc-600 dark:text-zinc-400">
            @if ((float)($descuento ?? 0) > 0)
                <span>Dcto global: -{{ number_format((float)$descuento, 2) }}</span>
            @endif
        </div>
        <div class="text-lg font-bold text-zinc-900 dark:text-white">
            Total: {{ number_format($this->total, 2) }}
        </div>
    </div>

    <div class="flex items-center gap-3">
        <flux:button variant="primary" type="submit">{{ $venta && $venta->exists ? 'Actualizar venta' : 'Guardar venta' }}</flux:button>
        <a href="{{ route('ventas.index') }}" class="text-sm font-medium text-zinc-600 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-white">Cancelar</a>
    </div>
</form>
