<x-layouts.app>
    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="overflow-hidden rounded-xl border border-neutral-800 bg-neutral-950 shadow-2xl shadow-black/20">
                <div class="border-b border-neutral-800 bg-neutral-900/70 px-6 py-5">
                    <div>
                        <h1 class="text-2xl font-semibold tracking-tight text-white">Nuevo Proveedor</h1>
                        <p class="mt-1 text-sm text-neutral-400">Registra los datos del proveedor en el sistema.</p>
                    </div>
                </div>

                <div class="p-6">
                    <form action="{{ route('suppliers.store') }}" method="POST" class="space-y-6">
                        @csrf

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label for="nombre" class="block text-sm font-medium text-neutral-300">Nombre *</label>
                                <input type="text" name="nombre" id="nombre" value="{{ old('nombre') }}"
                                       class="mt-1 block w-full rounded-lg border-neutral-700 bg-neutral-900 text-neutral-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('nombre') border-red-500 @enderror">
                                @error('nombre') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="nit" class="block text-sm font-medium text-neutral-300">NIT *</label>
                                <input type="text" name="nit" id="nit" value="{{ old('nit') }}"
                                       class="mt-1 block w-full rounded-lg border-neutral-700 bg-neutral-900 text-neutral-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('nit') border-red-500 @enderror">
                                @error('nit') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="telefono" class="block text-sm font-medium text-neutral-300">Teléfono *</label>
                                <input type="text" name="telefono" id="telefono" value="{{ old('telefono') }}"
                                       class="mt-1 block w-full rounded-lg border-neutral-700 bg-neutral-900 text-neutral-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('telefono') border-red-500 @enderror">
                                @error('telefono') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label for="email" class="block text-sm font-medium text-neutral-300">Email *</label>
                                <input type="email" name="email" id="email" value="{{ old('email') }}"
                                       class="mt-1 block w-full rounded-lg border-neutral-700 bg-neutral-900 text-neutral-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('email') border-red-500 @enderror">
                                @error('email') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="direccion" class="block text-sm font-medium text-neutral-300">Dirección *</label>
                                <textarea name="direccion" id="direccion" rows="3"
                                          class="mt-1 block w-full rounded-lg border-neutral-700 bg-neutral-900 text-neutral-100 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('direccion') border-red-500 @enderror">{{ old('direccion') }}</textarea>
                                @error('direccion') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
                            </div>

                            <div class="sm:col-span-2">
                                <label for="contacto_nombre" class="block text-sm font-medium text-neutral-300">Persona de contacto (opcional)</label>
                                <input type="text" name="contacto_nombre" id="contacto_nombre" value="{{ old('contacto_nombre') }}"
                                       class="mt-1 block w-full rounded-lg border-neutral-700 bg-neutral-900 text-neutral-100 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4">
                            <a href="{{ route('suppliers.index') }}" class="rounded-lg px-4 py-2 text-sm font-medium text-neutral-300 transition hover:bg-neutral-800 hover:text-white">Cancelar</a>
                            <button type="submit" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm shadow-blue-950/40 transition hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-2 focus:ring-offset-neutral-950">Guardar Proveedor</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>