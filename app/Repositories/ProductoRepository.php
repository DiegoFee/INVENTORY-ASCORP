<?php

namespace App\Repositories;

use App\Models\Producto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * // Autor: Diego Méndez - Fecha: 20/05/2026
 */
class ProductoRepository implements ProductoRepositoryInterface
{
    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function findBySku(string $sku): ?Producto
    {
        return Producto::query()->where('sku', $sku)->first();
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function getLowStockProducts(int $limit = 10): Collection
    {
        return Producto::query()
            ->lowStock()
            ->active()
            ->orderBy('stock_actual')
            ->limit($limit)
            ->get();
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function updateStock(Producto $producto, int $stockActual): Producto
    {
        return DB::transaction(function () use ($producto, $stockActual): Producto {
            $lockedProducto = Producto::query()
                ->whereKey($producto->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $lockedProducto->update([
                'stock_actual' => max(0, $stockActual),
            ]);

            return $lockedProducto->refresh();
        });
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function decreaseStock(Producto $producto, int $quantity): Producto
    {
        $nuevoStock = $producto->stock_actual - $quantity;

        return $this->updateStock($producto, $nuevoStock);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function increaseStock(Producto $producto, int $quantity): Producto
    {
        $nuevoStock = $producto->stock_actual + $quantity;

        return $this->updateStock($producto, $nuevoStock);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function paginateForList(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return Producto::query()
            ->when($search, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('sku', 'like', '%'.$search.'%')
                        ->orWhere('nombre', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function create(array $data): Producto
    {
        return Producto::query()->create($data);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function update(Producto $producto, array $data): Producto
    {
        $producto->update($data);

        return $producto->refresh();
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function delete(Producto $producto): void
    {
        $producto->delete();
    }
}
