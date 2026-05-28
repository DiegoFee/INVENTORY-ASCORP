<?php

namespace App\Enums;

enum Permission: string
{
    case DashboardView = 'dashboard.view';
    case DashboardActivityView = 'dashboard.activity.view';
    case SalesDashboardView = 'dashboard.sales.view';
    case InventoryDashboardView = 'dashboard.inventory.view';

    case UsersView = 'users.view';
    case ClientsView = 'clientes.view';

    case VentasView = 'ventas.view';
    case VentasCreate = 'ventas.create';
    case VentasUpdate = 'ventas.update';
    case VentasDelete = 'ventas.delete';
    case VentasClose = 'ventas.close';
    case VentasExport = 'ventas.export';

    case DevolucionesView = 'devoluciones.view';
    case DevolucionesCreate = 'devoluciones.create';
    case DevolucionesApprove = 'devoluciones.approve';
    case DevolucionesReject = 'devoluciones.reject';
    case DevolucionesExport = 'devoluciones.export';

    case InventarioView = 'inventario.view';
    case InventarioEntrada = 'inventario.entrada';
    case InventarioSalida = 'inventario.salida';
    case InventarioKardex = 'inventario.kardex';

    case ComprasView = 'compras.view';
    case ComprasCreate = 'compras.create';
    case ComprasUpdate = 'compras.update';
    case ComprasDelete = 'compras.delete';
    case ComprasReceive = 'compras.receive';

    case ProductosView = 'productos.view';
    case ProductosCreate = 'productos.create';
    case ProductosUpdate = 'productos.update';
    case ProductosDelete = 'productos.delete';

    case SuppliersView = 'suppliers.view';
    case SuppliersCreate = 'suppliers.create';
    case SuppliersUpdate = 'suppliers.update';
    case SuppliersDelete = 'suppliers.delete';

    case CxcView = 'cxc.view';
    case CxcUpdate = 'cxc.update';

    case ReportsVentasView = 'reports.ventas.view';
    case ReportsInventarioView = 'reports.inventario.view';
    case ReportsCxcView = 'reports.cxc.view';
    case ReportsExport = 'reports.export';

    case FosoView = 'foso.view';
    case FosoCreate = 'foso.create';
    case FosoDelete = 'foso.delete';
    case FosoClose = 'foso.close';
    case FosoExport = 'foso.export';

    case AlertasView = 'alertas.view';
}
