<?php

use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Services\InventoryMovementService;

// Autor: Diego Méndez - Fecha: 21/05/2026 - Descripcion: Registra una entrada y actualiza el stock.
test('registers inventory entry and updates stock', function () {
    $producto = Producto::factory()->create([
        'stock_actual' => 5,
    ]);

    $service = app(InventoryMovementService::class);

    $service->registerEntrada([
        'producto_id' => $producto->id,
        'cantidad' => 4,
        'costo_unitario' => 8.5,
        'observaciones' => 'Entrada manual',
    ]);

    expect($producto->fresh()->stock_actual)->toBe(9);
    $this->assertDatabaseHas('movimientos_inventario', [
        'producto_id' => $producto->id,
        'tipo' => MovimientoInventario::TipoEntrada,
        'cantidad' => 4,
    ]);
});

// Autor: Diego Méndez - Fecha: 21/05/2026 - Descripcion: Registra una salida y actualiza el stock.
test('registers inventory exit and updates stock', function () {
    $producto = Producto::factory()->create([
        'stock_actual' => 10,
    ]);

    $service = app(InventoryMovementService::class);

    $service->registerSalida([
        'producto_id' => $producto->id,
        'cantidad' => 3,
        'observaciones' => 'Salida manual',
    ]);

    expect($producto->fresh()->stock_actual)->toBe(7);
    $this->assertDatabaseHas('movimientos_inventario', [
        'producto_id' => $producto->id,
        'tipo' => MovimientoInventario::TipoSalida,
        'cantidad' => 3,
    ]);
});
