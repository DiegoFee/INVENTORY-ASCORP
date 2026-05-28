<?php

namespace App\Services;

use App\Models\Alerta;
use App\Models\Producto;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * // Autor: Diego Méndez - Fecha: 27/05/2026
 */
class AlertaService
{
    /**
     * // Autor: Diego Méndez - Fecha: 27/05/2026
     */
    public function generarAlertasStock(): void
    {
        $productos = Producto::query()
            ->active()
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->get(['id', 'nombre', 'stock_actual', 'stock_minimo']);

        foreach ($productos as $producto) {
            $tipo = $producto->stock_actual === 0 ? 'critico' : 'stock_bajo';

            $yaExiste = Alerta::query()
                ->where('producto_id', $producto->id)
                ->where('tipo', $tipo)
                ->exists();

            if ($yaExiste) {
                continue;
            }

            $mensaje = $tipo === 'critico'
                ? "{$producto->nombre}: stock agotado (0 unidades). Reposicion urgente requerida."
                : "{$producto->nombre}: stock {$producto->stock_actual} por debajo del minimo {$producto->stock_minimo}.";

            Alerta::query()->create([
                'tipo' => $tipo,
                'producto_id' => $producto->id,
                'mensaje' => $mensaje,
            ]);
        }
    }

    /**
     * // Autor: Diego Méndez - Fecha: 27/05/2026
     */
    public function obtenerActivas(): Collection
    {
        return Alerta::query()
            ->noLeidas()
            ->with('producto:id,nombre')
            ->latest()
            ->get();
    }

    /**
     * // Autor: Diego Méndez - Fecha: 27/05/2026
     */
    public function obtenerTodas(?string $tipo = null, ?bool $leida = null): LengthAwarePaginator
    {
        $query = Alerta::query()->with('producto:id,nombre,sku');

        if ($tipo !== null) {
            $query->porTipo($tipo);
        }

        if ($leida !== null) {
            $leida ? $query->where('leida', true) : $query->noLeidas();
        }

        return $query->latest()->paginate(15);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 27/05/2026
     */
    public function marcarComoLeida(Alerta $alerta): void
    {
        $alerta->update(['leida' => true]);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 27/05/2026
     */
    public function marcarTodasComoLeidas(): void
    {
        Alerta::query()->noLeidas()->update(['leida' => true]);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 27/05/2026
     */
    public function contarNoLeidas(): int
    {
        return Alerta::query()->noLeidas()->count();
    }
}
