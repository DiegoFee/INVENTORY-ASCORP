<div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
    <h3 class="mb-3 text-sm font-semibold text-zinc-800 dark:text-zinc-200">Accesos rápidos</h3>
    <div class="grid gap-2">
        @foreach ($actions as $action)
            @if (Route::has($action['route']))
                <a href="{{ route($action['route']) }}" class="rounded-md border border-zinc-200 px-3 py-2 text-sm text-zinc-700 transition hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">
                    {{ $action['label'] }}
                </a>
            @endif
        @endforeach
    </div>
</div>
