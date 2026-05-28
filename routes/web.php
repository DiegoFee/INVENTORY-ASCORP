<?php

/** Autor: Arandi Hurtado, Fecha: 21/05/2026, Descripción: Definicion de rutas web incluyendo PDFs de ventas y devoluciones. */

use App\Enums\Permission;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\CxcController;
use App\Http\Controllers\DevolucionController;
use App\Http\Controllers\FosoController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::resource('suppliers', SupplierController::class)
    ->middleware(['auth', 'can:'.Permission::SuppliersView->value]);

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified', 'can:'.Permission::DashboardView->value])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
    Volt::route('settings/password', 'settings.password')->name('settings.password');
    Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');

    Volt::route('alertas', 'alertas.historial')
        ->middleware('can:'.Permission::AlertasView->value)
        ->name('alertas.index');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard/ventas', 'dashboard', [
        'title' => 'Panel de ventas',
        'description' => 'Acceso inicial para vendedores y cajeros.',
    ])->middleware('can:'.Permission::SalesDashboardView->value)->name('sales.dashboard');

    Route::view('dashboard/bodega', 'dashboard', [
        'title' => 'Panel de bodega',
        'description' => 'Acceso inicial para inventario y productos.',
    ])->middleware('can:'.Permission::InventoryDashboardView->value)->name('inventory.dashboard');
});

Route::middleware(['auth', 'can:'.Permission::UsersView->value])->group(function () {
    Route::patch('users/{user}/status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::resource('users', UserController::class);
});

Route::middleware(['auth', 'can:'.Permission::ClientsView->value])->group(function () {
    Volt::route('clientes', 'clientes.index')->name('clientes.index');
    Volt::route('clientes/create', 'clientes.create')->name('clientes.create');
    Volt::route('clientes/{cliente}/edit', 'clientes.edit')->name('clientes.edit');
});

Route::middleware(['auth', 'can:'.Permission::VentasView->value])->group(function () {
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
Route::middleware(['auth', 'can:'.Permission::DevolucionesView->value])->group(function () {
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
Route::middleware(['auth', 'can:'.Permission::InventarioView->value])->group(function () {
    Route::get('inventario', [InventarioController::class, 'index'])->name('inventario.index');
    Route::get('inventario/entrada', [InventarioController::class, 'entrada'])->name('inventario.entrada');
    Route::get('inventario/salida', [InventarioController::class, 'salida'])->name('inventario.salida');
    Route::get('inventario/kardex/{producto}', [InventarioController::class, 'kardex'])->name('inventario.kardex');
    Route::resource('compras', CompraController::class);
    Route::patch('compras/{compra}/receive', [CompraController::class, 'receive'])->name('compras.receive');
    Route::resource('productos', ProductoController::class);
});

Route::middleware(['auth', 'can:'.Permission::CxcView->value])->group(function () {
    Route::get('/cxc', [CxcController::class, 'index'])->name('cxc.index');
    Route::get('/cxc/{cuenta}', [CxcController::class, 'show'])->name('cxc.show');
    Route::post('/cxc/{cuenta}/abono', [CxcController::class, 'storeAbono'])
        ->middleware('can:'.Permission::CxcUpdate->value)
        ->name('cxc.abono.store');
});

/**
 * Funcionamiento: rutas de reportes con exportacion PDF.
 * Tablas: ventas, productos, movimientos_inventario, cuentas_por_cobrar.
 * Flujo: expone formularios de filtros y descarga reportes.
 */
Route::middleware(['auth'])->group(function () {
    Route::get('reports/ventas', [ReportController::class, 'ventas'])
        ->middleware('can:'.Permission::ReportsVentasView->value)
        ->name('reports.ventas');
    Route::get('reports/inventario', [ReportController::class, 'inventario'])
        ->middleware('can:'.Permission::ReportsInventarioView->value)
        ->name('reports.inventario');
    Route::get('reports/cxc', [ReportController::class, 'cxc'])
        ->middleware('can:'.Permission::ReportsCxcView->value)
        ->name('reports.cxc');
    Route::get('reports/{type}/pdf', [ReportController::class, 'exportPdf'])
        ->middleware('can:'.Permission::ReportsExport->value)
        ->where('type', 'ventas|inventario|cxc')
        ->name('reports.export');
});

Route::middleware(['auth', 'can:'.Permission::FosoView->value])->group(function () {
    Route::resource('foso', FosoController::class)->except(['edit', 'update'])->parameter('foso', 'servicio');
    Route::patch('foso/{servicio}/close', [FosoController::class, 'close'])->name('foso.close');
    Route::get('foso/{servicio}/comprobante', [FosoController::class, 'comprobante'])->name('foso.comprobante');
});

require __DIR__.'/auth.php';
