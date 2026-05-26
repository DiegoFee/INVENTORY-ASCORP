<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Dashboard principal</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Monitoreo de ventas, inventario y operación.</p>
        </div>
    </div>

    <livewire:dashboard.kpi-cards />

    <div class="grid gap-6 lg:grid-cols-2">
        <livewire:dashboard.sales-chart />
        <livewire:dashboard.inventory-chart />
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        @include('livewire.dashboard.quick-actions', ['actions' => $quickActions])
        @include('livewire.dashboard.pending-tasks', ['tasks' => $pendingTasks])

        <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
            <h3 class="mb-3 text-sm font-semibold text-zinc-800 dark:text-zinc-200">Alertas activas (Top 10)</h3>
            <div class="space-y-2">
                @forelse ($alerts as $alert)
                    <div class="rounded-md border border-zinc-200 p-3 text-sm dark:border-zinc-700">
                        <p class="font-medium text-zinc-900 dark:text-white">{{ strtoupper($alert['tipo']) }}</p>
                        <p class="text-zinc-600 dark:text-zinc-300">{{ $alert['mensaje'] }}</p>
                    </div>
                @empty
                    <p class="text-sm text-zinc-500">Sin alertas activas.</p>
                @endforelse
            </div>
        </div>
    </div>

    <livewire:dashboard.recent-activity />
</div>
