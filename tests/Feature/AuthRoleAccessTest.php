<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;

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
