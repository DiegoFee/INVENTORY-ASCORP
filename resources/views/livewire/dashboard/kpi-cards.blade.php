<div wire:poll.60s="refreshKpis" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
        <p class="text-xs uppercase text-zinc-500">Ventas del día</p>
        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">{{ number_format((float) ($kpis['ventas_dia'] ?? 0), 2) }}</p>
        <p class="text-xs text-zinc-500">{{ (int) ($kpis['cantidad_ventas_dia'] ?? 0) }} ventas</p>
    </div>
    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
        <p class="text-xs uppercase text-zinc-500">Ventas del mes</p>
        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">{{ number_format((float) ($kpis['ventas_mes'] ?? 0), 2) }}</p>
        <p class="text-xs text-zinc-500">Ticket promedio: {{ number_format((float) ($kpis['ticket_promedio'] ?? 0), 2) }}</p>
    </div>
    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
        <p class="text-xs uppercase text-zinc-500">Inventario</p>
        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">{{ (int) ($kpis['total_productos'] ?? 0) }}</p>
        <p class="text-xs text-red-600">Críticos: {{ (int) ($kpis['productos_stock_critico'] ?? 0) }}</p>
    </div>
    <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
        <p class="text-xs uppercase text-zinc-500">Operación</p>
        <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-white">{{ (int) ($kpis['servicios_foso_hoy'] ?? 0) }}</p>
        <p class="text-xs text-zinc-500">Alertas activas: {{ (int) ($kpis['alertas_activas'] ?? 0) }}</p>
    </div>
</div>
