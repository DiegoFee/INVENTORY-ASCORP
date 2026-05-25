{{-- /** Autor: Arandi Hurtado, Fecha: 22/05/2026, Descripción: Inicialización de variables fiscales y cálculo de IVA para DomPDF. */ --}}
@php
    $nit = $venta->cliente?->nit ?? 'CF';
    $direccion = $venta->cliente?->direccion ?? 'Consumidor Final';
    $ivaTasa = (float) ($ivaTasa ?? 0.12);
    $total = (float) $venta->total;
    
    // Desglose del IVA en Quetzales (Q)
    $base = $ivaTasa > 0 ? $total / (1 + $ivaTasa) : $total;
    $iva = $total - $base;
    $formatMoney = fn (float $value): string => 'Q ' . number_format($value, 2);
    
    // Fallback seguro de fechas
    $fechaFactura = $venta->closed_at 
        ? \Carbon\Carbon::parse($venta->closed_at)->format('d/m/Y') 
        : \Carbon\Carbon::parse($venta->created_at)->format('d/m/Y');
@endphp
{{-- Funcionamiento: calcula base imponible, IVA y formato de moneda para factura. Tablas: ventas, detalles_venta, clientes. Flujo: usa datos de venta cargados desde controlador. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Factura #{{ $numero }}</title>
</head>
<body>
<table width="100%" cellspacing="0" cellpadding="6">
    <tr>
        <td>
            <strong>MaxCar</strong><br>
            Factura serie {{ $serie }}<br>
            No. {{ $numero }}
        </td>
        <td align="right">
            Fecha: {{ $venta->closed_at?->format('d/m/Y') ?? $venta->created_at?->format('d/m/Y') }}
        </td>
    </tr>
</table>

<table width="100%" cellspacing="0" cellpadding="6" style="margin-top: 10px;">
    <tr>
        <td><strong>Cliente:</strong> {{ $venta->cliente?->nombre ?? 'Consumidor final' }}</td>
        <td><strong>NIT:</strong> {{ $nit }}</td>
    </tr>
    <tr>
        <td colspan="2"><strong>Dirección:</strong> {{ $direccion }}</td>
    </tr>
</table>

<table width="100%" cellspacing="0" cellpadding="6" style="margin-top: 16px;" border="1">
    <thead>
    <tr>
        <th align="left">Producto</th>
        <th align="right">Cantidad</th>
        <th align="right">Precio</th>
        <th align="right">Descuento</th>
        <th align="right">Subtotal</th>
    </tr>
    </thead>
    <tbody>
    @foreach ($venta->detalles as $detalle)
        <tr>
            <td>{{ $detalle->producto?->nombre ?? 'Producto eliminado' }}</td>
            <td align="right">{{ $detalle->cantidad }}</td>
            <td align="right">{{ $formatMoney((float) $detalle->precio_unitario) }}</td>
            <td align="right">{{ $formatMoney((float) ($detalle->descuento ?? 0)) }}</td>
            <td align="right">{{ $formatMoney((float) $detalle->subtotal) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<table width="100%" cellspacing="0" cellpadding="6" style="margin-top: 12px;">
    <tr>
        <td align="right"><strong>Base imponible:</strong></td>
        <td align="right" width="140">{{ $formatMoney($base) }}</td>
    </tr>
    <tr>
        <td align="right"><strong>IVA ({{ number_format($ivaTasa * 100, 0) }}%):</strong></td>
        <td align="right">{{ $formatMoney($iva) }}</td>
    </tr>
    <tr>
        <td align="right"><strong>Total:</strong></td>
        <td align="right"><strong>{{ $formatMoney($total) }}</strong></td>
    </tr>
</table>
</body>
</html>
