<?php

namespace App\Providers;

use App\Enums\Permission;
use App\Models\Producto;
use App\Models\Role;
use App\Models\User;
use App\Observers\ProductoObserver;
use App\Repositories\CompraRepository;
use App\Repositories\CompraRepositoryInterface;
use App\Repositories\DevolucionRepository;
use App\Repositories\DevolucionRepositoryInterface;
use App\Repositories\MovimientoInventarioRepository;
use App\Repositories\MovimientoInventarioRepositoryInterface;
use App\Repositories\ProductoRepository;
use App\Repositories\ProductoRepositoryInterface;
use App\Repositories\VentaRepository;
use App\Repositories\VentaRepositoryInterface;
use Illuminate\Support\Facades\Gate;
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
        $this->app->bind(MovimientoInventarioRepositoryInterface::class, MovimientoInventarioRepository::class);
        $this->app->bind(ProductoRepositoryInterface::class, ProductoRepository::class);
        $this->app->bind(VentaRepositoryInterface::class, VentaRepository::class);
        $this->app->bind(DevolucionRepositoryInterface::class, DevolucionRepository::class);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function boot(): void
    {
        Producto::observe(ProductoObserver::class);

        Gate::before(function (User $user): ?bool {
            return $user->isAdministrator() ? true : null;
        });

        $sellerPermissions = [
            Permission::DashboardView,
            Permission::SalesDashboardView,
            Permission::VentasView,
            Permission::VentasCreate,
            Permission::VentasUpdate,
            Permission::VentasClose,
            Permission::VentasExport,
            Permission::DevolucionesView,
            Permission::DevolucionesCreate,
            Permission::DevolucionesApprove,
            Permission::DevolucionesReject,
            Permission::DevolucionesExport,
            Permission::AlertasView,
        ];

        $warehousePermissions = [
            Permission::DashboardView,
            Permission::InventoryDashboardView,
            Permission::InventarioView,
            Permission::InventarioEntrada,
            Permission::InventarioSalida,
            Permission::InventarioKardex,
            Permission::ComprasView,
            Permission::ComprasCreate,
            Permission::ComprasUpdate,
            Permission::ComprasDelete,
            Permission::ComprasReceive,
            Permission::ProductosView,
            Permission::ProductosCreate,
            Permission::ProductosUpdate,
            Permission::ProductosDelete,
            Permission::SuppliersView,
            Permission::SuppliersCreate,
            Permission::SuppliersUpdate,
            Permission::SuppliersDelete,
            Permission::AlertasView,
        ];

        $this->defineRolePermissions($sellerPermissions, Role::Seller);
        $this->defineRolePermissions($warehousePermissions, Role::Warehouse);

        foreach ([
            Permission::UsersView,
            Permission::ClientsView,
            Permission::VentasDelete,
            Permission::CxcView,
            Permission::CxcUpdate,
            Permission::ReportsVentasView,
            Permission::ReportsInventarioView,
            Permission::ReportsCxcView,
            Permission::ReportsExport,
            Permission::FosoView,
            Permission::FosoCreate,
            Permission::FosoDelete,
            Permission::FosoClose,
            Permission::FosoExport,
            Permission::DashboardActivityView,
        ] as $adminOnlyPermission) {
            Gate::define($adminOnlyPermission->value, fn (): bool => false);
        }
    }

    /**
     * @param  list<Permission>  $permissions
     */
    private function defineRolePermissions(array $permissions, string ...$roles): void
    {
        foreach ($permissions as $permission) {
            Gate::define(
                $permission->value,
                fn (User $user): bool => $user->hasRole(...$roles)
            );
        }
    }
}
