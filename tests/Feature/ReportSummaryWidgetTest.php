<?php

use App\Livewire\Dashboard\ReportSummaryWidget;
use App\Models\CuentaPorCobrar;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

test('calcula el valor total del inventario', function () {
    Producto::factory()->create([
        'precio_venta' => 10,
        'stock_actual' => 3,
    ]);

    Producto::factory()->create([
        'precio_venta' => 5,
        'stock_actual' => 4,
    ]);

    $valor = Livewire::test(ReportSummaryWidget::class)
        ->get('valorTotalInventario');

    expect($valor)->toBe(50.0);
});

test('calcula ventas del mes actual con comparativo', function () {
    Carbon::setTestNow(Carbon::create(2026, 5, 26, 10, 0, 0));

    Venta::factory()->create([
        'total' => 200,
        'created_at' => Carbon::now()->startOfMonth()->addDays(2),
    ]);

    Venta::factory()->create([
        'total' => 100,
        'created_at' => Carbon::now()->startOfMonth()->addDays(10),
    ]);

    Venta::factory()->create([
        'total' => 150,
        'created_at' => Carbon::now()->subMonth()->startOfMonth()->addDays(5),
    ]);

    $ventas = Livewire::test(ReportSummaryWidget::class)
        ->get('ventasMesActual');

    expect($ventas['total'] ?? null)->toBe(300.0)
        ->and($ventas['change'] ?? null)->toBe(100.0)
        ->and($ventas['trend'] ?? null)->toBe('up');

    Carbon::setTestNow();
});

test('suma saldos cxc por vencer en 7 dias', function () {
    Carbon::setTestNow(Carbon::create(2026, 5, 26, 10, 0, 0));

    CuentaPorCobrar::factory()->create([
        'saldo' => 120,
        'fecha_vencimiento' => Carbon::today()->addDays(3),
        'estado' => CuentaPorCobrar::EstadoPendiente,
    ]);

    CuentaPorCobrar::factory()->create([
        'saldo' => 80,
        'fecha_vencimiento' => Carbon::today()->addDays(7),
        'estado' => CuentaPorCobrar::EstadoPendiente,
    ]);

    CuentaPorCobrar::factory()->create([
        'saldo' => 60,
        'fecha_vencimiento' => Carbon::today()->addDays(8),
        'estado' => CuentaPorCobrar::EstadoPendiente,
    ]);

    CuentaPorCobrar::factory()->create([
        'saldo' => 40,
        'fecha_vencimiento' => Carbon::today()->addDays(5),
        'estado' => CuentaPorCobrar::EstadoPagada,
    ]);

    $total = Livewire::test(ReportSummaryWidget::class)
        ->get('saldosCxcPorVencer');

    expect($total)->toBe(240.0);

    Carbon::setTestNow();
});
