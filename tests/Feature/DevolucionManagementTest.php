<?php

/** Autor: Arandi Hurtado, Fecha: 21/05/2026, Descripción: Pruebas de devoluciones con nota de credito y saldos. */

use App\Models\Caja;
use App\Models\CuentaPorCobrar;
use App\Models\Devolucion;
use App\Models\Producto;
use App\Models\User;
use App\Models\Venta;
use Livewire\Volt\Volt;

beforeEach(function (): void {
    $this->admin = User::factory()->admin()->create();
    $this->producto = Producto::factory()->create(['precio_venta' => 100]);
    $this->caja = Caja::query()->create([
        'user_id_open' => $this->admin->id,
        'status' => 'open',
        'saldo_apertura' => 0,
        'opened_at' => now(),
    ]);
    $this->venta = Venta::query()->create([
        'caja_id' => $this->caja->id,
        'user_id' => $this->admin->id,
        'total' => 200,
        'descuento' => 0,
        'estado' => Venta::EstadoConfirmada,
    ]);
    $this->venta->detalles()->create([
        'producto_id' => $this->producto->id,
        'cantidad' => 2,
        'precio_unitario' => 100,
        'descuento' => 0,
        'subtotal' => 200,
    ]);

    $this->actingAs($this->admin);
});

test('admin can create devolucion via livewire', function () {
    Volt::test('devoluciones.create')
        ->set('venta_id', (string) $this->venta->id)
        ->call('loadVentaDetalles')
        ->set('detalles.0.selected', true)
        ->set('detalles.0.cantidad', '1')
        ->set('motivo', 'Producto defectuoso')
        ->call('save')
        ->assertRedirect();

    $devolucion = Devolucion::query()->first();

    expect($devolucion)->not->toBeNull()
        ->and($devolucion->venta_id)->toBe($this->venta->id)
        ->and($devolucion->user_id)->toBe($this->admin->id)
        ->and($devolucion->estado)->toBe(Devolucion::EstadoPendiente)
        ->and((float) $devolucion->monto)->toBe(100.0)
        ->and($devolucion->motivo)->toBe('Producto defectuoso')
        ->and($devolucion->detalles()->count())->toBe(1)
        ->and($devolucion->detalles()->first()->producto_id)->toBe($this->producto->id)
        ->and($devolucion->detalles()->first()->cantidad)->toBe(1);
});

test('admin can create devolucion via http post', function () {
    $payload = [
        'venta_id' => $this->venta->id,
        'motivo' => 'Producto dañado',
        'detalles' => [
            [
                'producto_id' => $this->producto->id,
                'cantidad' => 1,
                'precio_unitario' => 100,
                'descuento' => 0,
            ],
        ],
    ];

    $this->post(route('devoluciones.store'), $payload)
        ->assertRedirect();

    $devolucion = Devolucion::query()->first();

    expect($devolucion)->not->toBeNull()
        ->and($devolucion->venta_id)->toBe($this->venta->id)
        ->and($devolucion->estado)->toBe(Devolucion::EstadoPendiente)
        ->and($devolucion->detalles()->count())->toBe(1);
});

test('admin can approve devolucion from show page', function () {
    $devolucion = crearDevolucion($this->venta, $this->admin, $this->producto);

    Volt::test('devoluciones.show', ['devolucionId' => $devolucion->id])
        ->call('approve')
        ->assertSet('devolucion.estado', Devolucion::EstadoProcesada);

    $devolucion->refresh();
    expect($devolucion->estado)->toBe(Devolucion::EstadoProcesada);
});

test('admin can reject devolucion from show page', function () {
    $devolucion = crearDevolucion($this->venta, $this->admin, $this->producto);

    Volt::test('devoluciones.show', ['devolucionId' => $devolucion->id])
        ->call('reject')
        ->assertSet('devolucion.estado', Devolucion::EstadoRechazada);

    $devolucion->refresh();
    expect($devolucion->estado)->toBe(Devolucion::EstadoRechazada);
});

test('approve updates stock via inventory movement', function () {
    $devolucion = crearDevolucion($this->venta, $this->admin, $this->producto);
    $stockAntes = $this->producto->fresh()->stock_actual ?? 0;

    Volt::test('devoluciones.show', ['devolucionId' => $devolucion->id])
        ->call('approve');

    $producto = $this->producto->fresh();
    expect((int) $producto->stock_actual)->toBe($stockAntes + 1);
});

test('devolucion list shows created entries', function () {
    crearDevolucion($this->venta, $this->admin, $this->producto);

    Volt::test('devoluciones.list')
        ->assertSee('Pendiente')
        ->assertSee((string) $this->venta->id);
});

test('validation prevents create without selected productos', function () {
    Volt::test('devoluciones.create')
        ->set('venta_id', (string) $this->venta->id)
        ->call('loadVentaDetalles')
        ->call('save')
        ->assertHasErrors('detalles');
});

test('create route returns 200 not 404', function () {
    $this->get(route('devoluciones.create'))
        ->assertStatus(200)
        ->assertSee('Nueva devolución')
        ->assertSee('Seleccionar venta');
});

test('show page renders devolucion with detalles', function () {
    $devolucion = crearDevolucion($this->venta, $this->admin, $this->producto);

    $this->get(route('devoluciones.show', $devolucion))
        ->assertStatus(200)
        ->assertSee("Devoluci\u{00f3}n #{$devolucion->id}")
        ->assertSee($this->producto->nombre)
        ->assertSee('Pendiente')
        ->assertSee(number_format(100, 2))
        ->assertDontSee('No hay detalles registrados');
});

test('approve creates cuenta por cobrar adjustment', function () {
    $devolucion = crearDevolucion($this->venta, $this->admin, $this->producto);

    Volt::test('devoluciones.show', ['devolucionId' => $devolucion->id])
        ->call('approve');

    $cuenta = CuentaPorCobrar::query()
        ->where('venta_id', $this->venta->id)
        ->first();

    expect($cuenta)->not->toBeNull()
        ->and((float) $cuenta->saldo)->toBe(100.0)
        ->and($cuenta->estado)->toBe(CuentaPorCobrar::EstadoPendiente);
});

test('admin can download nota credito pdf', function () {
    $devolucion = crearDevolucion($this->venta, $this->admin, $this->producto);

    Volt::test('devoluciones.show', ['devolucionId' => $devolucion->id])
        ->call('approve');

    $this->get(route('devoluciones.nota-credito', $devolucion))
        ->assertSuccessful()
        ->assertHeader('content-type', 'application/pdf');
});

function crearDevolucion(Venta $venta, User $user, Producto $producto): Devolucion
{
    $devolucion = Devolucion::query()->create([
        'venta_id' => $venta->id,
        'user_id' => $user->id,
        'monto' => 100,
        'motivo' => 'Test',
        'estado' => Devolucion::EstadoPendiente,
    ]);
    $devolucion->detalles()->create([
        'producto_id' => $producto->id,
        'cantidad' => 1,
        'precio_unitario' => 100,
        'descuento' => 0,
        'subtotal' => 100,
    ]);

    return $devolucion->fresh();
}
