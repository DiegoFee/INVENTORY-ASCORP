<?php

use App\Models\Producto;
use App\Services\StockCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

// Autor: Diego Méndez - Fecha: 20/05/2026 - Descripcion: Reduce el stock cuando existe disponibilidad.
test('decreases stock when available', function () {
    $producto = Producto::factory()->create([
        'stock_actual' => 10,
        'stock_minimo' => 2,
    ]);

    $service = app(StockCalculationService::class);

    $service->decreaseStock($producto, 4);

    expect($producto->fresh()->stock_actual)->toBe(6);
});

// Autor: Diego Méndez - Fecha: 20/05/2026 - Descripcion: Bloquea la disminucion cuando el stock es insuficiente.
test('prevents stock decrease when insufficient', function () {
    $producto = Producto::factory()->create([
        'stock_actual' => 2,
        'stock_minimo' => 1,
    ]);

    $service = app(StockCalculationService::class);

    expect(fn () => $service->decreaseStock($producto, 5))
        ->toThrow(ValidationException::class);
});

// Autor: Diego Méndez - Fecha: 20/05/2026 - Descripcion: Calcula la prediccion de stock minimo.
test('calculates low stock prediction', function () {
    $service = app(StockCalculationService::class);

    expect($service->calculateLowStockPrediction(100, 20, 10.0))->toBe(8)
        ->and($service->calculateLowStockPrediction(10, 20, 5.0))->toBe(0)
        ->and($service->calculateLowStockPrediction(10, 5, 0.0))->toBeNull();
});
