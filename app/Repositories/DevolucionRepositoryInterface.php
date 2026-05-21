<?php

namespace App\Repositories;

use App\Models\Devolucion;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface DevolucionRepositoryInterface
{
    public function paginateForList(?string $search, int $perPage = 10): LengthAwarePaginator;

    public function findWithRelations(Devolucion $devolucion): Devolucion;

    public function create(array $data, array $detalles): Devolucion;

    public function update(Devolucion $devolucion, array $data, array $detalles): Devolucion;

    public function updateStatus(Devolucion $devolucion, string $estado): Devolucion;

    public function delete(Devolucion $devolucion): void;
}
