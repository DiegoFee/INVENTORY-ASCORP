{{-- /** Autor: Celvin Arandi, Fecha: 25/05/2026, Descripcion: Vista de filtros para reporte de cuentas por cobrar. */ --}}
<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Reporte de cuentas por cobrar</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Filtra por cliente, estado y vencimiento.</p>
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <form method="GET" action="{{ route('reports.export', 'cxc') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Cliente</label>
                    <select name="cliente_id" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                        <option value="">Todos</option>
                        @foreach ($clientes as $cliente)
                            <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Estado</label>
                    <select name="estado" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                        <option value="">Todos</option>
                        <option value="pendiente" {{ request('estado') === 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="pagada" {{ request('estado') === 'pagada' ? 'selected' : '' }}>Pagada</option>
                    </select>
                </div>
                <div class="flex items-center gap-3 pt-6">
                    <input type="checkbox" id="vencidos" name="vencidos" value="1" class="h-4 w-4 rounded border-zinc-300 dark:border-zinc-600" {{ request('vencidos') ? 'checked' : '' }} />
                    <label for="vencidos" class="text-sm text-zinc-700 dark:text-zinc-300">Solo vencidos</label>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-zinc-900">Descargar PDF</button>
                    <a href="{{ route('reports.cxc') }}" class="rounded-md border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-600">Limpiar</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
