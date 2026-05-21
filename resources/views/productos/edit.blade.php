<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Editar producto</h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $producto->nombre }}</p>
            </div>

            <a href="{{ route('productos.show', $producto) }}" class="text-sm font-medium text-zinc-700 hover:text-zinc-950 dark:text-zinc-300 dark:hover:text-white">Ver detalle</a>
        </div>

        <livewire:productos.form :producto="$producto" />
    </div>
</x-layouts.app>
