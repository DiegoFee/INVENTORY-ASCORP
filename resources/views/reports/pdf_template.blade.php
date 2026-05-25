{{-- /** Autor: Celvin Arandi, Fecha: 25/05/2026, Descripcion: Plantilla base PDF para reportes de INVENTORY-ASCORP. */ --}}
@php
    $title = $report['title'] ?? 'Reporte';
    $filters = $report['filters'] ?? [];
    $rows = $report['rows'] ?? collect();
    $totals = $report['totals'] ?? [];
    $generatedAt = $generatedAt ?? now();
    $rows = $rows instanceof \Illuminate\Support\Collection ? $rows : collect($rows);
    $movimientosCriticos = $report['movimientos_criticos'] ?? collect();
    $movimientosCriticos = $movimientosCriticos instanceof \Illuminate\Support\Collection
        ? $movimientosCriticos
        : collect($movimientosCriticos);
    $hasFilters = collect($filters)
        ->filter(fn ($value) => $value !== null && $value !== '' && $value !== false)
        ->isNotEmpty();
    $formatMoney = fn (float $value): string => 'Q '.number_format($value, 2);
    $formatDate = fn ($value): string => $value ? \Carbon\Carbon::parse($value)->format('d/m/Y') : '-';
@endphp
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page { margin: 24px 24px 60px 24px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #333; padding: 6px 8px; }
        th { background: #f2f2f2; text-align: left; }
        .header-table td { border: 0; padding: 0; }
        .meta { font-size: 10px; color: #555; }
        .text-right { text-align: right; }
        .no-border td { border: 0; }
        .section-title { margin: 16px 0 6px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>
    <table class="header-table" cellspacing="0" cellpadding="0">
        <tr>
            <td>
                <div><strong>INVENTORY-ASCORP</strong></div>
                <div class="meta">Reporte corporativo</div>
            </td>
            <td class="text-right">
                <div>{{ $title }}</div>
                <div class="meta">Generado: {{ $generatedAt->format('d/m/Y H:i') }}</div>
            </td>
        </tr>
    </table>

    @if ($hasFilters)
        <div class="section-title">Filtros aplicados</div>
        <table class="no-border">
            <tr>
                @if ($type === 'ventas')
                    <td><strong>Fecha inicio:</strong> {{ $filters['fecha_inicio'] ?? '-' }}</td>
                    <td><strong>Fecha fin:</strong> {{ $filters['fecha_fin'] ?? '-' }}</td>
                    <td><strong>Cliente:</strong> {{ $filters['cliente_id'] ?? '-' }}</td>
                    <td><strong>Tipo pago:</strong> {{ $filters['tipo_pago'] ?? '-' }}</td>
                @elseif ($type === 'inventario')
                    <td><strong>Stock bajo:</strong> {{ ($filters['stock_bajo'] ?? false) ? 'Si' : 'No' }}</td>
                    <td><strong>Producto:</strong> {{ $filters['producto_id'] ?? '-' }}</td>
                @elseif ($type === 'cxc')
                    <td><strong>Cliente:</strong> {{ $filters['cliente_id'] ?? '-' }}</td>
                    <td><strong>Estado:</strong> {{ $filters['estado'] ?? '-' }}</td>
                    <td><strong>Vencidos:</strong> {{ ($filters['vencidos'] ?? false) ? 'Si' : 'No' }}</td>
                @endif
            </tr>
        </table>
    @endif

    @if ($type === 'ventas')
        <div class="section-title">Detalle de ventas</div>
        <table>
            <thead>
                <tr>
                    <th>Fecha cierre</th>
                    <th>Cliente</th>
                    <th>Tipo pago</th>
                    <th>Estado</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $venta)
                    <tr>
                        <td>{{ $formatDate($venta->closed_at ?? $venta->created_at) }}</td>
                        <td>{{ $venta->cliente?->nombre ?? 'Sin cliente' }}</td>
                        <td>{{ $venta->tipo_pago ?? '-' }}</td>
                        <td>{{ $venta->estado }}</td>
                        <td class="text-right">{{ $formatMoney((float) $venta->total) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Sin registros para los filtros seleccionados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @elseif ($type === 'inventario')
        <div class="section-title">Stock actual</div>
        <table>
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Producto</th>
                    <th class="text-right">Stock actual</th>
                    <th class="text-right">Stock minimo</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $producto)
                    <tr>
                        <td>{{ $producto->sku }}</td>
                        <td>{{ $producto->nombre }}</td>
                        <td class="text-right">{{ $producto->stock_actual }}</td>
                        <td class="text-right">{{ $producto->stock_minimo }}</td>
                        <td>{{ $producto->stock_actual <= $producto->stock_minimo ? 'Bajo' : 'Normal' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Sin registros para los filtros seleccionados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        @if ($movimientosCriticos->isNotEmpty())
            <div class="section-title">Movimientos criticos recientes</div>
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Producto</th>
                        <th>Tipo</th>
                        <th>Origen</th>
                        <th class="text-right">Cantidad</th>
                        <th class="text-right">Stock nuevo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($movimientosCriticos->flatten(1) as $movimiento)
                        @php
                            $tipo = $movimiento->tipo instanceof \BackedEnum ? $movimiento->tipo->value : $movimiento->tipo;
                            $origen = $movimiento->origen instanceof \BackedEnum ? $movimiento->origen->value : $movimiento->origen;
                        @endphp
                        <tr>
                            <td>{{ $formatDate($movimiento->created_at) }}</td>
                            <td>{{ $movimiento->producto?->nombre ?? '-' }}</td>
                            <td>{{ $tipo ?? '-' }}</td>
                            <td>{{ $origen ?? '-' }}</td>
                            <td class="text-right">{{ $movimiento->cantidad }}</td>
                            <td class="text-right">{{ $movimiento->stock_nuevo }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @elseif ($type === 'cxc')
        <div class="section-title">Detalle de cuentas por cobrar</div>
        <table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Venta</th>
                    <th>Vencimiento</th>
                    <th>Estado</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $cuenta)
                    <tr>
                        <td>{{ $cuenta->cliente?->nombre ?? 'Sin cliente' }}</td>
                        <td>#{{ $cuenta->venta_id }}</td>
                        <td>{{ $formatDate($cuenta->fecha_vencimiento) }}</td>
                        <td>{{ $cuenta->estado }}</td>
                        <td class="text-right">{{ $formatMoney((float) $cuenta->total) }}</td>
                        <td class="text-right">{{ $formatMoney((float) $cuenta->saldo) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Sin registros para los filtros seleccionados.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif

    <div class="section-title">Resumen</div>
    <table class="no-border">
        @if ($type === 'ventas')
            <tr>
                <td class="text-right"><strong>Total ventas:</strong></td>
                <td class="text-right">{{ $formatMoney((float) ($totals['total_ventas'] ?? 0)) }}</td>
            </tr>
            <tr>
                <td class="text-right"><strong>Ventas registradas:</strong></td>
                <td class="text-right">{{ $totals['cantidad'] ?? 0 }}</td>
            </tr>
        @elseif ($type === 'inventario')
            <tr>
                <td class="text-right"><strong>Total productos:</strong></td>
                <td class="text-right">{{ $totals['total_productos'] ?? 0 }}</td>
            </tr>
            <tr>
                <td class="text-right"><strong>Total stock:</strong></td>
                <td class="text-right">{{ $totals['total_stock'] ?? 0 }}</td>
            </tr>
        @elseif ($type === 'cxc')
            <tr>
                <td class="text-right"><strong>Total saldo:</strong></td>
                <td class="text-right">{{ $formatMoney((float) ($totals['total_saldo'] ?? 0)) }}</td>
            </tr>
            <tr>
                <td class="text-right"><strong>Cuentas registradas:</strong></td>
                <td class="text-right">{{ $totals['cantidad'] ?? 0 }}</td>
            </tr>
        @endif
    </table>

    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_text(520, 820, "Pagina {PAGE_NUM} de {PAGE_COUNT}", null, 9, [0, 0, 0]);
        }
    </script>
</body>
</html>
