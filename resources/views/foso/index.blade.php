<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Servicios de Foso</h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Registro de servicios, insumos y cierre de foso.</p>
            </div>
            <a href="{{ route('foso.create') }}" class="rounded-md bg-zinc-900 px-4 py-2 text-white hover:bg-zinc-700">Nuevo servicio</a>
        </div>

        <!-- Filtros -->
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label class="text-sm font-medium">Cliente</label>
                    <select name="cliente_id" class="w-full rounded-md border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800">
                        <option value="">Todos</option>
                        @foreach(\App\Models\Cliente::orderBy('nombre')->get() as $cliente)
                            <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>{{ $cliente->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Estado</label>
                    <select name="estado" class="w-full rounded-md border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800">
                        <option value="">Todos</option>
                        <option value="abierto" {{ request('estado') == 'abierto' ? 'selected' : '' }}>Abierto</option>
                        <option value="cerrado" {{ request('estado') == 'cerrado' ? 'selected' : '' }}>Cerrado</option>
                    </select>
                </div>
                <div>
                    <label class="text-sm font-medium">Placa</label>
                    <input type="text" name="placa" value="{{ request('placa') }}" class="w-full rounded-md border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-md bg-zinc-900 px-4 py-2 text-white">Filtrar</button>
                    @if(request()->anyFilled(['cliente_id','estado','placa']))
                        <a href="{{ route('foso.index') }}" class="rounded-md border px-4 py-2">Limpiar</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabla -->
        <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr><th class="px-5 py-3 text-left">ID</th><th>Cliente</th><th>Placa</th><th>Fecha</th><th>Total</th><th>Estado</th><th class="text-right">Acciones</th></tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                    @forelse($servicios as $servicio)
                        <tr>
                            <td class="px-5 py-4">{{ $servicio->id }}</td>
                            <td class="px-5 py-4">{{ $servicio->cliente->nombre }}</td>
                            <td class="px-5 py-4">{{ $servicio->placa_vehiculo }}</td>
                            <td class="px-5 py-4">{{ $servicio->fecha->format('d/m/Y') }}</td>
                            <td class="px-5 py-4">Q{{ number_format($servicio->total, 2) }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold {{ $servicio->estado === 'abierto' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                    {{ ucfirst($servicio->estado) }}
                                </span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('foso.show', $servicio) }}" class="text-blue-600 hover:underline">Ver</a>
                                <a href="{{ route('foso.comprobante', $servicio) }}" class="ml-2 text-green-600 hover:underline">PDF</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-8 text-center">No hay servicios registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div>{{ $servicios->links() }}</div>
    </div>
</x-layouts.app>