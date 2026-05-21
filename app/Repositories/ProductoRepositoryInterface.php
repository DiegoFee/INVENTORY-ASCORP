<?php

namespace App\Repositories;

use App\Models\Producto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * // Autor: Diego Méndez - Fecha: 20/05/2026
 */
interface ProductoRepositoryInterface
{
    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function findBySku(string $sku): ?Producto;

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function getLowStockProducts(int $limit = 10): Collection;

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function updateStock(Producto $producto, int $stockActual): Producto;

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function decreaseStock(Producto $producto, int $quantity): Producto;

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function increaseStock(Producto $producto, int $quantity): Producto;

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function paginateForList(?string $search, int $perPage = 10): LengthAwarePaginator;

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function create(array $data): Producto;

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function update(Producto $producto, array $data): Producto;

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function delete(Producto $producto): void;
}
