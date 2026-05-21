<?php

use App\Http\Controllers\CompraController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::resource('suppliers', SupplierController::class)->middleware('auth');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', 'role:Admin'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard/ventas', 'dashboard', [
        'title' => 'Panel de ventas',
        'description' => 'Acceso inicial para vendedores y cajeros.',
    ])->middleware('role:Admin,Vendedor')->name('sales.dashboard');

    Route::view('dashboard/bodega', 'dashboard', [
        'title' => 'Panel de bodega',
        'description' => 'Acceso inicial para inventario y productos.',
    ])->middleware('role:Admin,Bodeguero')->name('inventory.dashboard');
});

Route::middleware(['auth', 'role:Admin'])->group(function () {
    Route::patch('users/{user}/status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::resource('users', UserController::class);

    Volt::route('clientes', 'clientes.index')->name('clientes.index');
    Volt::route('clientes/create', 'clientes.create')->name('clientes.create');
    Volt::route('clientes/{cliente}/edit', 'clientes.edit')->name('clientes.edit');
});

Route::middleware(['auth', 'role:Admin,Bodeguero'])->group(function () {
    Route::resource('compras', CompraController::class);
    Route::patch('compras/{compra}/receive', [CompraController::class, 'receive'])->name('compras.receive');
    Route::resource('productos', ProductoController::class);
});

require __DIR__.'/auth.php';
