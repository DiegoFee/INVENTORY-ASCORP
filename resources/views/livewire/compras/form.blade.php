<?php

use App\Models\Compra;
use App\Models\Producto;
use App\Models\Supplier;
use App\Services\CompraService;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;

/**
 * // Autor: Diego Méndez - Fecha: 21/05/2026
 */
new class extends Component {
    public ?Compra $compra = null;
    public ?int $proveedor_id = null;
    public string $estado = Compra::EstadoBorrador;
    public ?string $fecha_compra = null;
    public ?string $observaciones = null;
    public array $detalles = [];

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function mount(?Compra $compra = null): void
    {
        $this->compra = $compra && $compra->exists ? $compra->load('detalles') : null;

        if ($this->compra) {
            $this->proveedor_id = $this->compra->proveedor_id;
            $this->estado = $this->compra->estado ?: Compra::EstadoBorrador;
            $this->fecha_compra = $this->compra->fecha_compra?->format('Y-m-d');
            $this->observaciones = $this->compra->observaciones;
            $this->detalles = $this->compra->detalles->map(function ($detalle): array {
                return [
                    'producto_id' => $detalle->producto_id,
                    'cantidad' => $detalle->cantidad,
                    'precio_costo' => $detalle->precio_costo,
                ];
            })->toArray();
        } else {
            $this->fecha_compra = now()->toDateString();
            $this->detalles = [$this->detalleVacio()];
        }
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function getProveedoresProperty(): Collection
    {
        return Supplier::query()->orderBy('nombre')->get();
    }

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
    public function addDetalle(): void
    {
        $this->detalles[] = $this->detalleVacio();
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function removeDetalle(int $index): void
    {
        unset($this->detalles[$index]);
        $this->detalles = array_values($this->detalles);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function save(CompraService $service): void
    {
        $validated = $this->validate();

        $compra = $service->createCompra($validated, $validated['detalles']);

        $this->redirectRoute('compras.show', $compra, navigate: true);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function update(CompraService $service): void
    {
        $validated = $this->validate();

        $compra = $service->updateCompra($this->compra, $validated, $validated['detalles']);

        $this->redirectRoute('compras.show', $compra, navigate: true);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    protected function rules(): array
    {
        return [
            'proveedor_id' => ['required', 'integer', 'exists:proveedores,id'],
            'estado' => ['required', 'string', Rule::in([Compra::EstadoBorrador, Compra::EstadoConfirmada])],
            'fecha_compra' => ['nullable', 'date'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.producto_id' => ['required', 'integer', 'exists:productos,id'],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
            'detalles.*.precio_costo' => ['required', 'numeric', 'min:0'],
        ];
    }

    private function detalleVacio(): array
    {
        return [
            'producto_id' => null,
            'cantidad' => 1,
            'precio_costo' => 0,
        ];
    }
}; ?>

<form wire:submit="{{ $compra && $compra->exists ? 'update' : 'save' }}" class="max-w-5xl space-y-6">
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="grid gap-2">
            <label class="text-sm font-medium text-zinc-800 dark:text-zinc-200" for="proveedor_id">Proveedor</label>
            <select
                id="proveedor_id"
                wire:model="proveedor_id"
                class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
            >
                <option value="">Seleccionar proveedor</option>
                @foreach ($this->proveedores as $proveedor)
                    <option value="{{ $proveedor->id }}">{{ $proveedor->nombre }}</option>
                @endforeach
            </select>
            @error('proveedor_id') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-2">
            <label class="text-sm font-medium text-zinc-800 dark:text-zinc-200" for="estado">Estado</label>
            <select
                id="estado"
                wire:model="estado"
                class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
            >
                <option value="{{ \App\Models\Compra::EstadoBorrador }}">Borrador</option>
                <option value="{{ \App\Models\Compra::EstadoConfirmada }}">Confirmada</option>
            </select>
            @error('estado') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-2">
            <label class="text-sm font-medium text-zinc-800 dark:text-zinc-200" for="fecha_compra">Fecha</label>
            <input
                id="fecha_compra"
                type="date"
                wire:model="fecha_compra"
                class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
            >
            @error('fecha_compra') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
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
                            <th class="px-4 py-3">Precio costo</th>
                            <th class="px-4 py-3 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                        @foreach ($detalles as $index => $detalle)
                            <tr wire:key="detalle-{{ $index }}">
                                <td class="px-4 py-3">
                                    <select
                                        wire:model="detalles.{{ $index }}.producto_id"
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
                                        wire:model="detalles.{{ $index }}.cantidad"
                                        class="w-full rounded-md border border-zinc-300 bg-white px-2 py-1.5 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                                    >
                                    @error('detalles.'.$index.'.cantidad') <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </td>
                                <td class="px-4 py-3">
                                    <input
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        wire:model="detalles.{{ $index }}.precio_costo"
                                        class="w-full rounded-md border border-zinc-300 bg-white px-2 py-1.5 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                                    >
                                    @error('detalles.'.$index.'.precio_costo') <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
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

    <div class="flex items-center gap-3">
        <flux:button variant="primary" type="submit">{{ $compra && $compra->exists ? 'Actualizar compra' : 'Guardar compra' }}</flux:button>
        <a href="{{ route('compras.index') }}" class="text-sm font-medium text-zinc-600 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-white">Cancelar</a>
    </div>
</form>
