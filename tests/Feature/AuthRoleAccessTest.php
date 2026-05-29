<?php

use App\Models\Caja;
use App\Models\Devolucion;
use App\Models\Producto;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Venta;
use Database\Seeders\RoleSeeder;
use Illuminate\Auth\Access\AuthorizationException;
use Livewire\Volt\Volt;

test('seller can access sales dashboard', function () {
    $user = User::factory()->seller()->create();

    $this->actingAs($user)
        ->get(route('sales.dashboard'))
        ->assertSuccessful()
        ->assertSee('Panel de ventas');
});

test('seller cannot access warehouse dashboard', function () {
    $user = User::factory()->seller()->create();

    $this->actingAs($user)
        ->get(route('inventory.dashboard'))
        ->assertForbidden();
});

test('warehouse user can access inventory dashboard', function () {
    $user = User::factory()->warehouse()->create();

    $this->actingAs($user)
        ->get(route('inventory.dashboard'))
        ->assertSuccessful()
        ->assertSee('Panel de bodega');
});

test('role seeder creates the required auth roles', function () {
    $this->seed(RoleSeeder::class);

    expect(Role::query()->pluck('name')->all())
        ->toContain(Role::Admin, Role::Seller, Role::Warehouse);
});

test('admin can access every protected module entry point', function () {
    $admin = User::factory()->admin()->create();

    $routes = [
        'dashboard',
        'sales.dashboard',
        'inventory.dashboard',
        'ventas.index',
        'devoluciones.index',
        'inventario.index',
        'compras.index',
        'productos.index',
        'suppliers.index',
        'cxc.index',
        'reports.ventas',
        'reports.inventario',
        'reports.cxc',
        'users.index',
        'clientes.index',
        'foso.index',
        'alertas.index',
    ];

    foreach ($routes as $routeName) {
        $this->actingAs($admin)
            ->get(route($routeName))
            ->assertSuccessful();
    }
});

test('seller only sees allowed commercial modules in the navigation', function () {
    $seller = User::factory()->seller()->create();

    $this->actingAs($seller)
        ->get(route('sales.dashboard'))
        ->assertSuccessful()
        ->assertSee('Ventas')
        ->assertSee('Devoluciones')
        ->assertDontSee('Ventas Comerciales')
        ->assertDontSee('Movimientos Stock')
        ->assertDontSee('Saldos CxC')
        ->assertDontSee('Cuentas por Cobrar')
        ->assertDontSee('Inventario')
        ->assertDontSee('Compras')
        ->assertDontSee('Productos')
        ->assertDontSee('Proveedores')
        ->assertDontSee('Servicios Foso')
        ->assertDontSee('Usuarios')
        ->assertDontSee('Clientes');
});

test('seller is blocked from restricted modules by direct url', function () {
    $seller = User::factory()->seller()->create();

    $forbiddenRoutes = [
        'inventory.dashboard',
        'inventario.index',
        'compras.index',
        'productos.index',
        'suppliers.index',
        'cxc.index',
        'reports.ventas',
        'reports.inventario',
        'reports.cxc',
        'users.index',
        'clientes.index',
        'foso.index',
    ];

    foreach ($forbiddenRoutes as $routeName) {
        $this->actingAs($seller)
            ->get(route($routeName))
            ->assertForbidden();
    }
});

test('seller is blocked from restricted ajax and export requests', function () {
    $seller = User::factory()->seller()->create();

    $this->actingAs($seller)
        ->getJson(route('cxc.index'))
        ->assertForbidden();

    foreach (['ventas', 'inventario', 'cxc'] as $type) {
        $this->actingAs($seller)
            ->get(route('reports.export', ['type' => $type]))
            ->assertForbidden();
    }
});

test('seller cannot delete sales even if the delete route is submitted manually', function () {
    $seller = User::factory()->seller()->create();
    $admin = User::factory()->admin()->create();
    $venta = Venta::factory()->for($admin, 'usuario')->create();

    $this->actingAs($seller)
        ->delete(route('ventas.destroy', $venta))
        ->assertForbidden();
});

