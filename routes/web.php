<?php

/** Autor: Arandi Hurtado, Fecha: 21/05/2026, Descripción: Definicion de rutas web incluyendo PDFs de ventas y devoluciones. */

use App\Http\Controllers\CompraController;
use App\Http\Controllers\CxcController;
use App\Http\Controllers\DevolucionController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VentaController;
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
    Volt::route('alertas', 'alertas.index')->name('alertas.index');

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

Route::middleware(['auth', 'role:Admin,Vendedor'])->group(function () {
    Route::get('ventas/{venta}/factura', [VentaController::class, 'factura'])->name('ventas.factura');
    Route::get('ventas/{venta}/comprobante', [VentaController::class, 'comprobante'])->name('ventas.comprobante');
    Route::resource('ventas', VentaController::class)->only(['index', 'show', 'store', 'update', 'destroy', 'create', 'edit']);
    Route::patch('ventas/{venta}/close', [VentaController::class, 'close'])->name('ventas.close');
});

/**
 * Funcionamiento: rutas de ventas con generacion de factura y comprobante en PDF.
 * Tablas: ventas, detalles_venta, cuenta_por_cobrar.
 * Flujo: expone endpoints protegidos para ver, editar, cerrar y exportar ventas.
 */
Route::middleware(['auth', 'role:Admin,Vendedor'])->group(function () {
    Volt::route('devoluciones/create', 'devoluciones.create')->name('devoluciones.create');
    Route::resource('devoluciones', DevolucionController::class)->only(['index', 'show', 'store']);
    Route::get('devoluciones/{devolucion}/nota-credito', [DevolucionController::class, 'notaCredito'])->name('devoluciones.nota-credito');
    Route::patch('devoluciones/{devolucion}/approve', [DevolucionController::class, 'approve'])->name('devoluciones.approve');
    Route::patch('devoluciones/{devolucion}/reject', [DevolucionController::class, 'reject'])->name('devoluciones.reject');
});

/**
 * Funcionamiento: rutas de devoluciones con nota de credito en PDF.
 * Tablas: devoluciones, detalles_devolucion, cuenta_por_cobrar.
 * Flujo: expone endpoints protegidos para crear, aprobar y exportar notas de credito.
 */
Route::middleware(['auth', 'role:Admin,Bodeguero'])->group(function () {
    Route::get('inventario', [InventarioController::class, 'index'])->name('inventario.index');
    Route::get('inventario/entrada', [InventarioController::class, 'entrada'])->name('inventario.entrada');
    Route::get('inventario/salida', [InventarioController::class, 'salida'])->name('inventario.salida');
    Route::get('inventario/kardex/{producto}', [InventarioController::class, 'kardex'])->name('inventario.kardex');
    Route::resource('compras', CompraController::class);
    Route::patch('compras/{compra}/receive', [CompraController::class, 'receive'])->name('compras.receive');
    Route::resource('productos', ProductoController::class);
});

Route::get('/cxc', [CxcController::class, 'index'])->name('cxc.index')->middleware('auth');
Route::get('/cxc/{cuenta}', [CxcController::class, 'show'])->name('cxc.show')->middleware('auth');
Route::post('/cxc/{cuenta}/abono', [CxcController::class, 'storeAbono'])->name('cxc.abono.store')->middleware('auth');
require __DIR__.'/auth.php';
