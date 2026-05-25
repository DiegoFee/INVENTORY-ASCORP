{{-- /** Autor: Celvin Arandi, Fecha: 25/05/2026, Descripcion: Vista de filtros para reporte de inventario. */ --}}
<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Reporte de inventario</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Selecciona producto o alerta de stock bajo.</p>
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <form method="GET" action="{{ route('reports.export', 'inventario') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Producto</label>
                    <select name="producto_id" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                        <option value="">Todos</option>
                        @foreach ($productos as $producto)
                            <option value="{{ $producto->id }}" {{ request('producto_id') == $producto->id ? 'selected' : '' }}>
                                {{ $producto->nombre }} ({{ $producto->sku }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-3 pt-6">
                    <input type="checkbox" id="stock_bajo" name="stock_bajo" value="1" class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600" {{ request('stock_bajo') ? 'checked' : '' }} />
                    <label for="stock_bajo" class="text-sm text-zinc-700 dark:text-zinc-300">Solo stock bajo</label>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-zinc-900">Descargar PDF</button>
                    <a href="{{ route('reports.inventario') }}" class="rounded-md border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-600">Limpiar</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
