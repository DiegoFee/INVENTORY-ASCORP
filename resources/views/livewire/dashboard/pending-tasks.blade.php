<div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
    <h3 class="mb-3 text-sm font-semibold text-zinc-800 dark:text-zinc-200">Tareas pendientes</h3>
    <div class="space-y-2">
        @foreach ($tasks as $task)
            @if (Route::has($task['route']))
                <a href="{{ route($task['route']) }}" class="flex items-center justify-between rounded-md border border-zinc-200 px-3 py-2 text-sm dark:border-zinc-700">
                    <span class="text-zinc-700 dark:text-zinc-200">{{ $task['label'] }}</span>
                    <span class="rounded-full bg-zinc-900 px-2 py-0.5 text-xs text-white dark:bg-zinc-200 dark:text-zinc-900">{{ $task['count'] }}</span>
                </a>
            @endif
        @endforeach
    </div>
</div>
