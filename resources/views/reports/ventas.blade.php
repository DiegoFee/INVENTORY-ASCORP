{{-- /** Autor: Celvin Arandi, Fecha: 25/05/2026, Descripcion: Vista de filtros para reporte de ventas. */ --}}
<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Reporte de ventas</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Filtra por fechas, cliente y tipo de pago.</p>
        </div>

        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <form method="GET" action="{{ route('reports.export', 'ventas') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Fecha inicio</label>
                    <input type="date" name="fecha_inicio" value="{{ request('fecha_inicio') }}" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800" />
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Fecha fin</label>
                    <input type="date" name="fecha_fin" value="{{ request('fecha_fin') }}" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800" />
                </div>
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
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Tipo pago</label>
                    <select name="tipo_pago" class="mt-1 w-full rounded-md border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800">
                        <option value="">Todos</option>
                        <option value="contado" {{ request('tipo_pago') === 'contado' ? 'selected' : '' }}>Efectivo</option>
                        <option value="credito" {{ request('tipo_pago') === 'credito' ? 'selected' : '' }}>Credito</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white dark:bg-white dark:text-zinc-900">Descargar PDF</button>
                    <a href="{{ route('reports.ventas') }}" class="rounded-md border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-600">Limpiar</a>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
