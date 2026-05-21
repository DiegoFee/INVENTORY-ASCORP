<?php

namespace App\Repositories;

use App\Models\Compra;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * // Autor: Diego Méndez - Fecha: 21/05/2026
 */
interface CompraRepositoryInterface
{
    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function paginateForList(?string $search, int $perPage = 10): LengthAwarePaginator;

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function findWithRelations(Compra $compra): Compra;

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function create(array $data, array $detalles): Compra;

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function update(Compra $compra, array $data, array $detalles): Compra;

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function updateStatus(Compra $compra, string $estado, ?string $fechaRecepcion = null): Compra;

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function delete(Compra $compra): void;
}
