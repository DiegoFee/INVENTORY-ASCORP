{{-- /** Autor: Arandi Hurtado, Fecha: 21/05/2026, Descripción: Plantilla PDF para nota de crédito de devolución. */ --}}
@php
    $venta = $devolucion->venta;
    $total = (float) $devolucion->monto;
    $formatMoney = fn (float $value): string => 'Q '.number_format($value, 2);
@endphp
{{-- Funcionamiento: define venta asociada, total y formato de moneda. Tablas: devoluciones, detalles_devolucion, ventas. Flujo: usa datos cargados desde controlador. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Nota de crédito #{{ $devolucion->id }}</title>
</head>
<body>
<table width="100%" cellspacing="0" cellpadding="6">
    <tr>
        <td>
            <strong>MaxCar</strong><br>
            Nota de crédito #{{ $devolucion->id }}<br>
            Venta asociada #{{ $venta?->id ?? 'N/A' }}
        </td>
        <td align="right">
            Fecha: {{ $devolucion->created_at?->format('d/m/Y') ?? now()->format('d/m/Y') }}
        </td>
    </tr>
</table>

<table width="100%" cellspacing="0" cellpadding="6" style="margin-top: 10px;">
    <tr>
        <td><strong>Cliente:</strong> {{ $venta?->cliente?->nombre ?? 'Consumidor final' }}</td>
    </tr>
    <tr>
        <td><strong>Motivo:</strong> {{ $devolucion->motivo ?? 'Sin motivo especificado' }}</td>
    </tr>
</table>

<table width="100%" cellspacing="0" cellpadding="6" style="margin-top: 16px;" border="1">
    <thead>
    <tr>
        <th align="left">Producto</th>
        <th align="right">Cantidad</th>
        <th align="right">Precio</th>
        <th align="right">Subtotal</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($devolucion->detalles as $detalle)
        <tr>
            <td>{{ $detalle->producto?->nombre ?? 'Producto eliminado' }}</td>
            <td align="right">{{ $detalle->cantidad }}</td>
            <td align="right">{{ $formatMoney((float) $detalle->precio_unitario) }}</td>
            <td align="right">{{ $formatMoney((float) $detalle->subtotal) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<table width="100%" cellspacing="0" cellpadding="6" style="margin-top: 12px;">
    <tr>
        <td align="right"><strong>Total nota de crédito:</strong></td>
        <td align="right" width="160"><strong>{{ $formatMoney($total) }}</strong></td>
    </tr>
</table>
</body>
</html>
