<x-layouts.app>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h1 class="text-2xl font-bold mb-4">Detalle del Proveedor</h1>

                    <div class="mb-4">
                        <strong>Nombre:</strong> {{ $supplier->nombre }}
                    </div>
                    <div class="mb-4">
                        <strong>NIT:</strong> {{ $supplier->nit }}
                    </div>
                    <div class="mb-4">
                        <strong>Teléfono:</strong> {{ $supplier->telefono }}
                    </div>
                    <div class="mb-4">
                        <strong>Email:</strong> {{ $supplier->email }}
                    </div>
                    <div class="mb-4">
                        <strong>Dirección:</strong> {{ $supplier->direccion }}
                    </div>
                    <div class="mb-4">
                        <strong>Contacto:</strong> {{ $supplier->contacto_nombre ?? 'No especificado' }}
                    </div>

                    <div class="flex items-center gap-2">
                        <a href="{{ route('suppliers.edit', $supplier) }}" class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">Editar</a>
                        <a href="{{ route('suppliers.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">Volver</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>