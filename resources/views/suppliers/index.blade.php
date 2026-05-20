<x-layouts.app>
    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <!-- Tarjeta principal -->
            <div class="overflow-hidden rounded-xl border border-neutral-800 bg-neutral-950 shadow-2xl shadow-black/20">
                <!-- Cabecera con título y botón nuevo -->
                <div class="border-b border-neutral-800 bg-neutral-900/70 px-6 py-5">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1 class="text-2xl font-semibold tracking-tight text-white">
                                Proveedores
                            </h1>
                            <p class="mt-1 text-sm text-neutral-400">
                                Gestiona tus proveedores, contactos y compras.
                            </p>
                        </div>
                        <a href="{{ route('suppliers.create') }}"
                           class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm shadow-blue-950/40 transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:ring-offset-neutral-950">
                            + Nuevo Proveedor
                        </a>
                    </div>
                </div>

                <div class="p-6">
                    <!-- Mensaje de éxito -->
                    @if(session('success'))
                        <div class="mb-5 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm font-medium text-emerald-300">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Formulario de búsqueda y filtros -->
                    <div class="mb-6 rounded-lg border border-neutral-800 bg-neutral-900/40 p-4">
                        <form method="GET" action="{{ route('suppliers.index') }}" class="flex flex-col gap-4 md:flex-row md:items-end">
                            <!-- Buscador principal -->
                            <div class="flex-1">
                                <label for="search" class="block text-xs font-medium uppercase tracking-wide text-neutral-400 mb-1">Buscar</label>
                                <div class="relative">
                                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                                           placeholder="Nombre, NIT o email..."
                                           class="w-full rounded-lg border-neutral-700 bg-neutral-900 py-2 pl-10 pr-4 text-neutral-100 placeholder:text-neutral-500 focus:border-blue-500 focus:ring-blue-500">
                                    <svg class="absolute left-3 top-2.5 h-5 w-5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>

                            <!-- Filtro: tiene contacto -->
                            <div class="w-full md:w-48">
                                <label for="has_contact" class="block text-xs font-medium uppercase tracking-wide text-neutral-400 mb-1">Contacto</label>
                                <select name="has_contact" id="has_contact"
                                        class="w-full rounded-lg border-neutral-700 bg-neutral-900 py-2 pl-3 pr-8 text-neutral-100 focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Todos</option>
                                    <option value="yes" {{ request('has_contact') == 'yes' ? 'selected' : '' }}>Con contacto</option>
                                    <option value="no" {{ request('has_contact') == 'no' ? 'selected' : '' }}>Sin contacto</option>
                                </select>
                            </div>

                            <!-- Botones -->
                            <div class="flex gap-2">
                                <button type="submit"
                                        class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-blue-500">
                                    Filtrar
                                </button>
                                @if(request()->anyFilled(['search', 'has_contact']))
                                    <a href="{{ route('suppliers.index') }}"
                                       class="inline-flex items-center justify-center rounded-lg border border-neutral-700 px-4 py-2 text-sm font-medium text-neutral-300 transition hover:bg-neutral-800">
                                        Limpiar
                                    </a>
                                @endif
                            </div>
                        </form>
                    </div>

                    <!-- Tabla de proveedores -->
                    <div class="overflow-hidden rounded-lg border border-neutral-800">
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-neutral-800">
                                <thead class="bg-neutral-900">
                                    <tr>
                                        <th class="w-16 px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-neutral-400">#</th>
                                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-neutral-400">Nombre</th>
                                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-neutral-400">NIT</th>
                                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-neutral-400">Teléfono</th>
                                        <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide text-neutral-400">Email</th>
                                        <th class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wide text-neutral-400">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-neutral-800 bg-neutral-950">
                                    @forelse($suppliers as $supplier)
                                        <tr class="transition hover:bg-neutral-900/70">
                                            <td class="whitespace-nowrap px-5 py-4 text-sm text-neutral-400">{{ $loop->iteration }}</td>
                                            <td class="whitespace-nowrap px-5 py-4">
                                                <div class="font-medium text-neutral-100">{{ $supplier->nombre }}</div>
                                                @if($supplier->contacto_nombre)
                                                    <div class="mt-0.5 text-xs text-neutral-500">Contacto: {{ $supplier->contacto_nombre }}</div>
                                                @endif
                                            </td>
                                            <td class="whitespace-nowrap px-5 py-4 text-sm text-neutral-300">{{ $supplier->nit }}</td>
                                            <td class="whitespace-nowrap px-5 py-4 text-sm text-neutral-300">{{ $supplier->telefono }}</td>
                                            <td class="whitespace-nowrap px-5 py-4 text-sm text-neutral-300">{{ $supplier->email }}</td>
                                            <td class="whitespace-nowrap px-5 py-4 text-right text-sm">
                                                <div class="inline-flex items-center gap-2">
                                                    <a href="{{ route('suppliers.show', $supplier) }}"
                                                       class="rounded-md px-2.5 py-1.5 font-medium text-blue-400 transition hover:bg-blue-500/10 hover:text-blue-300">
                                                        Ver
                                                    </a>
                                                    <a href="{{ route('suppliers.edit', $supplier) }}"
                                                       class="rounded-md px-2.5 py-1.5 font-medium text-amber-400 transition hover:bg-amber-500/10 hover:text-amber-300">
                                                        Editar
                                                    </a>
                                                    <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Eliminar este proveedor?')">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="rounded-md px-2.5 py-1.5 font-medium text-red-400 transition hover:bg-red-500/10 hover:text-red-300">
                                                            Eliminar
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-5 py-12 text-center">
                                                <svg class="mx-auto h-12 w-12 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                                </svg>
                                                <div class="mt-2 text-sm font-medium text-neutral-300">No hay proveedores registrados</div>
                                                <div class="mt-1 text-sm text-neutral-500">Crea el primer proveedor para comenzar.</div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Paginación -->
                    @if($suppliers->hasPages())
                        <div class="mt-5">
                            {{ $suppliers->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>