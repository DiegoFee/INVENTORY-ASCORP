<?php

use App\Models\Cliente;
use App\Models\User;
use Livewire\Volt\Volt;

// Autor: Celvin
// Descripcion: Permite que un admin visite el listado de clientes.
test('admin can view clients index', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('clientes.index'))
        ->assertSuccessful();
});

// Autor: Celvin
// Descripcion: Restringe el acceso al modulo de clientes para vendedores.
test('seller cannot access clients module', function () {
    $seller = User::factory()->seller()->create();

    $this->actingAs($seller)
        ->get(route('clientes.index'))
        ->assertForbidden();
});

// Autor: Celvin
// Descripcion: Permite crear un cliente desde el formulario Volt.
test('admin can create client', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin);

    Volt::test('clientes.create')
        ->set('nit', 'CF')
        ->set('nombre', 'Cliente Demo')
        ->set('direccion', 'Zona 1')
        ->set('telefono', '5551234')
        ->call('save')
        ->assertRedirect(route('clientes.index'));

    $this->assertDatabaseHas('clientes', [
        'nit' => 'CF',
        'nombre' => 'Cliente Demo',
    ]);
});

// Autor: Celvin
// Descripcion: Permite actualizar los datos de un cliente.
test('admin can update client', function () {
    $admin = User::factory()->admin()->create();
    $cliente = Cliente::factory()->create([
        'nit' => '12345',
        'nombre' => 'Cliente Original',
    ]);

    $this->actingAs($admin);

    Volt::test('clientes.edit', ['cliente' => $cliente->id])
        ->set('nombre', 'Cliente Actualizado')
        ->call('update')
        ->assertRedirect(route('clientes.index'));

    expect($cliente->fresh()->nombre)->toBe('Cliente Actualizado');
});

// Autor: Celvin
// Descripcion: Permite eliminar un cliente desde el listado.
test('admin can delete client', function () {
    $admin = User::factory()->admin()->create();
    $cliente = Cliente::factory()->create();

    $this->actingAs($admin);

    Volt::test('clientes.index')
        ->call('delete', $cliente->id);

    $this->assertDatabaseMissing('clientes', [
        'id' => $cliente->id,
    ]);
});
