<?php

/** Autor: Diego Méndez - Fecha: 26/05/2026, Descripcion: Servicio de alertas de stock bajo y productos criticos. */

namespace App\Services;

use App\Models\Alerta;
use App\Models\Producto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class AlertaService
{
    /**
     * Funcionamiento: genera alertas de stock bajo y critico para todos los productos activos.
     */
    public function generateAlerts(): void
    {
        $productos = Producto::query()->active()->get();

        foreach ($productos as $producto) {
            if ($producto->stock_actual <= 0) {
                $this->createAlertIfNotExists($producto, Alerta::TipoCritico,
                    "Stock critico: {$producto->nombre} (SKU: {$producto->sku}) tiene stock en 0.");
            } elseif ($producto->stock_actual <= $producto->stock_minimo) {
                $this->createAlertIfNotExists($producto, Alerta::TipoBajo,
                    "Stock bajo: {$producto->nombre} (SKU: {$producto->sku}) tiene {$producto->stock_actual} unidades, minimo {$producto->stock_minimo}.");
            }
        }
    }

    /**
     * Funcionamiento: genera alerta para un producto especifico cuando se actualiza el stock.
     */
    public function generateAlertForProducto(Producto $producto, ?int $userId = null): void
    {
        if (! $producto->activo) {
            return;
        }

        if ($producto->stock_actual <= 0) {
            $this->createAlertIfNotExists($producto, Alerta::TipoCritico,
                "Stock critico: {$producto->nombre} (SKU: {$producto->sku}) tiene stock en 0.", $userId);
        } elseif ($producto->stock_actual <= $producto->stock_minimo) {
            $this->createAlertIfNotExists($producto, Alerta::TipoBajo,
                "Stock bajo: {$producto->nombre} (SKU: {$producto->sku}) tiene {$producto->stock_actual} unidades, minimo {$producto->stock_minimo}.", $userId);
        }
    }

    /**
     * Funcionamiento: elimina alertas duplicadas, conservando solo la mas reciente por producto+tipo.
     */
    public function cleanDuplicates(): int
    {
        $subquery = Alerta::query()
            ->selectRaw('MAX(id) as id')
            ->groupBy('producto_id', 'tipo');

        return Alerta::query()
            ->whereNotIn('id', $subquery)
            ->delete();
    }

    /**
     * Funcionamiento: obtiene las alertas activas (no leidas) con su producto relacionado.
     */
    public function getActiveAlerts(): Collection
    {
        return Alerta::query()
            ->where('leida', false)
            ->with('producto')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Funcionamiento: obtiene el conteo de alertas no leidas.
     */
    public function getUnreadCount(): int
    {
        return Alerta::query()->where('leida', false)->count();
    }

    /**
     * Funcionamiento: obtiene el historial completo de alertas paginado.
     */
    public function getHistory(int $perPage = 20): LengthAwarePaginator
    {
        return Alerta::query()
            ->with('producto')
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * Funcionamiento: marca una alerta como leida.
     */
    public function markAsRead(int $alertaId): void
    {
        if ($alertaId <= 0) {
            return;
        }

        Alerta::query()->whereKey($alertaId)->update(['leida' => true]);
    }

    /**
     * Funcionamiento: marca todas las alertas como leidas.
     */
    public function markAllAsRead(): void
    {
        Alerta::query()->where('leida', false)->update(['leida' => true]);
    }

    /**
     * Funcionamiento: elimina alertas viejas (mas de 30 dias).
     */
    public function cleanOldAlerts(): int
    {
        return Alerta::query()
            ->where('created_at', '<', now()->subDays(30))
            ->delete();
    }

    /**
     * Funcionamiento: crea una alerta solo si no existe ninguna alerta del mismo tipo para el mismo producto.
     */
    private function createAlertIfNotExists(Producto $producto, string $tipo, string $mensaje, ?int $userId = null): void
    {
        $exists = Alerta::query()
            ->where('producto_id', $producto->id)
            ->where('tipo', $tipo)
            ->exists();

        if (! $exists) {
            Alerta::query()->create([
                'producto_id' => $producto->id,
                'user_id' => $userId,
                'tipo' => $tipo,
                'mensaje' => $mensaje,
                'leida' => false,
            ]);
        }
    }
}
