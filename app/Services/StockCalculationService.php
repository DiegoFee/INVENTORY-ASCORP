<?php

namespace App\Services;

use App\Models\Producto;
use App\Repositories\ProductoRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * // Autor: Diego Méndez - Fecha: 20/05/2026
 */
class StockCalculationService
{
    public function __construct(private readonly ProductoRepositoryInterface $productos) {}

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function decreaseStock(Producto $producto, int $quantity): Producto
    {
        return DB::transaction(function () use ($producto, $quantity): Producto {
            $this->ensurePositiveQuantity($quantity);

            if (! $this->checkAvailability($producto, $quantity)) {
                throw ValidationException::withMessages([
                    'stock_actual' => 'Stock insuficiente para realizar la operacion.',
                ]);
            }

            return $this->productos->decreaseStock($producto, $quantity);
        });
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function increaseStock(Producto $producto, int $quantity): Producto
    {
        return DB::transaction(function () use ($producto, $quantity): Producto {
            $this->ensurePositiveQuantity($quantity);

            return $this->productos->increaseStock($producto, $quantity);
        });
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function checkAvailability(Producto $producto, int $quantity): bool
    {
        $this->ensurePositiveQuantity($quantity);

        $productoActualizado = $producto->refresh();

        return $productoActualizado->stock_actual >= $quantity;
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function calculateLowStockPrediction(int $currentStock, int $minimumStock, float $averageDailyUsage): ?int
    {
        if ($averageDailyUsage <= 0) {
            return null;
        }

        $available = $currentStock - $minimumStock;
        $days = (int) floor($available / $averageDailyUsage);

        return max(0, $days);
    }

    private function ensurePositiveQuantity(int $quantity): void
    {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'stock_actual' => 'La cantidad debe ser mayor a cero.',
            ]);
        }
    }
}
