<?php

use App\Models\Caja;
use App\Models\Cliente;
use App\Models\CuentaPorCobrar;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;

// Autor: Celvin Arandi - Fecha: 25/05/2026 - Descripcion: Exporta reporte de ventas en PDF.
test('admin can export ventas report pdf', function () {
    $admin = User::factory()->admin()->create();
    $cliente = Cliente::factory()->create();
    $caja = Caja::query()->create([
        'user_id_open' => $admin->id,
        'opened_at' => now(),
        'saldo_apertura' => 0,
        'status' => 'open',
    ]);

    Venta::query()->create([
        'caja_id' => $caja->id,
        'user_id' => $admin->id,
        'cliente_id' => $cliente->id,
        'total' => 250.50,
        'descuento' => 0,
        'estado' => Venta::EstadoCerrada,
        'tipo_pago' => 'contado',
        'opened_at' => now()->subDay(),
        'closed_at' => now(),
    ]);

    $response = $this->actingAs($admin)->get(route('reports.export', [
        'type' => 'ventas',
        'fecha_inicio' => now()->subDays(2)->toDateString(),
        'fecha_fin' => now()->toDateString(),
    ]));

    $response->assertSuccessful()->assertHeader('content-type', 'application/pdf');
});

// Autor: Celvin Arandi - Fecha: 25/05/2026 - Descripcion: Exporta reporte de inventario en PDF.
test('admin can export inventario report pdf', function () {
    $admin = User::factory()->admin()->create();
    $producto = Producto::factory()->create([
        'stock_actual' => 3,
        'stock_minimo' => 5,
    ]);

    MovimientoInventario::query()->create([
        'producto_id' => $producto->id,
        'tipo' => MovimientoInventario::TipoSalida,
        'origen' => MovimientoInventario::OrigenVenta,
        'cantidad' => 2,
        'stock_anterior' => 5,
        'stock_nuevo' => 3,
        'costo_unitario' => 45,
    ]);

    $response = $this->actingAs($admin)->get(route('reports.export', [
        'type' => 'inventario',
        'stock_bajo' => 1,
    ]));

    $response->assertSuccessful()->assertHeader('content-type', 'application/pdf');
});

// Autor: Celvin Arandi - Fecha: 25/05/2026 - Descripcion: Exporta reporte de cuentas por cobrar en PDF.
test('admin can export cxc report pdf', function () {
    $admin = User::factory()->admin()->create();
    $cliente = Cliente::factory()->create();
    $caja = Caja::query()->create([
        'user_id_open' => $admin->id,
        'opened_at' => now(),
        'saldo_apertura' => 0,
        'status' => 'open',
    ]);

    $venta = Venta::query()->create([
        'caja_id' => $caja->id,
        'user_id' => $admin->id,
        'cliente_id' => $cliente->id,
        'total' => 300,
        'descuento' => 0,
        'estado' => Venta::EstadoCerrada,
        'tipo_pago' => 'credito',
        'opened_at' => now()->subDays(5),
        'closed_at' => now()->subDays(4),
    ]);

    CuentaPorCobrar::query()->create([
        'venta_id' => $venta->id,
        'cliente_id' => $cliente->id,
        'total' => 300,
        'saldo' => 150,
        'fecha_vencimiento' => now()->subDays(2)->toDateString(),
        'estado' => 'pendiente',
    ]);

    $response = $this->actingAs($admin)->get(route('reports.export', [
        'type' => 'cxc',
        'vencidos' => 1,
    ]));

    $response->assertSuccessful()->assertHeader('content-type', 'application/pdf');
});
