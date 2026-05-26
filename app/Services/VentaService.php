<?php

/** Autor: Arandi Hurtado, Fecha: 21/05/2026, Descripción: Servicio de ventas con cierre y cuentas por cobrar. */

namespace App\Services;

use App\Models\Caja;
use App\Models\Cliente;
use App\Models\CuentaPorCobrar;
use App\Models\Venta;
use App\Repositories\VentaRepositoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VentaService
{
    public function __construct(
        private readonly VentaRepositoryInterface $ventas,
        private readonly InventoryMovementService $inventoryService
    ) {}

    /**
     * Funcionamiento: inyecta repositorio de ventas y servicio de inventario.
     */
    public function createVenta(array $data, array $detalles): Venta
    {
        return DB::transaction(function () use ($data, $detalles): Venta {
            $detalleNormalizado = $this->normalizarDetalles($detalles);
            $descuentoGlobal = (float) ($data['descuento'] ?? 0);
            $total = max(0, $this->calcularTotal($detalleNormalizado) - $descuentoGlobal);

            $caja = $this->resolveCajaParaUsuario((int) ($data['user_id'] ?? 0));

            $payload = array_merge($this->filtrarVentaData($data), [
                'caja_id' => $caja->id,
                'total' => $total,
                'estado' => $data['estado'] ?? Venta::EstadoBorrador,
                'opened_at' => $data['opened_at'] ?? now()->toDateTimeString(),
            ]);

            $venta = $this->ventas->create($payload, $detalleNormalizado);

            // Crear cuenta por cobrar si la venta es a crédito en la creación directa
            if (isset($data['tipo_pago']) && $data['tipo_pago'] === 'credito') {
                CuentaPorCobrar::create([
                    'venta_id' => $venta->id,
                    'cliente_id' => $venta->cliente_id,
                    'total' => $venta->total,
                    'saldo' => $venta->total,
                    'fecha_vencimiento' => now()->addDays(30),
                    'estado' => CuentaPorCobrar::EstadoPendiente,
                    'observaciones' => 'Cuenta de crédito generada al crear la venta.',
                ]);
            }

            return $venta;
        });
    }

    /**
     * Funcionamiento: actualiza la venta mientras no esté cerrada.
     */
    public function updateVenta(Venta $venta, array $data, array $detalles): Venta
    {
        if ($venta->estado === Venta::EstadoCerrada) {
            throw ValidationException::withMessages([
                'estado' => 'No se puede editar una venta cerrada.',
            ]);
        }

        return DB::transaction(function () use ($venta, $data, $detalles): Venta {
            $detalleNormalizado = $this->normalizarDetalles($detalles);
            $descuentoGlobal = (float) ($data['descuento'] ?? 0);
            $total = max(0, $this->calcularTotal($detalleNormalizado) - $descuentoGlobal);

            $payload = array_merge($this->filtrarVentaData($data), [
                'total' => $total,
                'estado' => $data['estado'] ?? $venta->estado,
            ]);

            return $this->ventas->update($venta, $payload, $detalleNormalizado);
        });
    }

    /**
     * Funcionamiento: confirma venta en borrador y carga relaciones.
     */
    public function confirmVenta(Venta $venta): Venta
    {
        if ($venta->estado !== Venta::EstadoBorrador) {
            return $this->ventas->findWithRelations($venta);
        }

        return $this->ventas->updateStatus(
            $venta,
            Venta::EstadoConfirmada,
            null
        );
    }

    /**
     * Funcionamiento: cierra la venta, descuenta inventario y asegura la cuenta por cobrar (solo créditos).
     */
    public function closeVenta(Venta $venta): Venta
    {
        if ($venta->estado !== Venta::EstadoConfirmada) {
            throw ValidationException::withMessages([
                'estado' => 'Solo se pueden cerrar ventas confirmadas.',
            ]);
        }

        return DB::transaction(function () use ($venta): Venta {
            $ventaConDetalles = $this->ventas->findWithRelations($venta);

            if ($ventaConDetalles->detalles->isEmpty()) {
                throw ValidationException::withMessages([
                    'detalles' => 'La venta no tiene detalles para cerrar.',
                ]);
            }

            foreach ($ventaConDetalles->detalles as $detalle) {
                $this->inventoryService->registerSalidaFromVenta($ventaConDetalles, $detalle);
            }

            $ventaCerrada = $this->ventas->updateStatus(
                $ventaConDetalles,
                Venta::EstadoCerrada,
                now()->toDateTimeString()
            );

            // CORRECCIÓN: Solo se genera cuenta por cobrar si el tipo de pago es crédito
            if ($ventaCerrada->tipo_pago === 'credito') {
                $this->ensureCuentaPorCobrar($ventaCerrada);
            }

            return $ventaCerrada;
        });
    }

    private function filtrarVentaData(array $data): array
    {
        return Arr::only($data, ['user_id', 'cliente_id', 'descuento', 'observaciones', 'tipo_pago']);
    }

    private function resolveCajaParaUsuario(int $userId): Caja
    {
        if ($userId < 1) {
            throw ValidationException::withMessages([
                'user_id' => 'Usuario no autenticado para resolver caja.',
            ]);
        }

        $caja = Caja::query()
            ->where('user_id_open', $userId)
            ->whereNull('closed_at')
            ->first();

        if ($caja !== null) {
            return $caja;
        }

        return Caja::query()->create([
            'user_id_open' => $userId,
            'opened_at' => now()->toDateTimeString(),
            'saldo_apertura' => 0,
            'status' => 'open',
        ]);
    }

    private function normalizarDetalles(array $detalles): array
    {
        $normalizados = collect($detalles)
            ->filter(fn (array $detalle): bool => (int) ($detalle['producto_id'] ?? 0) > 0)
            ->map(function (array $detalle): array {
                $cantidad = max(1, (int) ($detalle['cantidad'] ?? 0));
                $precioUnitario = (float) ($detalle['precio_unitario'] ?? 0);
                $descuento = (float) ($detalle['descuento'] ?? 0);
                $subtotal = $cantidad * $precioUnitario - $descuento;

                return [
                    'producto_id' => (int) $detalle['producto_id'],
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'descuento' => $descuento,
                    'subtotal' => max(0, $subtotal),
                ];
            })
            ->values()
            ->all();

        if ($normalizados === []) {
            throw ValidationException::withMessages([
                'detalles' => 'Debes agregar al menos un producto a la venta.',
            ]);
        }

        return $normalizados;
    }

    private function calcularTotal(array $detalles): float
    {
        return (float) collect($detalles)->sum('subtotal');
    }

    /**
     * Autor: Celvin Arandi
     * Descripción: Asegura la existencia de una cuenta por cobrar al cerrar la venta comercial.
     */
    public function ensureCuentaPorCobrar(Venta $venta): CuentaPorCobrar
    {
        $existing = CuentaPorCobrar::query()->where('venta_id', $venta->getKey())->first();

        if ($existing !== null) {
            return $existing;
        }

        // Estrategia de seguridad física para MySQL:
        // Si no hay cliente_id, buscamos el primero o creamos un Consumidor Final comodín.
        $clienteId = $venta->cliente_id;

        if (! $clienteId) {
            $clienteComodin = Cliente::query()->firstOrCreate(
                ['nit' => 'CF'],
                [
                    'nombre' => 'Consumidor Final',
                    'telefono' => '00000000',
                    'direccion' => 'Ciudad',
                ]
            );
            $clienteId = $clienteComodin->id;
        }

        return CuentaPorCobrar::query()->create([
            'venta_id' => $venta->getKey(),
            'cliente_id' => $clienteId,
            'total' => (float) $venta->total,
            'saldo' => (float) $venta->total,
            'fecha_vencimiento' => now()->addDays(30),
            'estado' => CuentaPorCobrar::EstadoPendiente,
            'observaciones' => 'Cuenta generada automáticamente al cerrar la venta comercial en Quetzales.',
        ]);
    }
}
