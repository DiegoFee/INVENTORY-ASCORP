<?php

use App\Models\Cliente;
use Livewire\Volt\Component;

// Autor: Celvin
// Descripcion: Componente para editar clientes.
new class extends Component {
    public Cliente $cliente;
    public string $nit = '';
    public string $nombre = '';
    public ?string $direccion = null;
    public ?string $telefono = null;

    // Autor: Celvin
    // Descripcion: Inicializa el formulario con los datos del cliente.
    public function mount(Cliente $cliente): void
    {
        $this->cliente = $cliente;
        $this->nit = $cliente->nit;
        $this->nombre = $cliente->nombre;
        $this->direccion = $cliente->direccion ?? '';
        $this->telefono = $cliente->telefono ?? '';
    }

    // Autor: Celvin
    // Descripcion: Actualiza la informacion del cliente.
    public function update(): void
    {
        $this->direccion = $this->direccion === '' ? null : $this->direccion;
        $this->telefono = $this->telefono === '' ? null : $this->telefono;

        $validated = $this->validate([
            'nit' => ['required', 'string', 'max:20', 'regex:/^(CF|\d+)$/i'],
            'nombre' => ['required', 'string', 'max:255'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:25', 'regex:/^[0-9]+$/'],
        ]);

        $this->cliente->update($validated);

        session()->flash('success', 'Cliente actualizado correctamente.');

        $this->redirectRoute('clientes.index', navigate: true);
    }
}; ?>

<div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="space-y-1">
            <flux:heading>Editar cliente</flux:heading>
            <flux:subheading>{{ $cliente->nombre }}</flux:subheading>
        </div>

        <a href="{{ route('clientes.index') }}" class="text-sm font-medium text-zinc-700 hover:text-zinc-950 dark:text-zinc-300 dark:hover:text-white" wire:navigate>
            Volver
        </a>
    </div>

    <form wire:submit="update" class="max-w-2xl space-y-5">
        <div class="grid gap-2">
            <flux:input wire:model="nit" label="NIT" name="nit" required />
            @error('nit') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-2">
            <flux:input wire:model="nombre" label="Nombre" name="nombre" required />
            @error('nombre') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-2">
            <flux:textarea wire:model="direccion" label="Direccion" name="direccion" rows="3" />
            @error('direccion') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="grid gap-2">
            <flux:input wire:model="telefono" label="Telefono" name="telefono" />
            @error('telefono') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-3">
            <flux:button variant="primary" type="submit">Actualizar</flux:button>
            <a href="{{ route('clientes.index') }}" class="text-sm font-medium text-zinc-600 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-white" wire:navigate>
                Cancelar
            </a>
        </div>
    </form>
</div>