test('warehouse user only sees allowed inventory modules in the navigation', function () {
    $warehouse = User::factory()->warehouse()->create();

    $this->actingAs($warehouse)
        ->get(route('inventory.dashboard'))
        ->assertSuccessful()
        ->assertSee('Inventario')
        ->assertSee('Compras')
        ->assertSee('Productos')
        ->assertSee('Proveedores')
        ->assertDontSee('Servicios Foso')
        ->assertDontSee('Ventas')
        ->assertDontSee('Devoluciones')
        ->assertDontSee('Ventas Comerciales')
        ->assertDontSee('Movimientos Stock')
        ->assertDontSee('Saldos CxC')
        ->assertDontSee('Cuentas por Cobrar')
        ->assertDontSee('Usuarios')
        ->assertDontSee('Clientes');
});

test('warehouse user is blocked from commercial financial admin and foso modules by direct url', function () {
    $warehouse = User::factory()->warehouse()->create();

    $forbiddenRoutes = [
        'sales.dashboard',
        'ventas.index',
        'devoluciones.index',
        'cxc.index',
        'reports.ventas',
        'reports.inventario',
        'reports.cxc',
        'users.index',
        'clientes.index',
        'foso.index',
    ];

    foreach ($forbiddenRoutes as $routeName) {
        $this->actingAs($warehouse)
            ->get(route($routeName))
            ->assertForbidden();
    }
});

test('warehouse user cannot bypass foso through livewire actions', function () {
    $warehouse = User::factory()->warehouse()->create();

    $this->actingAs($warehouse);

    expect(fn () => Volt::test('fosoform'))
        ->toThrow(AuthorizationException::class);
});

test('seller cannot create product', function () {
    $seller = User::factory()->seller()->create();

    $this->actingAs($seller)
        ->get(route('productos.create'))
        ->assertForbidden();

    $this->actingAs($seller)
        ->post(route('productos.store'), [
            'nombre' => 'Test',
            'stock_actual' => 0,
            'stock_minimo' => 5,
        ])->assertForbidden();
});

test('seller cannot update or delete product', function () {
    $admin = User::factory()->admin()->create();
    $seller = User::factory()->seller()->create();
    $producto = Producto::factory()->create();

    $this->actingAs($seller)
        ->get(route('productos.edit', $producto))
        ->assertForbidden();

    $this->actingAs($seller)
        ->put(route('productos.update', $producto), [
            'nombre' => 'Updated',
            'stock_actual' => $producto->stock_actual,
            'stock_minimo' => $producto->stock_minimo,
        ])->assertForbidden();

    $this->actingAs($seller)
        ->delete(route('productos.destroy', $producto))
        ->assertForbidden();
});

test('seller cannot create compra', function () {
    $seller = User::factory()->seller()->create();

    $this->actingAs($seller)
        ->get(route('compras.create'))
        ->assertForbidden();
});

test('seller cannot manage supplier', function () {
    $seller = User::factory()->seller()->create();
    $supplier = Supplier::factory()->create();

    $this->actingAs($seller)
        ->get(route('suppliers.create'))
        ->assertForbidden();

    $this->actingAs($seller)
        ->delete(route('suppliers.destroy', $supplier))
        ->assertForbidden();
});

test('seller cannot access inventario actions', function () {
    $seller = User::factory()->seller()->create();

    $this->actingAs($seller)
        ->get(route('inventario.entrada'))
        ->assertForbidden();

    $this->actingAs($seller)
        ->get(route('inventario.salida'))
        ->assertForbidden();
});

