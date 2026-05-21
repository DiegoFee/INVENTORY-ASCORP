<?php

use App\Models\Producto;
use App\Services\InventoryMovementService;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;

/**
 * // Autor: Diego Méndez - Fecha: 21/05/2026
 */
new class extends Component {
    public ?int $producto_id = null;
    public int $cantidad = 1;
    public ?float $costo_unitario = null;
    public ?string $observaciones = null;

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function getProductosProperty(): Collection
    {
        return Producto::query()->active()->orderBy('nombre')->get();
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function registerEntrada(InventoryMovementService $service): void
    {
        $validated = $this->validate();

        $service->registerEntrada([
            'producto_id' => $validated['producto_id'],
            'cantidad' => $validated['cantidad'],
            'costo_unitario' => $validated['costo_unitario'] ?? null,
            'observaciones' => $validated['observaciones'] ?? null,
            'user_id' => auth()->id(),
        ]);

        session()->flash('success', 'Entrada registrada correctamente.');

        $this->redirectRoute('inventario.index', navigate: true);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    protected function rules(): array
    {
        return [
            'producto_id' => ['required', 'integer', 'exists:productos,id'],
            'cantidad' => ['required', 'integer', 'min:1'],
            'costo_unitario' => ['nullable', 'numeric', 'min:0'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
        ];
    }
}; ?>

<form wire:submit="registerEntrada" class="max-w-3xl space-y-6">
    @if (session('success'))
        <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid gap-2">
        <label class="text-sm font-medium text-zinc-800 dark:text-zinc-200" for="producto_id">Producto</label>
        <select
            id="producto_id"
            wire:model="producto_id"
            class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
        >
            <option value="">Seleccionar producto</option>
            @foreach ($this->productos as $producto)
                <option value="{{ $producto->id }}">{{ $producto->nombre }}</option>
            @endforeach
        </select>
        @error('producto_id') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>

    <div class="grid gap-2">
        <label class="text-sm font-medium text-zinc-800 dark:text-zinc-200" for="cantidad">Cantidad</label>
        <input
            id="cantidad"
            type="number"
            min="1"
            wire:model="cantidad"
            class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
        >
        @error('cantidad') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>

    <div class="grid gap-2">
        <label class="text-sm font-medium text-zinc-800 dark:text-zinc-200" for="costo_unitario">Costo unitario</label>
        <input
            id="costo_unitario"
            type="number"
            min="0"
            step="0.01"
            wire:model="costo_unitario"
            class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
        >
        @error('costo_unitario') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>

    <div class="grid gap-2">
        <label class="text-sm font-medium text-zinc-800 dark:text-zinc-200" for="observaciones">Observaciones</label>
        <textarea
            id="observaciones"
            rows="3"
            wire:model="observaciones"
            class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
        ></textarea>
        @error('observaciones') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center gap-3">
        <flux:button variant="primary" type="submit">Registrar entrada</flux:button>
        <a href="{{ route('inventario.index') }}" class="text-sm font-medium text-zinc-600 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-white">Cancelar</a>
    </div>
</form>
