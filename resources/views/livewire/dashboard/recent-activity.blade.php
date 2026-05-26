<div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
    <h3 class="mb-3 text-sm font-semibold text-zinc-800 dark:text-zinc-200">Actividad reciente</h3>
    <div class="space-y-3">
        @forelse ($activities as $activity)
            <div class="rounded-md border border-zinc-200 p-3 dark:border-zinc-700">
                <p class="text-sm font-medium text-zinc-900 dark:text-white">{{ $activity['usuario'] }}</p>
                <p class="text-sm text-zinc-700 dark:text-zinc-300">{{ $activity['accion'] }}</p>
                <p class="text-xs text-zinc-500">{{ $activity['fecha'] }}</p>
            </div>
        @empty
            <p class="text-sm text-zinc-500">Sin actividad reciente.</p>
        @endforelse
    </div>
</div>
