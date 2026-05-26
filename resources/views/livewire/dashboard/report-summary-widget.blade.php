<div class="grid gap-4 lg:grid-cols-3">
    <flux:card class="border border-zinc-700/70 bg-zinc-900/80">
        <div class="flex h-full flex-col">
            <div class="mt-3 space-y-1">
                <flux:subheading class="text-xs uppercase text-zinc-400">Ventas del mes</flux:subheading>
                <flux:heading size="xl" class="text-white">
                    Q.{{ number_format((float) ($ventasMesActual['total'] ?? 0), 2, '.', ',') }}
                </flux:heading>
                <flux:text class="text-xs text-zinc-400">
                    Ticket promedio: Q.{{ number_format((float) ($ventasMesActual['ticket_promedio'] ?? 0), 2, '.', ',') }}
                </flux:text>
            </div>
            <flux:button
                size="sm"
                icon="arrow-down-tray"
                href="{{ route('reports.export', ['type' => 'ventas']) }}"
                variant="ghost"
                class="mt-auto w-full justify-center gap-2 !text-emerald-400 !hover:text-emerald-300"
            >
                DESCARGAR REPORTE DE VENTAS EN PDF
            </flux:button>
        </div>
    </flux:card>

    <flux:card class="border border-zinc-700/70 bg-zinc-900/80">
        <div class="flex h-full flex-col">
            <div class="mt-3 space-y-1">
                <flux:subheading class="text-xs uppercase text-zinc-400">Inventario completo</flux:subheading>
                <flux:heading size="xl" class="text-white">
                    {{ (int) ($cantidadTotalInventario ?? 0) }}
                </flux:heading>
                <flux:text class="text-xs text-zinc-300">
                    Q.{{ number_format((float) ($valorTotalInventario ?? 0), 2, '.', ',') }}
                </flux:text>
                <flux:text class="text-xs text-rose-400">
                    Criticos: {{ (int) ($productosStockCritico ?? 0) }}
                </flux:text>
            </div>
            <flux:button
                size="sm"
                icon="arrow-down-tray"
                href="{{ route('reports.export', ['type' => 'inventario']) }}"
                variant="ghost"
                class="mt-auto w-full justify-center gap-2 !text-emerald-400 !hover:text-emerald-300"
            >
                DESCARGAR INVENTARIO EN PDF
            </flux:button>
        </div>
    </flux:card>

    <flux:card class="border border-zinc-700/70 bg-zinc-900/80">
        <div class="flex h-full flex-col">
            <div class="mt-3 space-y-1">
                <flux:subheading class="text-xs uppercase text-zinc-400">Saldos CxC pendientes</flux:subheading>
                <flux:heading size="xl" class="text-white">
                    Q.{{ number_format((float) ($saldosCxcPorVencer ?? 0), 2, '.', ',') }}
                </flux:heading>
                <flux:text class="text-xs text-zinc-400">Total Pending Portfolio / Overdue in 7 days</flux:text>
            </div>
            <flux:button
                size="sm"
                icon="arrow-down-tray"
                href="{{ route('reports.export', ['type' => 'cxc']) }}"
                variant="ghost"
                class="mt-auto w-full justify-center gap-2 !text-emerald-400 !hover:text-emerald-300"
            >
                DESCARGAR BALANCES CXC EN PDF
            </flux:button>
        </div>
    </flux:card>
</div>