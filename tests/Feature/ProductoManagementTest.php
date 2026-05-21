<?php

use App\Models\Producto;
use App\Models\User;

// Autor: Diego Méndez - Fecha: 20/05/2026 - Descripcion: Permite crear un producto.
test('admin can create product', function () {
    $admin = User::factory()->admin()->create();

    $payload = [
        'sku' => 'SKU-1000',
        'nombre' => 'Producto Demo',
        'stock_actual' => 15,
        'stock_minimo' => 5,
        'precio_costo' => 10.5,
        'precio_venta' => 15.75,
        'unidad_medida' => 'unidad',
        'categoria' => 'General',
        'activo' => true,
    ];

    $response = $this->actingAs($admin)->post(route('productos.store'), $payload);

    $producto = Producto::query()->where('sku', 'SKU-1000')->first();

    $response->assertRedirect(route('productos.show', $producto));
    $this->assertDatabaseHas('productos', [
        'sku' => 'SKU-1000',
        'nombre' => 'Producto Demo',
    ]);
});

// Autor: Diego Méndez - Fecha: 20/05/2026 - Descripcion: Permite actualizar el stock de un producto.
test('warehouse can update product stock', function () {
    $warehouse = User::factory()->warehouse()->create();
    $producto = Producto::factory()->create([
        'stock_actual' => 8,
    ]);

    $payload = [
        'sku' => $producto->sku,
        'nombre' => $producto->nombre,
        'stock_actual' => 15,
        'stock_minimo' => $producto->stock_minimo,
        'precio_costo' => $producto->precio_costo,
        'precio_venta' => $producto->precio_venta,
        'unidad_medida' => $producto->unidad_medida,
        'categoria' => $producto->categoria,
        'activo' => $producto->activo,
    ];

    $response = $this->actingAs($warehouse)->put(route('productos.update', $producto), $payload);

    $response->assertRedirect(route('productos.show', $producto));
    expect($producto->fresh()->stock_actual)->toBe(15);
});

// Autor: Diego Méndez - Fecha: 20/05/2026 - Descripcion: Permite eliminar un producto.
test('admin can delete product', function () {
    $admin = User::factory()->admin()->create();
    $producto = Producto::factory()->create();

    $this->actingAs($admin)
        ->delete(route('productos.destroy', $producto))
        ->assertRedirect(route('productos.index'));

    $this->assertSoftDeleted('productos', [
        'id' => $producto->id,
    ]);
});
