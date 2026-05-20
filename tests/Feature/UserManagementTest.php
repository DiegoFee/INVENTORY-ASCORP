<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('admin can create user', function () {
    $admin = User::factory()->admin()->create();
    $role = Role::query()->where('name', Role::Seller)->firstOrFail();

    $response = $this->actingAs($admin)->post(route('users.store'), [
        'name' => 'Usuario de ventas',
        'email' => 'ventas.nuevo@ascorp.test',
        'password' => 'password',
        'role_id' => $role->id,
        'is_active' => true,
    ]);

    $createdUser = User::query()->where('email', 'ventas.nuevo@ascorp.test')->first();

    $response->assertRedirect(route('users.show', $createdUser));
    $this->assertDatabaseHas('users', [
        'email' => 'ventas.nuevo@ascorp.test',
        'role_id' => $role->id,
        'is_active' => true,
    ]);
    expect(Hash::check('password', $createdUser->password))->toBeTrue();
});

test('seller cannot access user management', function () {
    $seller = User::factory()->seller()->create();

    $this->actingAs($seller)
        ->get(route('users.index'))
        ->assertForbidden();
});

test('duplicate email fails validation', function () {
    $admin = User::factory()->admin()->create();
    $existingUser = User::factory()->seller()->create([
        'email' => 'duplicado@ascorp.test',
    ]);

    $this->actingAs($admin)
        ->from(route('users.create'))
        ->post(route('users.store'), [
            'name' => 'Usuario duplicado',
            'email' => $existingUser->email,
            'password' => 'password',
            'role_id' => $existingUser->role_id,
            'is_active' => true,
        ])
        ->assertRedirect(route('users.create'))
        ->assertSessionHasErrors('email');
});

test('user cannot delete themselves', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->delete(route('users.destroy', $admin))
        ->assertForbidden();

    $this->assertModelExists($admin);
});

test('admin cannot change their own role', function () {
    $admin = User::factory()->admin()->create();
    $sellerRole = Role::query()->where('name', Role::Seller)->firstOrFail();

    $this->actingAs($admin)
        ->from(route('users.edit', $admin))
        ->put(route('users.update', $admin), [
            'name' => $admin->name,
            'email' => $admin->email,
            'password' => null,
            'role_id' => $sellerRole->id,
            'is_active' => true,
        ])
        ->assertRedirect(route('users.edit', $admin))
        ->assertSessionHasErrors('role_id');

    expect($admin->fresh()->role_id)->not->toBe($sellerRole->id);
});
