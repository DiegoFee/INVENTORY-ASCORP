<?php

namespace App\Services;

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

    public function createVenta(array $data, array $detalles): Venta
    {
        return DB::transaction(function () use ($data, $detalles): Venta {
            $detalleNormalizado = $this->normalizarDetalles($detalles);
            $descuentoGlobal = (float) ($data['descuento'] ?? 0);
            $total = max(0, $this->calcularTotal($detalleNormalizado) - $descuentoGlobal);

            $payload = array_merge($this->filtrarVentaData($data), [
                'total' => $total,
                'estado' => $data['estado'] ?? Venta::EstadoBorrador,
                'opened_at' => $data['opened_at'] ?? now()->toDateTimeString(),
            ]);

            return $this->ventas->create($payload, $detalleNormalizado);
        });
    }

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

            return $this->ventas->updateStatus(
                $ventaConDetalles,
                Venta::EstadoCerrada,
                now()->toDateTimeString()
            );
        });
    }

    private function filtrarVentaData(array $data): array
    {
        return Arr::only($data, ['caja_id', 'user_id', 'cliente_id', 'descuento', 'observaciones']);
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
}
