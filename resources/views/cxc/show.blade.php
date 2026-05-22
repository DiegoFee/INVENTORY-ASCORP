<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Cuenta #{{ $cuenta->id }}</h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Detalles de la cuenta por cobrar</p>
            </div>
            <a href="{{ route('cxc.index') }}" class="inline-flex items-center justify-center rounded-md border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-100 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white dark:hover:bg-zinc-700">Volver</a>
        </div>

        @if(session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        <!-- Información del cliente y venta -->
        <div class="grid grid-cols-1 gap-4 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900 sm:grid-cols-2">
            <div>
                <span class="text-sm text-zinc-500 dark:text-zinc-400">Cliente:</span>
                <p class="text-zinc-900 dark:text-white">{{ $cuenta->cliente?->nombre ?? 'Cliente no disponible' }}</p>
            </div>
            <div>
                <span class="text-sm text-zinc-500 dark:text-zinc-400">Venta #:</span>
                <p class="text-zinc-900 dark:text-white">{{ $cuenta->venta?->id ?? 'N/A' }}</p>
            </div>
        </div>

        <!-- Resumen de la cuenta -->
        <div class="grid grid-cols-1 gap-4 rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900 sm:grid-cols-2">
            <div>
                <span class="text-sm text-zinc-500 dark:text-zinc-400">Total:</span>
                <p class="text-lg font-semibold text-zinc-900 dark:text-white">Q{{ number_format($cuenta->total, 2) }}</p>
            </div>
            <div>
                <span class="text-sm text-zinc-500 dark:text-zinc-400">Saldo pendiente:</span>
                <p class="text-lg font-semibold text-red-600 dark:text-red-400">Q{{ number_format($cuenta->saldo, 2) }}</p>
            </div>
            <div>
                <span class="text-sm text-zinc-500 dark:text-zinc-400">Estado:</span>
                <p class="capitalize text-zinc-900 dark:text-white">{{ $cuenta->estado }}</p>
            </div>
            <div>
                <span class="text-sm text-zinc-500 dark:text-zinc-400">Vencimiento:</span>
                <p class="text-zinc-900 dark:text-white">{{ $cuenta->fecha_vencimiento ? $cuenta->fecha_vencimiento->format('d/m/Y') : 'No definida' }}</p>
            </div>
        </div>

        <!-- Formulario de abono (solo si hay saldo) -->
        @if($cuenta->saldo > 0)
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <h3 class="text-md font-semibold text-zinc-900 dark:text-white">Registrar Abono</h3>
            <form action="{{ route('cxc.abono.store', $cuenta) }}" method="POST" class="mt-3 grid grid-cols-1 gap-4 sm:grid-cols-2">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Monto</label>
                    <input type="number" step="0.01" name="monto" class="mt-1 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Fecha pago</label>
                    <input type="date" name="fecha_pago" value="{{ date('Y-m-d') }}" class="mt-1 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Método</label>
                    <select name="metodo_pago" class="mt-1 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white" required>
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="cheque">Cheque</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Referencia (opcional)</label>
                    <input type="text" name="referencia" class="mt-1 w-full rounded-md border border-zinc-300 bg-white px-3 py-2 text-zinc-900 dark:border-zinc-600 dark:bg-zinc-800 dark:text-white">
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" class="rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">Guardar Abono</button>
                </div>
            </form>
        </div>
        @endif

        <!-- Historial de pagos -->
        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <h3 class="text-md font-semibold text-zinc-900 dark:text-white">Historial de Abonos</h3>
            <div class="mt-3 overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-semibold">Fecha</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold">Monto</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold">Método</th>
                            <th class="px-4 py-2 text-left text-xs font-semibold">Referencia</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                        @forelse($cuenta->pagos->sortByDesc('fecha_pago') as $pago)
                        <tr>
                            <td class="whitespace-nowrap px-4 py-2 text-sm">{{ $pago->fecha_pago->format('d/m/Y') }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-sm text-green-600">Q{{ number_format($pago->monto, 2) }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-sm">{{ $pago->metodo_pago }}</td>
                            <td class="whitespace-nowrap px-4 py-2 text-sm">{{ $pago->referencia ?? '—' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="px-4 py-4 text-center text-sm">Sin abonos registrados</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>