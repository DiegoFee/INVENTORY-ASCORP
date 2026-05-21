<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Venta #{{ $venta->id }}</h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">
                    Cliente: {{ $venta->cliente?->nombre ?? 'Consumidor final' }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('ventas.index') }}"
                   class="rounded-md border border-zinc-300 px-3 py-2 text-sm font-medium hover:bg-zinc-50 dark:border-zinc-600 dark:hover:bg-zinc-800">
                    Volver
                </a>
                <a href="{{ route('ventas.edit', $venta) }}"
                   class="rounded-md bg-zinc-900 px-3 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">
                    Editar
                </a>
            </div>
        </div>
        <livewire:ventas.show :venta="$venta" />
    </div>
</x-layouts.app>
