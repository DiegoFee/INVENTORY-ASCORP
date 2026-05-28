<?php

use App\Models\Role;
use App\Models\User;
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
    $venta = \App\Models\Venta::factory()->for($admin, 'usuario')->create();

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
