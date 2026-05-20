<x-layouts.app>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="flex justify-between items-center mb-4">
                        <h1 class="text-2xl font-bold">Proveedores</h1>
                        <a href="{{ route('suppliers.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            + Nuevo Proveedor
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    <table class="min-w-full bg-white border">
                        <thead>
                            <tr>
                                <th class="py-2 px-4 border-b">#</th>
                                <th class="py-2 px-4 border-b">Nombre</th>
                                <th class="py-2 px-4 border-b">NIT</th>
                                <th class="py-2 px-4 border-b">Teléfono</th>
                                <th class="py-2 px-4 border-b">Email</th>
                                <th class="py-2 px-4 border-b">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($suppliers as $supplier)
                            <tr>
                                <td class="py-2 px-4 border-b">{{ $loop->iteration }}</td>
                                <td class="py-2 px-4 border-b">{{ $supplier->nombre }}</td>
                                <td class="py-2 px-4 border-b">{{ $supplier->nit }}</td>
                                <td class="py-2 px-4 border-b">{{ $supplier->telefono }}</td>
                                <td class="py-2 px-4 border-b">{{ $supplier->email }}</td>
                                <td class="py-2 px-4 border-b">
                                    <a href="{{ route('suppliers.show', $supplier) }}" class="text-blue-600 hover:text-blue-900 mr-2">Ver</a>
                                    <a href="{{ route('suppliers.edit', $supplier) }}" class="text-yellow-600 hover:text-yellow-900 mr-2">Editar</a>
                                    <form action="{{ route('suppliers.destroy', $supplier) }}" method="POST" class="inline-block" onsubmit="return confirm('¿Eliminar este proveedor?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $suppliers->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>