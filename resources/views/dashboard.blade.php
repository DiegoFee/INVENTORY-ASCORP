<x-layouts.app>
    <div class="space-y-4">
        <div class="flex flex-col gap-1">
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">{{ $title ?? 'Panel administrativo' }}</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $description ?? 'Centro de monitoreo empresarial.' }}</p>
        </div>

        <livewire:dashboard.dashboard />
    </div>
</x-layouts.app>
