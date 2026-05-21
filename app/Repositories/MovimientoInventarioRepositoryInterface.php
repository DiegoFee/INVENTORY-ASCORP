<?php

namespace App\Repositories;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * // Autor: Diego Méndez - Fecha: 21/05/2026
 */
interface MovimientoInventarioRepositoryInterface
{
    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function create(array $data): MovimientoInventario;

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function paginate(?string $search, int $perPage = 10): LengthAwarePaginator;

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function paginateByProducto(Producto $producto, ?string $tipo, int $perPage = 15): LengthAwarePaginator;

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function getRecent(int $limit = 10): Collection;
}
