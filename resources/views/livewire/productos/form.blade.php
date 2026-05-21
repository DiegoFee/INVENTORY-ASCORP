<?php

use App\Models\Producto;
use Livewire\Volt\Component;

/**
 * // Autor: Diego Méndez - Fecha: 20/05/2026
 */
new class extends Component {
    public ?Producto $producto = null;

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function mount(?Producto $producto = null): void
    {
        $this->producto = $producto;
    }
}; ?>

<form
    method="POST"
    action="{{ $producto ? route('productos.update', $producto) : route('productos.store') }}"
    class="max-w-4xl space-y-6"
>
    @csrf
    @if ($producto)
        @method('PUT')
    @endif

    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
        <div class="grid gap-2">
            <label for="sku" class="text-sm font-medium text-zinc-800 dark:text-zinc-200">SKU</label>
            <input
                id="sku"
                name="sku"
                value="{{ old('sku', $producto?->sku) }}"
                required
                class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
            >
            @error('sku') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-2">
            <label for="nombre" class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Nombre</label>
            <input
                id="nombre"
                name="nombre"
                value="{{ old('nombre', $producto?->nombre) }}"
                required
                class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
            >
            @error('nombre') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-2">
            <label for="categoria" class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Categoria</label>
            <input
                id="categoria"
                name="categoria"
                value="{{ old('categoria', $producto?->categoria) }}"
                required
                class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
            >
            @error('categoria') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-2">
            <label for="unidad_medida" class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Unidad de medida</label>
            <input
                id="unidad_medida"
                name="unidad_medida"
                value="{{ old('unidad_medida', $producto?->unidad_medida) }}"
                required
                class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
            >
            @error('unidad_medida') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-2">
            <label for="stock_actual" class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Stock actual</label>
            <input
                id="stock_actual"
                name="stock_actual"
                type="number"
                min="0"
                value="{{ old('stock_actual', $producto?->stock_actual ?? 0) }}"
                required
                class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
            >
            @error('stock_actual') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-2">
            <label for="stock_minimo" class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Stock minimo</label>
            <input
                id="stock_minimo"
                name="stock_minimo"
                type="number"
                min="0"
                value="{{ old('stock_minimo', $producto?->stock_minimo ?? 0) }}"
                required
                class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
            >
            @error('stock_minimo') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-2">
            <label for="precio_costo" class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Precio costo</label>
            <input
                id="precio_costo"
                name="precio_costo"
                type="number"
                min="0"
                step="0.01"
                value="{{ old('precio_costo', $producto?->precio_costo ?? 0) }}"
                required
                class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
            >
            @error('precio_costo') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-2">
            <label for="precio_venta" class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Precio venta</label>
            <input
                id="precio_venta"
                name="precio_venta"
                type="number"
                min="0"
                step="0.01"
                value="{{ old('precio_venta', $producto?->precio_venta ?? 0) }}"
                required
                class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
            >
            @error('precio_venta') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>
    </div>

    <div class="flex items-center gap-3">
        <input type="hidden" name="activo" value="0">
        <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
            <input
                type="checkbox"
                name="activo"
                value="1"
                @checked(old('activo', $producto?->activo ?? true))
                class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500"
            >
            Activo
        </label>
    </div>

    <div class="flex items-center gap-3">
        <button
            type="submit"
            class="rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200"
        >
            {{ $producto ? 'Actualizar' : 'Guardar' }}
        </button>
        <a
            href="{{ route('productos.index') }}"
            class="text-sm font-medium text-zinc-600 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-white"
        >
            Cancelar
        </a>
    </div>
</form>
