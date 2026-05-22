{{-- /** Autor: Arandi Hurtado, Fecha: 21/05/2026, Descripción: Plantilla PDF de comprobante de entrega. */ --}}
@php
    $total = (float) $venta->total;
    $formatMoney = fn (float $value): string => 'Q '.number_format($value, 2);
@endphp
{{-- Funcionamiento: define total y formato de moneda para comprobante. Tablas: ventas, detalles_venta. Flujo: usa datos de venta cargados desde controlador. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante #{{ $venta->id }}</title>
</head>
<body>
<table width="100%" cellspacing="0" cellpadding="6">
    <tr>
        <td>
            <strong>MaxCar</strong><br>
            Comprobante de entrega<br>
            Venta #{{ $venta->id }}
        </td>
        <td align="right">
            Fecha: {{ $venta->closed_at?->format('d/m/Y') ?? $venta->created_at?->format('d/m/Y') }}
        </td>
    </tr>
</table>

<table width="100%" cellspacing="0" cellpadding="6" style="margin-top: 10px;">
    <tr>
        <td><strong>Cliente:</strong> {{ $venta->cliente?->nombre ?? 'Consumidor final' }}</td>
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
    @foreach ($venta->detalles as $detalle)
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
        <td align="right"><strong>Total:</strong></td>
        <td align="right" width="140"><strong>{{ $formatMoney($total) }}</strong></td>
    </tr>
</table>
</body>
</html>
