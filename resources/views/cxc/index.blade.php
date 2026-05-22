<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Cuentas por Cobrar</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Gestión de créditos y abonos</p>
        </div>

        <!-- Filtros -->
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <form method="GET" class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Cliente</label>
                    <select name="cliente_id" class="mt-1 w-full rounded-md border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800">
                        <option value="">Todos</option>
                        @foreach(\App\Models\Cliente::orderBy('nombre')->get() as $cliente)
                            <option value="{{ $cliente->id }}" {{ request('cliente_id') == $cliente->id ? 'selected' : '' }}>
                                {{ $cliente->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Estado</label>
                    <select name="estado" class="mt-1 w-full rounded-md border border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800">
                        <option value="">Todos</option>
                        <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                        <option value="saldada" {{ request('estado') == 'saldada' ? 'selected' : '' }}>Saldada</option>
                        <option value="vencida" {{ request('estado') == 'vencida' ? 'selected' : '' }}>Vencida</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="rounded-md bg-zinc-900 px-4 py-2 text-white dark:bg-white dark:text-zinc-900">Filtrar</button>
                    @if(request()->anyFilled(['cliente_id', 'estado']))
                        <a href="{{ route('cxc.index') }}" class="rounded-md border border-zinc-300 px-4 py-2">Limpiar</a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabla -->
        <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
            <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                <thead class="bg-zinc-50 dark:bg-zinc-800">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider">Cliente</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider">Total</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider">Saldo</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider">Estado</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wider">Vencimiento</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                    @forelse($cuentas as $cuenta)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                        <td class="whitespace-nowrap px-5 py-4 text-sm">{{ $cuenta->cliente?->nombre ?? 'N/A' }}</td>
                        <td class="whitespace-nowrap px-5 py-4 text-sm">Q{{ number_format($cuenta->total, 2) }}</td>
                        <td class="whitespace-nowrap px-5 py-4 text-sm font-semibold {{ $cuenta->saldo > 0 ? 'text-red-600' : 'text-green-600' }}">Q{{ number_format($cuenta->saldo, 2) }}</td>
                        <td class="whitespace-nowrap px-5 py-4 text-sm capitalize">{{ $cuenta->estado }}</td>
                        <td class="whitespace-nowrap px-5 py-4 text-sm">{{ $cuenta->fecha_vencimiento ? $cuenta->fecha_vencimiento->format('d/m/Y') : '—' }}</td>
                        <td class="whitespace-nowrap px-5 py-4 text-right text-sm">
                            <a href="{{ route('cxc.show', $cuenta) }}" class="text-blue-600 hover:underline">Ver detalle</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-8 text-center text-sm text-zinc-500">No hay cuentas por cobrar registradas.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $cuentas->links() }}
        </div>
    </div>
</x-layouts.app>