<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Comprobante de Servicio Foso</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .details { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        .total { text-align: right; margin-top: 20px; font-size: 1.2em; }
    </style>
</head>
<body>
    <div class="header">
        <h2>INVENTORY ASCORP</h2>
        <h3>Comprobante de Servicio de Foso #{{ $servicio->id }}</h3>
        <p>Fecha: {{ $servicio->fecha->format('d/m/Y H:i') }}</p>
    </div>
    <div class="details">
        <p><strong>Cliente:</strong> {{ $servicio->cliente->nombre }}</p>
        <p><strong>Placa vehículo:</strong> {{ $servicio->placa_vehiculo }}</p>
        <p><strong>Técnico:</strong> {{ $servicio->tecnico->name }}</p>
        <p><strong>Estado:</strong> {{ ucfirst($servicio->estado) }}</p>
    </div>
    <table>
        <thead><tr><th>Producto</th><th>Cantidad</th><th>Precio unitario</th><th>Subtotal</th></tr></thead>
        <tbody>
            @foreach($servicio->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->producto->nombre }}</td>
                    <td>{{ $detalle->cantidad }}</td>
                    <td>Q{{ number_format($detalle->precio_unitario, 2) }}</td>
                    <td>Q{{ number_format($detalle->subtotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="total">
        <strong>Total: Q{{ number_format($servicio->total, 2) }}</strong>
    </div>
    <div style="margin-top: 50px; text-align: center; font-size: 0.8em;">
        Gracias por su preferencia
    </div>
</body>
</html>