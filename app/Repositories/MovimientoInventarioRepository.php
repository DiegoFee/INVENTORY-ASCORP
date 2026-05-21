<?php

namespace App\Repositories;

use App\Models\MovimientoInventario;
use App\Models\Producto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * // Autor: Diego Méndez - Fecha: 21/05/2026
 */
class MovimientoInventarioRepository implements MovimientoInventarioRepositoryInterface
{
    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function create(array $data): MovimientoInventario
    {
        return MovimientoInventario::query()->create($data);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function paginate(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return MovimientoInventario::query()
            ->with(['producto', 'usuario'])
            ->when($search, function (Builder $query, string $search): void {
                $query->whereHas('producto', function (Builder $query) use ($search): void {
                    $query->where('nombre', 'like', '%'.$search.'%')
                        ->orWhere('sku', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function paginateByProducto(Producto $producto, ?string $tipo, int $perPage = 15): LengthAwarePaginator
    {
        return MovimientoInventario::query()
            ->with(['usuario', 'compra'])
            ->where('producto_id', $producto->getKey())
            ->when($tipo, function (Builder $query, string $tipo): void {
                $query->where('tipo', $tipo);
            })
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function getRecent(int $limit = 10): Collection
    {
        return MovimientoInventario::query()
            ->with('producto')
            ->latest('id')
            ->limit($limit)
            ->get();
    }
}
