<div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900" wire:ignore>
    <h3 class="mb-3 text-sm font-semibold text-zinc-800 dark:text-zinc-200">Estado del inventario</h3>
    <div id="inventory-chart" class="h-72"></div>

    <script>
        (() => {
            const mountChart = () => {
                const el = document.querySelector('#inventory-chart');
                if (!el || typeof ApexCharts === 'undefined') {
                    return;
                }

                if (el.dataset.ready === '1') {
                    return;
                }

                const data = @json($distribution);
                const chart = new ApexCharts(el, {
                    chart: { type: 'donut', height: 280 },
                    series: [Number(data.normal), Number(data.bajo_minimo), Number(data.sin_stock)],
                    labels: ['Stock normal', 'Bajo mínimo', 'Sin stock'],
                    colors: ['#16a34a', '#f59e0b', '#dc2626'],
                    legend: { position: 'bottom' },
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
