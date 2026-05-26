<?php

use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

test('authenticated users can visit the dashboard', function () {
    $user = User::factory()->admin()->create();
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200)
        ->assertSee('wire:poll.60s', false);
});

test('seller can visit the dashboard', function () {
    $user = User::factory()->seller()->create();
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});

test('warehouse user can visit the dashboard', function () {
    $user = User::factory()->warehouse()->create();
    $this->actingAs($user);

    $response = $this->get('/dashboard');
    $response->assertStatus(200);
});