test('seller can view all devoluciones', function () {
    $seller1 = User::factory()->seller()->create();
    $seller2 = User::factory()->seller()->create();
    $admin = User::factory()->admin()->create();

    $caja = Caja::factory()->create([
        'user_id_open' => $admin->id,
        'status' => 'open',
        'saldo_apertura' => 0,
        'opened_at' => now(),
    ]);

    // Venta owned by seller1
    $miVenta = Venta::query()->create([
        'caja_id' => $caja->id,
        'user_id' => $seller1->id,
        'total' => 100,
        'descuento' => 0,
        'estado' => Venta::EstadoConfirmada,
    ]);
    // Venta owned by seller2
    $ventaSeller2 = Venta::query()->create([
        'caja_id' => $caja->id,
        'user_id' => $seller2->id,
        'total' => 100,
        'descuento' => 0,
        'estado' => Venta::EstadoConfirmada,
    ]);
    // Venta owned by admin
    $ventaAdmin = Venta::query()->create([
        'caja_id' => $caja->id,
        'user_id' => $admin->id,
        'total' => 100,
        'descuento' => 0,
        'estado' => Venta::EstadoConfirmada,
    ]);

    // Devolucion created by admin for seller1's venta — seller1 should see it
    $devolucionMiVenta = Devolucion::query()->create([
        'venta_id' => $miVenta->id,
        'user_id' => $admin->id,
        'monto' => 50,
        'motivo' => 'Test',
        'estado' => Devolucion::EstadoPendiente,
    ]);

    // Devolucion created by seller2 for seller2's venta — seller1 should NOT see it
    $devolucionSeller2 = Devolucion::query()->create([
        'venta_id' => $ventaSeller2->id,
        'user_id' => $seller2->id,
        'monto' => 50,
        'motivo' => 'Test',
        'estado' => Devolucion::EstadoPendiente,
    ]);

    // Devolucion for admin's venta — seller1 should NOT see it
    $devolucionAdmin = Devolucion::query()->create([
        'venta_id' => $ventaAdmin->id,
        'user_id' => $seller2->id,
        'monto' => 50,
        'motivo' => 'Test',
        'estado' => Devolucion::EstadoPendiente,
    ]);

    $this->actingAs($seller1);

    // Seller can see any devolucion
    $this->get(route('devoluciones.show', $devolucionMiVenta))->assertOk();
    $this->get(route('devoluciones.show', $devolucionSeller2))->assertOk();
    $this->get(route('devoluciones.show', $devolucionAdmin))->assertOk();
});

test('warehouse user cannot view devoluciones', function () {
    $warehouse = User::factory()->warehouse()->create();
    $admin = User::factory()->admin()->create();
    $caja = Caja::query()->create([
        'user_id_open' => $admin->id,
        'status' => 'open',
        'saldo_apertura' => 0,
        'opened_at' => now(),
    ]);
    $venta = Venta::query()->create([
        'caja_id' => $caja->id,
        'user_id' => $admin->id,
        'total' => 100,
        'descuento' => 0,
        'estado' => Venta::EstadoConfirmada,
    ]);
    $devolucion = Devolucion::query()->create([
        'venta_id' => $venta->id,
        'user_id' => $admin->id,
        'monto' => 50,
        'motivo' => 'Test',
        'estado' => Devolucion::EstadoPendiente,
    ]);

    $this->actingAs($warehouse)
        ->get(route('devoluciones.show', $devolucion))
        ->assertForbidden();
});

test('warehouse user cannot create venta', function () {
    $warehouse = User::factory()->warehouse()->create();

    $this->actingAs($warehouse)
        ->get(route('ventas.create'))
        ->assertForbidden();
});

test('warehouse user cannot manage foso', function () {
    $warehouse = User::factory()->warehouse()->create();

    $this->actingAs($warehouse)
        ->get(route('foso.index'))
        ->assertForbidden();
});

test('unauthenticated user cannot access any protected route', function () {
    $protectedRoutes = [
        route('ventas.index'),
        route('inventario.index'),
        route('compras.index'),
        route('productos.index'),
        route('dashboard'),
    ];

    foreach ($protectedRoutes as $url) {
        $this->get($url)->assertRedirect(route('login'));
    }
});
