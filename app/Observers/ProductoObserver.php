<?php

namespace App\Observers;

use App\Models\Producto;
use App\Services\AlertaService;
use Illuminate\Support\Facades\Log;

class ProductoObserver
{
    public function created(Producto $producto): void
    {
        $this->generarAlertasStock();
    }

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

        $this->generarAlertasStock();
    }

    private function generarAlertasStock(): void
    {
        app(AlertaService::class)->generarAlertasStock();
    }
}
