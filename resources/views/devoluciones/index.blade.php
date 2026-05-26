<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Devoluciones</h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Gestión de devoluciones de ventas.</p>
            </div>
            <flux:button href="{{ route('devoluciones.create') }}" wire:navigate class="px-3 py-1.5 text-xs">
                Crear devolución
            </flux:button>
        </div>
        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 dark:border-red-900/60 dark:bg-red-950/40 dark:text-red-200">
                {{ $errors->first() }}
            </div>
        @endif
        <livewire:productos.low-stock-alert />
        <livewire:devoluciones.list />
    </div>
</x-layouts.app>
