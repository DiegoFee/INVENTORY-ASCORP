<?php

namespace App\Observers;

use App\Models\Producto;
use Illuminate\Support\Facades\Log;

/**
 * // Autor: Diego Méndez - Fecha: 20/05/2026
 */
class ProductoObserver
{
    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function updated(Producto $producto): void
    {
        if (! $producto->wasChanged('stock_actual')) {
            return;
        }

        Log::info('Producto stock actualizado.', [
            'producto_id' => $producto->id,
            'sku' => $producto->sku,
            'stock_anterior' => $producto->getOriginal('stock_actual'),
            'stock_nuevo' => $producto->stock_actual,
        ]);
    }
}
