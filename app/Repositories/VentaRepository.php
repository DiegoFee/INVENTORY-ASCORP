<?php

namespace App\Repositories;

use App\Models\Venta;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class VentaRepository implements VentaRepositoryInterface
{
    public function paginateForList(?string $search, int $perPage = 10): LengthAwarePaginator
    {
        return Venta::query()
            ->with(['caja', 'usuario', 'cliente'])
            ->when($search, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('id', 'like', '%'.$search.'%')
                        ->orWhereHas('cliente', function (Builder $query) use ($search): void {
                            $query->where('nombre', 'like', '%'.$search.'%');
                        });
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function findWithRelations(Venta $venta): Venta
    {
        return $venta->load(['caja', 'usuario', 'cliente', 'detalles.producto']);
    }

    public function create(array $data, array $detalles): Venta
    {
        $venta = Venta::query()->create($data);
        $venta->detalles()->createMany($detalles);

        return $this->findWithRelations($venta);
    }

    public function update(Venta $venta, array $data, array $detalles): Venta
    {
        $venta->update($data);
        $venta->detalles()->delete();
        $venta->detalles()->createMany($detalles);

        return $this->findWithRelations($venta->refresh());
    }

    public function updateStatus(Venta $venta, string $estado, ?string $closedAt = null): Venta
    {
        $venta->update([
            'estado' => $estado,
            'closed_at' => $closedAt,
        ]);

        return $this->findWithRelations($venta->refresh());
    }

    public function delete(Venta $venta): void
    {
        $venta->delete();
    }
}
