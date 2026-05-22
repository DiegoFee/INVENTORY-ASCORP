<?php

namespace App\Repositories;

use App\Enums\CompraEstadoEnum;
use App\Models\Compra;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * // Autor: Diego Méndez - Fecha: 21/05/2026
 */
class CompraRepository implements CompraRepositoryInterface
{
    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function paginateForList(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return Compra::query()
            ->with('proveedor')
            ->when($search, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('codigo', 'like', '%'.$search.'%')
                        ->orWhereHas('proveedor', function (Builder $query) use ($search): void {
                            $query->where('nombre', 'like', '%'.$search.'%');
                        });
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function findWithRelations(Compra $compra): Compra
    {
        return $compra->load(['proveedor', 'detalles.producto']);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function create(array $data, array $detalles): Compra
    {
        return DB::transaction(function () use ($data, $detalles): Compra {
            $compra = Compra::query()->create($data);

            $compra->detalles()->createMany($detalles);

            return $this->findWithRelations($compra);
        });
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function update(Compra $compra, array $data, array $detalles): Compra
    {
        return DB::transaction(function () use ($compra, $data, $detalles): Compra {
            $compra->update($data);

            $compra->detalles()->delete();
            $compra->detalles()->createMany($detalles);

            return $this->findWithRelations($compra->refresh());
        });
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function updateStatus(Compra $compra, string|CompraEstadoEnum $estado, ?string $fechaRecepcion = null): Compra
    {
        $compra->update([
            'estado' => $estado,
            'fecha_recepcion' => $fechaRecepcion,
        ]);

        return $this->findWithRelations($compra->refresh());
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function delete(Compra $compra): void
    {
        $compra->delete();
    }
}
