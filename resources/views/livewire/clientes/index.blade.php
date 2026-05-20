<?php

use App\Models\Cliente;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Volt\Component;
use Livewire\WithPagination;

// Autor: Celvin
// Descripcion: Componente para listar clientes.
new class extends Component {
    use WithPagination;

    public string $search = '';

    // Autor: Celvin
    // Descripcion: Reinicia la paginacion al cambiar la busqueda.
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    // Autor: Celvin
    // Descripcion: Obtiene la lista paginada de clientes.
    public function getClientesProperty(): LengthAwarePaginator
    {
        return Cliente::query()
            ->when($this->search, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('nombre', 'like', '%'.$search.'%')
                        ->orWhere('nit', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('id')
            ->paginate(10);
    }

    // Autor: Celvin
    // Descripcion: Elimina un cliente por id.
    public function delete(int $clienteId): void
    {
        Cliente::query()->whereKey($clienteId)->delete();

        session()->flash('success', 'Cliente eliminado correctamente.');
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-1">
            <flux:heading>Clientes</flux:heading>
            <flux:subheading>Administracion de clientes internos.</flux:subheading>
        </div>

        <flux:button variant="primary" href="{{ route('clientes.create') }}" wire:navigate>
            Nuevo cliente
        </flux:button>
    </div>

    <div class="max-w-xl">
        <flux:input
            wire:model.debounce.300ms="search"
            label="Buscar"
            name="search"
            placeholder="Buscar por nombre o NIT"
        />
    </div>

    @if (session('success'))
        <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-lg border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                <thead class="bg-zinc-50 text-left text-xs font-semibold uppercase text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
                    <tr>
                        <th class="px-4 py-3">NIT</th>
                        <th class="px-4 py-3">Nombre</th>
                        <th class="px-4 py-3">Direccion</th>
                        <th class="px-4 py-3">Telefono</th>
                        <th class="px-4 py-3 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse ($this->clientes as $cliente)
                        <tr class="text-zinc-800 dark:text-zinc-100">
                            <td class="px-4 py-3 font-medium">{{ $cliente->nit }}</td>
                            <td class="px-4 py-3">{{ $cliente->nombre }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                {{ $cliente->direccion ?? 'Sin direccion' }}
                            </td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">
                                {{ $cliente->telefono ?? 'Sin telefono' }}
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap items-center justify-end gap-2">
                                    <flux:button
                                        href="{{ route('clientes.edit', $cliente) }}"
                                        wire:navigate
                                        class="px-3 py-1.5 text-xs"
                                    >
                                        Editar
                                    </flux:button>

                                    <flux:button
                                        type="button"
                                        class="px-3 py-1.5 text-xs text-red-700 dark:text-red-300"
                                        onclick="return confirm('Confirmas eliminar este cliente?')"
                                        wire:click="delete({{ $cliente->id }})"
                                    >
                                        Eliminar
                                    </flux:button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                No hay clientes registrados.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{ $this->clientes->links() }}
</div>
