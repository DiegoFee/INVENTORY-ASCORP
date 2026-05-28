<?php

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\Venta;
use App\Services\VentaService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public ?Venta $venta = null;
    public ?int $cliente_id = null;
    public string $estado = Venta::EstadoBorrador;
    public ?string $descuento = '0';
    public ?string $observaciones = null;
    public string $tipo_pago = 'contado'; // Campo para forma de pago
    public array $cart = [];
    public float $subtotal = 0;
    public float $total = 0;

    public function mount(?Venta $venta = null): void
    {
        $this->venta = $venta && $venta->exists ? $venta->load('detalles.producto') : null;

        if ($this->venta) {
            $this->cliente_id = $this->venta->cliente_id;
            $this->estado = $this->venta->estado ?: Venta::EstadoBorrador;
            $this->descuento = (string) ($this->venta->descuento ?: 0);
            $this->observaciones = $this->venta->observaciones;
            // Si guardas tipo_pago en BD, descomenta:
            // $this->tipo_pago = $this->venta->tipo_pago ?? 'contado';

            foreach ($this->venta->detalles as $detalle) {
                $this->cart[$detalle->producto_id] = [
                    'id' => $detalle->producto_id,
                    'name' => $detalle->producto?->nombre ?? '',
                    'price' => (float) $detalle->precio_unitario,
                    'qty' => $detalle->cantidad,
                ];
            }
        }

        $this->recalculateTotals();
    }

    public function getClientesProperty(): Collection
    {
        return Cliente::query()->orderBy('nombre')->get();
    }

    public function addProduct(int $productId): void
    {
        $producto = Producto::findOrFail($productId);

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['qty']++;
        } else {
            $this->cart[$productId] = [
                'id' => $producto->id,
                'name' => $producto->nombre,
                'price' => (float) $producto->precio_venta,
                'qty' => 1,
            ];
        }

        $this->recalculateTotals();
    }

    #[On('productSelected')]
    public function addProductFromSelector(int $productId): void
    {
        $this->addProduct($productId);
    }

    public function recalculateTotals(): void
    {
        $this->subtotal = 0;

        foreach ($this->cart as $item) {
            $this->subtotal += (float) ($item['price'] ?? 0) * max(0, (int) ($item['qty'] ?? 0));
        }

        $descuentoGlobal = max(0, (float) ($this->descuento ?? 0));

        $this->total = max(0, $this->subtotal - $descuentoGlobal);
    }

    public function updatedCart(): void
    {
        $this->recalculateTotals();
    }

    public function removeDetalle(int $productId): void
    {
        unset($this->cart[$productId]);

        $this->recalculateTotals();
    }

    public function save(VentaService $service): void
    {
        $this->recalculateTotals();

        $validated = $this->validate();
        $validated['user_id'] = Auth::id();

        $detalles = collect($this->cart)->map(function (array $item): array {
            return [
                'producto_id' => (int) ($item['id'] ?? 0),
                'cantidad' => max(1, (int) ($item['qty'] ?? 0)),
                'precio_unitario' => max(0, (float) ($item['price'] ?? 0)),
                'descuento' => 0,
            ];
        })->values()->toArray();

        $venta = $service->createVenta($validated, $detalles);

        $this->redirectRoute('ventas.show', $venta, navigate: true);
    }

    public function update(VentaService $service): void
    {
        $this->recalculateTotals();

        $validated = $this->validate();

        $detalles = collect($this->cart)->map(function (array $item): array {
            return [
                'producto_id' => (int) ($item['id'] ?? 0),
                'cantidad' => max(1, (int) ($item['qty'] ?? 0)),
                'precio_unitario' => max(0, (float) ($item['price'] ?? 0)),
                'descuento' => 0,
            ];
        })->values()->toArray();

        $venta = $service->updateVenta($this->venta, $validated, $detalles);

        $this->redirectRoute('ventas.show', $venta, navigate: true);
    }

    protected function rules(): array
    {
        return [
            'cliente_id' => ['nullable', 'integer', 'exists:clientes,id'],
            'estado' => ['required', 'string', Rule::in([Venta::EstadoBorrador, Venta::EstadoConfirmada])],
            'descuento' => ['nullable', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'tipo_pago' => ['required', 'string', Rule::in(['contado', 'credito'])],
            'cart' => ['required', 'array', 'min:1'],
            'cart.*.id' => ['required', 'integer', 'exists:productos,id'],
            'cart.*.qty' => ['required', 'integer', 'min:1'],
            'cart.*.price' => ['required', 'numeric', 'min:0'],
        ];
    }
};
?>

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

        <!-- Campo Forma de pago (nuevo) -->
        <div class="grid gap-2">
            <label class="text-sm font-medium text-zinc-800 dark:text-zinc-200" for="tipo_pago">Forma de pago</label>
            <select
                id="tipo_pago"
                wire:model="tipo_pago"
                class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
            >
                <option value="contado">Contado</option>
                <option value="credito">Crédito</option>
            </select>
            @error('tipo_pago') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
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

    <livewire:ventas.product-selector />

    <div class="space-y-3">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h3 class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">Carrito de venta</h3>
        </div>

        @error('cart') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror

        @if (count($cart) > 0)
            <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                        <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                            <tr>
                                <th class="px-4 py-3">Producto</th>
                                <th class="px-4 py-3">Cant.</th>
                                <th class="px-4 py-3">Precio</th>
                                <th class="px-4 py-3">Total</th>
                                <th class="px-4 py-3 text-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @foreach ($cart as $item)
                                <tr wire:key="cart-{{ $item['id'] }}">
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-zinc-800 dark:text-zinc-100">{{ $item['name'] ?: 'Producto #' . $item['id'] }}</span>
                                        @error('cart.'.$item['id'].'.id') <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-4 py-3">
                                        <input
                                            type="number"
                                            min="1"
                                            wire:model.live="cart.{{ $item['id'] }}.qty"
                                            class="w-20 rounded-md border border-zinc-300 bg-white px-2 py-1.5 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                                        >
                                        @error('cart.'.$item['id'].'.qty') <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-zinc-800 dark:text-zinc-100">
                                            Q{{ number_format(max(0, (float) ($item['price'] ?? 0)), 2) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm font-medium text-zinc-800 dark:text-zinc-100">
                                            Q{{ number_format(max(0, (float) ($item['price'] ?? 0) * max(0, (int) ($item['qty'] ?? 0))), 2) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <flux:button type="button" wire:click="removeDetalle({{ $item['id'] }})" class="px-2 py-1 text-xs text-red-700 dark:text-red-300">
                                            Quitar
                                        </flux:button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <p class="py-4 text-center text-sm text-zinc-400 dark:text-zinc-500">Selecciona productos desde el buscador de arriba</p>
        @endif
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