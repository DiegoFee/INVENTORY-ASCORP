<?php

use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Livewire\Volt\Volt;

// Autor: Sistema - Fecha: 21/05/2026
// Descripcion: Crea una venta via HTTP POST y verifica persistencia completa.
test('admin can create sale via http post', function () {
    $admin = User::factory()->admin()->create();
    $producto = Producto::factory()->create([
        'precio_venta' => 100,
    ]);

    $payload = [
        'cliente_id' => null,
        'descuento' => 10,
        'estado' => Venta::EstadoBorrador,
        'observaciones' => 'Venta test',
        'detalles' => [
            [
                'producto_id' => $producto->id,
                'cantidad' => 2,
                'precio_unitario' => 100,
                'descuento' => 0,
            ],
        ],
    ];

    $response = $this->actingAs($admin)->post(route('ventas.store'), $payload);

    $venta = Venta::query()->first();

    $response->assertRedirect(route('ventas.show', $venta));

    expect($venta)->not->toBeNull()
        ->and($venta->caja_id)->not->toBeNull()
        ->and($venta->user_id)->toBe($admin->id)
        ->and((float) $venta->total)->toBe(190.0)
        ->and($venta->detalles()->count())->toBe(1)
        ->and($venta->detalles()->first()->producto_id)->toBe($producto->id);
});

// Autor: Sistema - Fecha: 21/05/2026
// Descripcion: Crea una venta via Livewire Volt con carrito asociativo.
test('admin can create sale via livewire with auto price', function () {
    $admin = User::factory()->admin()->create();
    $producto = Producto::factory()->create([
        'precio_venta' => 100,
    ]);

    $this->actingAs($admin);

    Volt::test('ventas.form')
        ->set('estado', Venta::EstadoBorrador)
        ->set('descuento', '10')
        ->set('cart', [
            $producto->id => [
                'id' => $producto->id,
                'name' => $producto->nombre,
                'price' => (float) $producto->precio_venta,
                'qty' => 2,
            ],
        ])
        ->call('save')
        ->assertRedirect();

    $venta = Venta::query()->first();

    expect($venta)->not->toBeNull()
        ->and($venta->caja_id)->not->toBeNull()
        ->and($venta->user_id)->toBe($admin->id)
        ->and((float) $venta->total)->toBe(190.0)
        ->and($venta->detalles()->count())->toBe(1)
        ->and($venta->detalles()->first()->producto_id)->toBe($producto->id);
});

// Autor: Sistema - Fecha: 21/05/2026
// Descripcion: Verifica que addProduct() carga precio y nombre del producto.
test('addProduct sets price and name from producto', function () {
    $admin = User::factory()->admin()->create();
    $producto = Producto::factory()->create([
        'precio_venta' => 75.50,
    ]);

    $this->actingAs($admin);

    $component = Volt::test('ventas.form')
        ->call('addProduct', $producto->id);

    $cart = $component->get('cart');

    expect($cart)->toHaveKey($producto->id)
        ->and((float) ($cart[$producto->id]['price'] ?? 0))->toBe(75.50)
        ->and($cart[$producto->id]['name'])->toBe($producto->nombre)
        ->and((int) ($cart[$producto->id]['qty'] ?? 0))->toBe(1);
});

// Autor: Sistema - Fecha: 21/05/2026
// Descripcion: addProduct incrementa qty si el producto ya existe en carrito.
test('addProduct increments quantity when product already in cart', function () {
    $admin = User::factory()->admin()->create();
    $producto = Producto::factory()->create();

    $this->actingAs($admin);

    $component = Volt::test('ventas.form')
        ->call('addProduct', $producto->id)
        ->call('addProduct', $producto->id);

    $cart = $component->get('cart');

    expect((int) ($cart[$producto->id]['qty'] ?? 0))->toBe(2);
});

// Autor: Sistema - Fecha: 21/05/2026
// Descripcion: Verifica que se asigna automaticamente una caja al crear venta.
test('sale always gets a valid caja_id', function () {
    $admin = User::factory()->admin()->create();
    $producto = Producto::factory()->create();

    $this->actingAs($admin);

    Volt::test('ventas.form')
        ->set('cart', [
            $producto->id => [
                'id' => $producto->id,
                'name' => $producto->nombre,
                'price' => (float) $producto->precio_venta,
                'qty' => 1,
            ],
        ])
        ->call('save')
        ->assertRedirect();

    $venta = Venta::query()->first();

    expect($venta)->not->toBeNull()
        ->and($venta->caja_id)->not->toBeNull()
        ->and($venta->caja)->not->toBeNull()
        ->and($venta->caja->user_id_open)->toBe($admin->id);
});

// Autor: Sistema - Fecha: 21/05/2026
// Descripcion: Verifica que el vendedor puede crear una venta.
test('seller can create sale', function () {
    $seller = User::factory()->seller()->create();
    $producto = Producto::factory()->create();

    $payload = [
        'estado' => Venta::EstadoBorrador,
        'detalles' => [
            [
                'producto_id' => $producto->id,
                'cantidad' => 1,
                'precio_unitario' => 50,
                'descuento' => 0,
            ],
        ],
    ];

    $this->actingAs($seller)
        ->post(route('ventas.store'), $payload)
        ->assertRedirect();

    expect(Venta::query()->count())->toBe(1);
});
