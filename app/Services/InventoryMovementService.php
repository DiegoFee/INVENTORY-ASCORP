<?php

namespace App\Services;

use App\Models\Compra;
use App\Models\DetalleCompra;
use App\Models\DetalleDevolucion;
use App\Models\DetalleVenta;
use App\Models\Devolucion;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Venta;
use App\Repositories\MovimientoInventarioRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * // Autor: Diego Méndez - Fecha: 21/05/2026
 */
class InventoryMovementService
{
    public function __construct(
        private readonly MovimientoInventarioRepositoryInterface $movimientos,
        private readonly StockCalculationService $stockService
    ) {}

    public function registerEntrada(array $data): MovimientoInventario
    {
        return $this->registerMovement(
            productoId: (int) $data['producto_id'],
            cantidad: (int) $data['cantidad'],
            tipo: MovimientoInventario::TipoEntrada,
            origen: MovimientoInventario::OrigenManual,
            costoUnitario: $data['costo_unitario'] ?? null,
            compraId: null,
            userId: $data['user_id'] ?? null,
            observaciones: $data['observaciones'] ?? null,
        );
    }

    public function registerSalida(array $data): MovimientoInventario
    {
        return $this->registerMovement(
            productoId: (int) $data['producto_id'],
            cantidad: (int) $data['cantidad'],
            tipo: MovimientoInventario::TipoSalida,
            origen: MovimientoInventario::OrigenSalida,
            costoUnitario: $data['costo_unitario'] ?? null,
            compraId: null,
            userId: $data['user_id'] ?? null,
            observaciones: $data['observaciones'] ?? null,
        );
    }

    public function registerEntradaFromCompra(Compra $compra, DetalleCompra $detalle, ?int $userId = null): MovimientoInventario
    {
        return $this->registerMovement(
            productoId: $detalle->producto_id,
            cantidad: $detalle->cantidad,
            tipo: MovimientoInventario::TipoEntrada,
            origen: MovimientoInventario::OrigenCompra,
            costoUnitario: (float) $detalle->precio_costo,
            compraId: $compra->getKey(),
            userId: $userId,
            observaciones: 'Ingreso por compra '.$compra->codigo,
        );
    }

    public function registerSalidaFromVenta(Venta $venta, DetalleVenta $detalle, ?int $userId = null): MovimientoInventario
    {
        return $this->registerMovement(
            productoId: $detalle->producto_id,
            cantidad: $detalle->cantidad,
            tipo: MovimientoInventario::TipoSalida,
            origen: MovimientoInventario::OrigenVenta,
            costoUnitario: (float) $detalle->precio_unitario,
            compraId: null,
            userId: $userId,
            observaciones: 'Salida por venta #'.$venta->getKey(),
        );
    }

    public function registerEntradaFromDevolucion(Devolucion $devolucion, DetalleDevolucion $detalle, ?int $userId = null): MovimientoInventario
    {
        return $this->registerMovement(
            productoId: $detalle->producto_id,
            cantidad: $detalle->cantidad,
            tipo: MovimientoInventario::TipoEntrada,
            origen: MovimientoInventario::OrigenDevolucion,
            costoUnitario: (float) $detalle->precio_unitario,
            compraId: null,
            userId: $userId,
            observaciones: 'Ingreso por devolución #'.$devolucion->getKey(),
        );
    }

    private function registerMovement(
        int $productoId,
        int $cantidad,
        string $tipo,
        string $origen,
        ?float $costoUnitario,
        ?int $compraId,
        ?int $userId,
        ?string $observaciones,
    ): MovimientoInventario {
        return DB::transaction(function () use ($productoId, $cantidad, $tipo, $origen, $costoUnitario, $compraId, $userId, $observaciones): MovimientoInventario {
            if ($cantidad <= 0) {
                throw ValidationException::withMessages([
                    'cantidad' => 'La cantidad debe ser mayor a cero.',
                ]);
            }

            $producto = Producto::query()->whereKey($productoId)->firstOrFail();
            $stockAnterior = $producto->fresh()->stock_actual;

            if ($tipo === MovimientoInventario::TipoSalida) {
                $this->stockService->decreaseStock($producto, $cantidad);
            } else {
                $this->stockService->increaseStock($producto, $cantidad);
            }

            $stockNuevo = $producto->fresh()->stock_actual;

            return $this->movimientos->create([
                'producto_id' => $productoId,
                'compra_id' => $compraId,
                'user_id' => $userId,
                'tipo' => $tipo,
                'origen' => $origen,
                'cantidad' => $cantidad,
                'stock_anterior' => $stockAnterior,
                'stock_nuevo' => $stockNuevo,
                'costo_unitario' => $costoUnitario,
                'observaciones' => $observaciones,
            ]);
        });
    }
}
