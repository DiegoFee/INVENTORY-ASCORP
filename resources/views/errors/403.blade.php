<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
        <title>Acceso Denegado - {{ config('app.name', 'Laravel') }}</title>
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <div class="flex flex-col items-center justify-center min-h-screen px-4">
            <h1 class="text-6xl font-bold text-red-600">403</h1>
            <p class="text-xl text-zinc-600 dark:text-zinc-400 mt-4">Acceso Denegado</p>
            <p class="text-sm text-zinc-500 dark:text-zinc-500 mt-2 text-center max-w-md">
                No tiene permisos suficientes para acceder a este módulo.
            </p>

            <div class="mt-8 flex gap-4">
                <flux:button href="{{ route(auth()->user()?->homeRouteName() ?? 'login') }}" icon="home">
                    Volver al Dashboard
                </flux:button>
                <flux:button onclick="history.back()" icon="arrow-left">
                    Regresar
                </flux:button>
            </div>
        </div>

        @fluxScripts
    </body>
</html>
