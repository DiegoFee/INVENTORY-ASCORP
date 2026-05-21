<?php

namespace App\Services;

use App\Models\Compra;
use App\Repositories\CompraRepositoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * // Autor: Diego Méndez - Fecha: 21/05/2026
 */
class CompraService
{
    public function __construct(
        private readonly CompraRepositoryInterface $compras,
        private readonly StockCalculationService $stockService
    ) {}

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function createCompra(array $data, array $detalles): Compra
    {
        return DB::transaction(function () use ($data, $detalles): Compra {
            $detalleNormalizado = $this->normalizarDetalles($detalles);
            $total = $this->calcularTotal($detalleNormalizado);

            $payload = array_merge($this->filtrarCompraData($data), [
                'codigo' => $this->generarCodigo(),
                'total' => $total,
                'estado' => $data['estado'] ?? Compra::EstadoBorrador,
                'fecha_compra' => $data['fecha_compra'] ?? now()->toDateString(),
            ]);

            return $this->compras->create($payload, $detalleNormalizado);
        });
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function updateCompra(Compra $compra, array $data, array $detalles): Compra
    {
        if ($compra->estado === Compra::EstadoRecibida) {
            throw ValidationException::withMessages([
                'estado' => 'No se puede editar una compra recibida.',
            ]);
        }

        return DB::transaction(function () use ($compra, $data, $detalles): Compra {
            $detalleNormalizado = $this->normalizarDetalles($detalles);
            $total = $this->calcularTotal($detalleNormalizado);

            $payload = array_merge($this->filtrarCompraData($data), [
                'total' => $total,
                'estado' => $data['estado'] ?? $compra->estado,
                'fecha_compra' => $data['fecha_compra'] ?? $compra->fecha_compra?->toDateString(),
            ]);

            return $this->compras->update($compra, $payload, $detalleNormalizado);
        });
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function confirmCompra(Compra $compra): Compra
    {
        if ($compra->estado !== Compra::EstadoBorrador) {
            return $this->compras->findWithRelations($compra);
        }

        return $this->compras->updateStatus(
            $compra,
            Compra::EstadoConfirmada,
            null
        );
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function receiveCompra(Compra $compra): Compra
    {
        if ($compra->estado !== Compra::EstadoConfirmada) {
            throw ValidationException::withMessages([
                'estado' => 'Solo se pueden recibir compras confirmadas.',
            ]);
        }

        return DB::transaction(function () use ($compra): Compra {
            $compraConDetalles = $this->compras->findWithRelations($compra);

            if ($compraConDetalles->detalles->isEmpty()) {
                throw ValidationException::withMessages([
                    'detalles' => 'La compra no tiene detalles para recibir.',
                ]);
            }

            foreach ($compraConDetalles->detalles as $detalle) {
                $this->stockService->increaseStock($detalle->producto, $detalle->cantidad);
            }

            return $this->compras->updateStatus(
                $compraConDetalles,
                Compra::EstadoRecibida,
                now()->toDateTimeString()
            );
        });
    }

    private function filtrarCompraData(array $data): array
    {
        return Arr::only($data, ['proveedor_id', 'observaciones']);
    }

    private function generarCodigo(): string
    {
        return 'OC-'.now()->format('Ymd').'-'.Str::upper(Str::random(4));
    }

    private function normalizarDetalles(array $detalles): array
    {
        $normalizados = collect($detalles)
            ->filter(fn (array $detalle): bool => (int) ($detalle['producto_id'] ?? 0) > 0)
            ->map(function (array $detalle): array {
                $cantidad = max(1, (int) ($detalle['cantidad'] ?? 0));
                $precio = (float) ($detalle['precio_costo'] ?? 0);
                $subtotal = $cantidad * $precio;

                return [
                    'producto_id' => (int) $detalle['producto_id'],
                    'cantidad' => $cantidad,
                    'precio_costo' => $precio,
                    'subtotal' => $subtotal,
                ];
            })
            ->values()
            ->all();

        if ($normalizados === []) {
            throw ValidationException::withMessages([
                'detalles' => 'Debes agregar al menos un producto a la compra.',
            ]);
        }

        return $normalizados;
    }

    private function calcularTotal(array $detalles): float
    {
        return (float) collect($detalles)->sum('subtotal');
    }
}
