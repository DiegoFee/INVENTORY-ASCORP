<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">{{ $producto->nombre }}</h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">SKU: {{ $producto->sku }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('productos.index') }}" class="rounded-md border border-zinc-300 px-3 py-2 text-sm font-medium hover:bg-zinc-50 dark:border-zinc-600 dark:hover:bg-zinc-800">Volver</a>
                <a href="{{ route('productos.edit', $producto) }}" class="rounded-md bg-zinc-900 px-3 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">Editar</a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        <dl class="grid max-w-4xl grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Categoria</dt>
                <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ $producto->categoria }}</dd>
            </div>

            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Unidad</dt>
                <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ $producto->unidad_medida }}</dd>
            </div>

            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Stock actual</dt>
                <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ $producto->stock_actual }}</dd>
            </div>

            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Stock minimo</dt>
                <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ $producto->stock_minimo }}</dd>
            </div>

            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Precio costo</dt>
                <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ number_format((float) $producto->precio_costo, 2) }}</dd>
            </div>

            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Precio venta</dt>
                <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ number_format((float) $producto->precio_venta, 2) }}</dd>
            </div>

            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Estado</dt>
                <dd class="mt-2">
                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $producto->activo ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-200' : 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-200' }}">
                        {{ $producto->activo ? 'Activo' : 'Inactivo' }}
                    </span>
                </dd>
            </div>

            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Actualizado</dt>
                <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ $producto->updated_at?->format('d/m/Y H:i') }}</dd>
            </div>
        </dl>
    </div>
</x-layouts.app>
