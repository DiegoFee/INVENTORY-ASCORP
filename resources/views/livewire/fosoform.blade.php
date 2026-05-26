<?php

use App\Models\Cliente;
use App\Models\Producto;
use App\Models\ServicioFoso;
use App\Services\InventoryMovementService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    public ?ServicioFoso $servicio = null;
    public ?int $cliente_id = null;
    public string $placa_vehiculo = '';
    public string $fecha;
    public ?string $observaciones = null;
    public array $cart = [];
    public float $subtotal = 0;
    public float $total = 0;

    public function mount(?ServicioFoso $servicio = null): void
    {
        $this->servicio = $servicio && $servicio->exists ? $servicio->load('detalles.producto') : null;
        $this->fecha = now()->toDateString();

        if ($this->servicio) {
            $this->cliente_id = $this->servicio->cliente_id;
            $this->placa_vehiculo = $this->servicio->placa_vehiculo;
            $this->fecha = $this->servicio->fecha?->format('Y-m-d') ?? now()->toDateString();
            $this->observaciones = $this->servicio->observaciones;

            foreach ($this->servicio->detalles as $detalle) {
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
        $this->total = max(0, $this->subtotal);
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

    public function save(InventoryMovementService $service): void
    {
        $this->recalculateTotals();

        $validated = $this->validate([
            'cliente_id' => 'required|integer|exists:clientes,id',
            'placa_vehiculo' => 'required|string|max:20',
            'fecha' => 'required|date',
            'observaciones' => 'nullable|string|max:1000',
            'cart' => 'required|array|min:1',
            'cart.*.id' => 'required|integer|exists:productos,id',
            'cart.*.qty' => 'required|integer|min:1',
            'cart.*.price' => 'required|numeric|min:0',
        ]);

        $servicio = ServicioFoso::create([
            'cliente_id' => $validated['cliente_id'],
            'user_id' => Auth::id(),
            'placa_vehiculo' => $validated['placa_vehiculo'],
            'fecha' => $validated['fecha'],
            'observaciones' => $validated['observaciones'],
            'total' => $this->total,
            'estado' => 'abierto',
        ]);

        foreach ($this->cart as $item) {
            $subtotal = (float) $item['price'] * (int) $item['qty'];
            $servicio->detalles()->create([
                'producto_id' => $item['id'],
                'cantidad' => $item['qty'],
                'precio_unitario' => $item['price'],
                'subtotal' => $subtotal,
            ]);
        }

        $this->redirectRoute('foso.show', $servicio);
    }
};
?>

<div>
    <form wire:submit="save" class="max-w-5xl space-y-6">
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
            <div class="grid gap-2">
                <label class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Cliente</label>
                <select wire:model="cliente_id" class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                    <option value="">Seleccione un cliente</option>
                    @foreach ($this->clientes as $cliente)
                        <option value="{{ $cliente->id }}">{{ $cliente->nombre }}</option>
                    @endforeach
                </select>
                @error('cliente_id') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-2">
                <label class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Placa del vehículo</label>
                <input type="text" wire:model="placa_vehiculo" class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                @error('placa_vehiculo') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-2">
                <label class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Fecha del servicio</label>
                <input type="date" wire:model="fecha" class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                @error('fecha') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-2 md:col-span-2">
                <label class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Observaciones</label>
                <textarea rows="2" wire:model="observaciones" class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"></textarea>
                @error('observaciones') <p class="text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <livewire:ventas.product-selector />

        <div class="space-y-3">
            <h3 class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">Insumos utilizados</h3>
            @error('cart') <p class="text-sm text-red-600">{{ $message }}</p> @enderror

            @if(count($cart) > 0)
                <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                    <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                        <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase dark:bg-zinc-800">
                            <tr><th class="px-4 py-3">Producto</th><th class="px-4 py-3">Cant.</th><th class="px-4 py-3">Precio</th><th class="px-4 py-3">Total</th><th class="px-4 py-3 text-right">Acciones</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($cart as $item)
                                <tr>
                                    <td class="px-4 py-3">{{ $item['name'] }}</td>
                                    <td class="px-4 py-3"><input type="number" min="1" wire:model.live="cart.{{ $item['id'] }}.qty" class="w-20 rounded-md border px-2 py-1"></td>
                                    <td class="px-4 py-3">Q{{ number_format($item['price'], 2) }}</td>
                                    <td class="px-4 py-3">Q{{ number_format($item['price'] * $item['qty'], 2) }}</td>
                                    <td class="px-4 py-3 text-right"><button type="button" wire:click="removeDetalle({{ $item['id'] }})" class="text-red-600">Quitar</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="flex justify-end">
                    <p class="text-lg font-bold">Total: Q{{ number_format($this->total, 2) }}</p>
                </div>
            @else
                <p class="py-4 text-center text-sm text-zinc-400">No hay insumos agregados. Busca productos arriba.</p>
            @endif
        </div>

        <div class="flex gap-3">
            <button type="submit" class="rounded-md bg-zinc-900 px-4 py-2 text-white hover:bg-zinc-700">Guardar Servicio</button>
            <a href="{{ route('foso.index') }}" class="rounded-md border px-4 py-2">Cancelar</a>
        </div>
    </form>
</div>