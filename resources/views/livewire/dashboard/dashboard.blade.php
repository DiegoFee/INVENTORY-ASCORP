<div class="space-y-6">
    <!-- Título y descripción -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-white">Dashboard principal</h1>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">Monitoreo de ventas, inventario y operación.</p>
        </div>
    </div>

    <livewire:dashboard.report-summary-widget />

    <!-- Gráficos -->
    <div class="grid gap-6 lg:grid-cols-2">
        <livewire:dashboard.sales-chart />
        <livewire:dashboard.inventory-chart />
    </div>

    <!-- Acciones rápidas, tareas pendientes y alertas -->
    <div class="grid gap-6 lg:grid-cols-3">
        @include('livewire.dashboard.quick-actions', ['actions' => $quickActions ?? []])
        @include('livewire.dashboard.pending-tasks', ['tasks' => $pendingTasks ?? []])

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

    <!-- Indicadores de Cuentas por Cobrar -->
    @if (isset($indicadoresCxc) && is_array($indicadoresCxc))
        <div class="space-y-6">
            <!-- Título de la sección CxC -->
            <div>
                <h2 class="text-lg font-semibold text-zinc-900 dark:text-white">Cuentas por Cobrar</h2>
                <p class="text-sm text-zinc-600 dark:text-zinc-400">Estado financiero de cuentas pendientes.</p>
            </div>

            <!-- Tarjetas de indicadores CxC -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <!-- Total de Cuentas Pendientes -->
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Cuentas Pendientes</p>
                            <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">
                                {{ $indicadoresCxc['total_cuentas'] ?? 0 }}
                            </p>
                        </div>
                        <!-- Icono SVG para cuentas -->
                        <div class="rounded-lg bg-blue-50 p-3 dark:bg-blue-900/20">
                            <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Adeudado -->
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Monto Total Adeudado</p>
                            <p class="mt-2 text-3xl font-bold text-zinc-900 dark:text-white">
                                Q{{ number_format($indicadoresCxc['total_adeudado'] ?? 0, 2, '.', ',') }}
                            </p>
                        </div>
                        <!-- Icono SVG para dinero -->
                        <div class="rounded-lg bg-green-50 p-3 dark:bg-green-900/20">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="9"></circle>
                                <path d="M12 6v12"></path>
                                <path d="M9 9h6a2 2 0 0 1 2 2v2a2 2 0 0 1-2 2h-6a2 2 0 0 1-2-2v-2a2 2 0 0 1 2-2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Cuentas Vencidas -->
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Cuentas Vencidas</p>
                            <p class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400">
                                {{ $indicadoresCxc['total_vencidas'] ?? 0 }}
                            </p>
                        </div>
                        <!-- Icono SVG para alerta -->
                        <div class="rounded-lg bg-red-50 p-3 dark:bg-red-900/20">
                            <svg class="h-6 w-6 text-red-600 dark:text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="13" r="8"></circle>
                                <path d="M12 9v4"></path>
                                <path d="M12 17h.01"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Monto Vencido -->
                <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-zinc-600 dark:text-zinc-400">Monto Vencido</p>
                            <p class="mt-2 text-3xl font-bold text-red-600 dark:text-red-400">
                                Q{{ number_format($indicadoresCxc['monto_vencido'] ?? 0, 2, '.', ',') }}
                            </p>
                        </div>
                        <!-- Icono SVG para reloj -->
                        <div class="rounded-lg bg-orange-50 p-3 dark:bg-orange-900/20">
                            <svg class="h-6 w-6 text-orange-600 dark:text-orange-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="9"></circle>
                                <polyline points="12 6 12 12 16 14"></polyline>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabla de Últimas Cuentas Pendientes -->
            <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
                <div class="border-b border-zinc-200 px-6 py-4 dark:border-zinc-700">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-semibold text-zinc-900 dark:text-white">Próximas a Vencer</h3>
                        <a href="{{ route('cxc.index') }}" class="text-xs font-medium text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                            Ver todas →
                        </a>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50">
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Cliente</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Saldo</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Vencimiento</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Estado</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-zinc-600 dark:text-zinc-400">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                            @forelse ($indicadoresCxc['ultimas_cuentas'] ?? [] as $cuenta)
                                @php
                                    $hoy = \Carbon\Carbon::today();
                                    $vencimiento = \Carbon\Carbon::parse($cuenta->fecha_vencimiento);
                                    $diasRestantes = $vencimiento->diffInDays($hoy, false);
                                    $esVencida = $diasRestantes < 0;
                                    $proximaAVencer = $diasRestantes >= 0 && $diasRestantes <= 7;
                                @endphp
                                <tr class="hover:bg-zinc-50 transition dark:hover:bg-zinc-800/50">
                                    <td class="px-6 py-4 text-sm text-zinc-900 dark:text-white">
                                        {{ $cuenta->cliente?->nombre ?? 'Sin cliente' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-zinc-600 dark:text-zinc-300">
                                        Q{{ number_format($cuenta->total ?? 0, 2, '.', ',') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium text-zinc-900 dark:text-white">
                                        Q{{ number_format($cuenta->saldo ?? 0, 2, '.', ',') }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-zinc-600 dark:text-zinc-300">
                                        {{ $vencimiento->format('d/m/Y') }}
                                        @if ($esVencida)
                                            <span class="ml-2 inline-block rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700 dark:bg-red-900/30 dark:text-red-400">
                                                Vencida {{ abs($diasRestantes) }}d
                                            </span>
                                        @elseif ($proximaAVencer)
                                            <span class="ml-2 inline-block rounded-full bg-yellow-100 px-2 py-0.5 text-xs font-medium text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400">
                                                En {{ $diasRestantes }}d
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span class="inline-flex items-center rounded-full bg-blue-100 px-3 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">
                                            Pendiente
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm">
                                        <a href="{{ route('cxc.show', $cuenta->id) }}" class="text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                            Ver
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
                                        <p class="font-medium">No hay cuentas pendientes.</p>
                                        <p class="text-xs">Todas tus cuentas han sido cobradas o canceladas.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <livewire:dashboard.recent-activity />
</div>
