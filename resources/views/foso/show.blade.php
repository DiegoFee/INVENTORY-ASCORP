<x-layouts.app> 
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-xl font-semibold">Servicio #{{ $servicio->id }}</h1>
                <p class="text-sm text-zinc-600">
                    Cliente: {{ $servicio->cliente?->nombre ?? 'Cliente no disponible' }}
                    | Placa: {{ $servicio->placa_vehiculo }}
                </p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('foso.index') }}" class="rounded-md border px-4 py-2">Volver</a>
                @if($servicio->estado === 'abierto')
                    <form action="{{ route('foso.close', $servicio) }}" method="POST" onsubmit="return confirm('¿Cerrar servicio? Se descontará el stock de los insumos.')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="rounded-md bg-yellow-600 px-4 py-2 text-white">Cerrar Servicio</button>
                    </form>
                @endif
                <a href="{{ url('/foso/' . $servicio->id . '/comprobante') }}" target="_blank" class="rounded-md bg-green-600 px-4 py-2 text-white">Comprobante PDF</a>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 rounded-lg border p-4 sm:grid-cols-2">
            <div><span class="text-zinc-500">Fecha:</span> {{ $servicio->fecha?->format('d/m/Y') ?? 'No definida' }}</div>
            <div><span class="text-zinc-500">Estado:</span> <span class="capitalize">{{ $servicio->estado }}</span></div>
            <div>
                <span class="text-zinc-500">Técnico:</span>
                {{ $servicio->tecnico?->name ?? 'No asignado' }}
            </div>
            <div><span class="text-zinc-500">Total:</span> Q{{ number_format($servicio->total, 2) }}</div>
            <div class="sm:col-span-2"><span class="text-zinc-500">Observaciones:</span> {{ $servicio->observaciones ?: 'Ninguna' }}</div>
        </div>

        <div class="rounded-lg border p-4">
            <h3 class="font-semibold mb-2">Insumos utilizados</h3>
            <table class="min-w-full divide-y">
                <thead><tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Subtotal</th></tr></thead>
                <tbody>
                    @foreach($servicio->detalles as $detalle)
                        <tr>
                            <td>{{ $detalle->producto?->nombre ?? 'Producto eliminado' }}</td>
                            <td>{{ $detalle->cantidad }}</td>
                            <td>Q{{ number_format($detalle->precio_unitario, 2) }}</td>
                            <td>Q{{ number_format($detalle->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>