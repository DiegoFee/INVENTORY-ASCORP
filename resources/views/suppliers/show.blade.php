<x-layouts.app>
    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-xl border border-neutral-800 bg-neutral-950 shadow-2xl shadow-black/20">
                <div class="border-b border-neutral-800 bg-neutral-900/70 px-6 py-5">
                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight text-white">Detalle del Proveedor</h1>
                        <p class="mt-1 text-sm text-neutral-400">Información completa del proveedor.</p>
                    </div>
                </div>

                <div class="p-6">
                    <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-neutral-400">Nombre</dt>
                            <dd class="mt-1 text-sm text-neutral-100">{{ $supplier->nombre }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-neutral-400">NIT</dt>
                            <dd class="mt-1 text-sm text-neutral-100">{{ $supplier->nit }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-neutral-400">Teléfono</dt>
                            <dd class="mt-1 text-sm text-neutral-100">{{ $supplier->telefono }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-neutral-400">Email</dt>
                            <dd class="mt-1 text-sm text-neutral-100">{{ $supplier->email }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-neutral-400">Dirección</dt>
                            <dd class="mt-1 text-sm text-neutral-100">{{ $supplier->direccion }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-neutral-400">Persona de contacto</dt>
                            <dd class="mt-1 text-sm text-neutral-100">{{ $supplier->contacto_nombre ?? 'No especificado' }}</dd>
                        </div>
                    </dl>

                    <div class="mt-8 flex items-center gap-4">
                        <a href="{{ route('suppliers.edit', $supplier) }}"
                           class="inline-flex items-center justify-center rounded-lg bg-amber-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-amber-950/40 transition hover:bg-amber-500">
                            Editar
                        </a>
                        <a href="{{ route('suppliers.index') }}"
                           class="rounded-lg px-4 py-2 text-sm font-medium text-neutral-300 transition hover:bg-neutral-800 hover:text-white">
                            Volver a la lista
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>