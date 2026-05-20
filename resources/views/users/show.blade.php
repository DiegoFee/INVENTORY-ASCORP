<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">{{ $user->name }}</h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ $user->email }}</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <a href="{{ route('users.index') }}" class="rounded-md border border-zinc-300 px-3 py-2 text-sm font-medium hover:bg-zinc-50 dark:border-zinc-600 dark:hover:bg-zinc-800">Volver</a>
                <a href="{{ route('users.edit', $user) }}" class="rounded-md bg-zinc-900 px-3 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">Editar</a>
            </div>
        </div>

        @if (session('success'))
            <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/60 dark:bg-emerald-950/40 dark:text-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        <dl class="grid max-w-3xl grid-cols-1 gap-4 sm:grid-cols-2">
            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Rol</dt>
                <dd class="mt-2">
                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $user->role?->name === \App\Models\Role::Admin ? 'bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-200' : 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-200' }}">
                        {{ $user->role?->name ?? 'Sin rol' }}
                    </span>
                </dd>
            </div>

            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Estado</dt>
                <dd class="mt-2">
                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $user->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-200' : 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-200' }}">
                        {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                    </span>
                </dd>
            </div>

            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Creado</dt>
                <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ $user->created_at?->format('d/m/Y H:i') }}</dd>
            </div>

            <div class="rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <dt class="text-xs font-semibold uppercase text-zinc-500 dark:text-zinc-400">Actualizado</dt>
                <dd class="mt-2 text-sm text-zinc-800 dark:text-zinc-100">{{ $user->updated_at?->format('d/m/Y H:i') }}</dd>
            </div>
        </dl>
    </div>
</x-layouts.app>
