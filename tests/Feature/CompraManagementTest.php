<?php

use App\Models\Compra;
use App\Models\Producto;
use App\Models\Supplier;
use App\Models\User;

// Autor: Diego Méndez - Fecha: 21/05/2026 - Descripcion: Permite crear una compra con detalles.
test('admin can create purchase', function () {
    $admin = User::factory()->admin()->create();
    $supplier = Supplier::query()->create([
        'nombre' => 'Proveedor Demo',
        'nit' => '1234567-8',
        'telefono' => '5551234',
        'email' => 'proveedor.demo@ascorp.test',
        'direccion' => 'Zona 1',
        'contacto_nombre' => 'Contacto Demo',
    ]);
    $producto = Producto::factory()->create([
        'precio_costo' => 10.5,
        'precio_venta' => 12.5,
    ]);

    $payload = [
        'proveedor_id' => $supplier->id,
        'estado' => 'borrador',
        'fecha_compra' => now()->toDateString(),
        'observaciones' => 'Compra inicial',
        'detalles' => [
            [
                'producto_id' => $producto->id,
                'cantidad' => 3,
                'precio_costo' => 10.5,
            ],
        ],
    ];

    $response = $this->actingAs($admin)->post(route('compras.store'), $payload);

    $compra = Compra::query()->first();

    $response->assertRedirect(route('compras.show', $compra));
    $this->assertDatabaseHas('compras', [
        'id' => $compra->id,
        'proveedor_id' => $supplier->id,
        'estado' => Compra::EstadoBorrador,
    ]);
    $this->assertDatabaseHas('detalles_compra', [
        'compra_id' => $compra->id,
        'producto_id' => $producto->id,
        'cantidad' => 3,
    ]);
});

// Autor: Diego Méndez - Fecha: 21/05/2026 - Descripcion: Actualiza stock al recibir mercaderia.
test('warehouse can receive purchase and update stock', function () {
    $warehouse = User::factory()->warehouse()->create();
    $supplier = Supplier::query()->create([
        'nombre' => 'Proveedor Stock',
        'nit' => '9876543-2',
        'telefono' => '5555678',
        'email' => 'proveedor.stock@ascorp.test',
        'direccion' => 'Zona 2',
        'contacto_nombre' => 'Contacto Stock',
    ]);
    $producto = Producto::factory()->create([
        'stock_actual' => 5,
    ]);

    $compra = Compra::query()->create([
        'proveedor_id' => $supplier->id,
        'codigo' => 'OC-TEST-0001',
        'estado' => Compra::EstadoConfirmada,
        'fecha_compra' => now()->toDateString(),
        'total' => 40,
    ]);

    $compra->detalles()->create([
        'producto_id' => $producto->id,
        'cantidad' => 4,
        'precio_costo' => 10,
        'subtotal' => 40,
    ]);

    $this->actingAs($warehouse)
        ->patch(route('compras.receive', $compra))
        ->assertRedirect(route('compras.show', $compra));

    expect($producto->fresh()->stock_actual)->toBe(9)
        ->and($compra->fresh()->estado)->toBe(Compra::EstadoRecibida);
});
