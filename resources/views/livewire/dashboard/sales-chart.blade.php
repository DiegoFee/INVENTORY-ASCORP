<div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900" wire:ignore>
    <h3 class="mb-3 text-sm font-semibold text-zinc-800 dark:text-zinc-200">Ventas últimos 12 meses</h3>
    <div id="sales-chart" class="h-72"></div>

    <script>
        (() => {
            const mountChart = () => {
                const el = document.querySelector('#sales-chart');
                if (!el || typeof ApexCharts === 'undefined') {
                    return;
                }

                if (el.dataset.ready === '1') {
                    return;
                }

                const rows = @json($series);
                const chart = new ApexCharts(el, {
                    chart: { type: 'bar', height: 280, toolbar: { show: false } },
                    series: [{ name: 'Ventas', data: rows.map(r => Number(r.total)) }],
                    xaxis: { categories: rows.map(r => r.mes) },
                    colors: ['#0f766e'],
                    dataLabels: { enabled: false },
                });

                chart.render();
                el.dataset.ready = '1';
            };

            if (typeof ApexCharts === 'undefined') {
                const script = document.createElement('script');
                script.src = 'https://cdn.jsdelivr.net/npm/apexcharts';
                script.onload = mountChart;
                document.head.appendChild(script);
            } else {
                mountChart();
            }
        })();
    </script>
</div>
