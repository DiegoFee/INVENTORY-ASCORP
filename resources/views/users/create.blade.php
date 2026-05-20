<x-layouts.app>
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-4 lg:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Nuevo usuario</h1>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Crea una cuenta interna con rol asignado.</p>
            </div>

            <a href="{{ route('users.index') }}" class="text-sm font-medium text-zinc-700 hover:text-zinc-950 dark:text-zinc-300 dark:hover:text-white">Volver</a>
        </div>

        <form method="POST" action="{{ route('users.store') }}" class="max-w-2xl space-y-5">
            @csrf

            <div class="grid gap-2">
                <label for="name" class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Nombre</label>
                <input id="name" name="name" value="{{ old('name') }}" required class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                @error('name') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-2">
                <label for="email" class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Correo</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                @error('email') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-2">
                <label for="password" class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Contraseña</label>
                <input id="password" name="password" type="password" required class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                @error('password') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-2">
                <label for="role_id" class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Rol</label>
                <select id="role_id" name="role_id" required class="rounded-md border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 focus:border-zinc-500 focus:outline-none dark:border-zinc-700 dark:bg-zinc-900 dark:text-white">
                    <option value="">Seleccionar rol</option>
                    @foreach ($roles as $role)
                        <option value="{{ $role->id }}" @selected((int) old('role_id') === $role->id)>{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('role_id') <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
            </div>

            <input type="hidden" name="is_active" value="0">
            <label class="flex items-center gap-2 text-sm text-zinc-700 dark:text-zinc-300">
                <input type="checkbox" name="is_active" value="1" @checked(old('is_active', true)) class="rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500">
                Activo
            </label>

            <div class="flex items-center gap-3">
                <button type="submit" class="rounded-md bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-white dark:text-zinc-900 dark:hover:bg-zinc-200">Guardar</button>
                <a href="{{ route('users.index') }}" class="text-sm font-medium text-zinc-600 hover:text-zinc-950 dark:text-zinc-400 dark:hover:text-white">Cancelar</a>
            </div>
        </form>
    </div>
</x-layouts.app>
