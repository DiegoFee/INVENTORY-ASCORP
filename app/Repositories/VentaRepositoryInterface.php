<?php

namespace App\Repositories;

use App\Models\Venta;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface VentaRepositoryInterface
{
    public function paginateForList(?string $search, int $perPage = 10): LengthAwarePaginator;

    public function findWithRelations(Venta $venta): Venta;

    public function create(array $data, array $detalles): Venta;

    public function update(Venta $venta, array $data, array $detalles): Venta;

    public function updateStatus(Venta $venta, string $estado, ?string $closedAt = null): Venta;

    public function delete(Venta $venta): void;
}
