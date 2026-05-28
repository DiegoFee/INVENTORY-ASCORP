<?php

namespace App\Repositories;

use App\Models\Devolucion;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class DevolucionRepository implements DevolucionRepositoryInterface
{
    public function paginateForList(?string $search, ?User $user = null, int $perPage = 10): LengthAwarePaginator
    {
        return Devolucion::query()
            ->with(['venta', 'usuario'])
            ->whereOwnedBy($user ?? auth()->user())
            ->when($search, function (Builder $query, string $search): void {
                $query->where(function (Builder $query) use ($search): void {
                    $query->where('id', 'like', '%'.$search.'%');
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function findWithRelations(Devolucion $devolucion): Devolucion
    {
        return $devolucion->load(['venta', 'usuario', 'detalles.producto']);
    }

    public function create(array $data, array $detalles): Devolucion
    {
        $devolucion = Devolucion::query()->create($data);
        $devolucion->detalles()->createMany($detalles);

        return $this->findWithRelations($devolucion);
    }

    public function update(Devolucion $devolucion, array $data, array $detalles): Devolucion
    {
        $devolucion->update($data);
        $devolucion->detalles()->delete();
        $devolucion->detalles()->createMany($detalles);

        return $this->findWithRelations($devolucion->refresh());
    }

    public function updateStatus(Devolucion $devolucion, string $estado): Devolucion
    {
        $devolucion->update([
            'estado' => $estado,
        ]);

        return $this->findWithRelations($devolucion->refresh());
    }

    public function delete(Devolucion $devolucion): void
    {
        $devolucion->delete();
    }
}
