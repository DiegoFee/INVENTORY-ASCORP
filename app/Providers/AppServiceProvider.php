<?php

namespace App\Providers;

use App\Models\Producto;
use App\Observers\ProductoObserver;
use App\Repositories\CompraRepository;
use App\Repositories\CompraRepositoryInterface;
use App\Repositories\ProductoRepository;
use App\Repositories\ProductoRepositoryInterface;
use Illuminate\Support\ServiceProvider;

/**
 * // Autor: Diego Méndez - Fecha: 20/05/2026
 */
class AppServiceProvider extends ServiceProvider
{
    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function register(): void
    {
        $this->app->bind(CompraRepositoryInterface::class, CompraRepository::class);
        $this->app->bind(ProductoRepositoryInterface::class, ProductoRepository::class);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function boot(): void
    {
        Producto::observe(ProductoObserver::class);
    }
}
