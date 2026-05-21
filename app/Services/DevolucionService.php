<?php

namespace App\Services;

use App\Models\Devolucion;
use App\Repositories\DevolucionRepositoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DevolucionService
{
    public function __construct(
        private readonly DevolucionRepositoryInterface $devoluciones,
        private readonly InventoryMovementService $inventoryService
    ) {}

    public function createDevolucion(array $data, array $detalles): Devolucion
    {
        return DB::transaction(function () use ($data, $detalles): Devolucion {
            $detalleNormalizado = $this->normalizarDetalles($detalles);
            $monto = $this->calcularTotal($detalleNormalizado);

            $payload = array_merge($this->filtrarDevolucionData($data), [
                'monto' => $monto,
                'estado' => $data['estado'] ?? Devolucion::EstadoPendiente,
            ]);

            return $this->devoluciones->create($payload, $detalleNormalizado);
        });
    }

    public function updateDevolucion(Devolucion $devolucion, array $data, array $detalles): Devolucion
    {
        if ($devolucion->estado === Devolucion::EstadoProcesada) {
            throw ValidationException::withMessages([
                'estado' => 'No se puede editar una devolución procesada.',
            ]);
        }

        return DB::transaction(function () use ($devolucion, $data, $detalles): Devolucion {
            $detalleNormalizado = $this->normalizarDetalles($detalles);
            $monto = $this->calcularTotal($detalleNormalizado);

            $payload = array_merge($this->filtrarDevolucionData($data), [
                'monto' => $monto,
                'estado' => $data['estado'] ?? $devolucion->estado,
            ]);

            return $this->devoluciones->update($devolucion, $payload, $detalleNormalizado);
        });
    }

    public function procesarDevolucion(Devolucion $devolucion): Devolucion
    {
        if ($devolucion->estado !== Devolucion::EstadoPendiente) {
            throw ValidationException::withMessages([
                'estado' => 'Solo se pueden procesar devoluciones pendientes.',
            ]);
        }

        return DB::transaction(function () use ($devolucion): Devolucion {
            $devolucionConDetalles = $this->devoluciones->findWithRelations($devolucion);

            if ($devolucionConDetalles->detalles->isEmpty()) {
                throw ValidationException::withMessages([
                    'detalles' => 'La devolución no tiene detalles para procesar.',
                ]);
            }

            foreach ($devolucionConDetalles->detalles as $detalle) {
                $this->inventoryService->registerEntradaFromDevolucion($devolucionConDetalles, $detalle);
            }

            return $this->devoluciones->updateStatus(
                $devolucionConDetalles,
                Devolucion::EstadoProcesada
            );
        });
    }

    public function rejectDevolucion(Devolucion $devolucion, ?string $motivo = null): Devolucion
    {
        if ($devolucion->estado !== Devolucion::EstadoPendiente) {
            throw ValidationException::withMessages([
                'estado' => 'Solo se pueden rechazar devoluciones pendientes.',
            ]);
        }

        return $this->devoluciones->updateStatus(
            $devolucion,
            Devolucion::EstadoRechazada
        );
    }

    private function filtrarDevolucionData(array $data): array
    {
        return Arr::only($data, ['venta_id', 'user_id', 'motivo']);
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
                'detalles' => 'Debes agregar al menos un producto a la devolución.',
            ]);
        }

        return $normalizados;
    }

    private function calcularTotal(array $detalles): float
    {
        return (float) collect($detalles)->sum('subtotal');
    }
}
