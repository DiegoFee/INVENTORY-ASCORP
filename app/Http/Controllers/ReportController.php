<?php

declare(strict_types=1);

/** Autor: Celvin Arandi, Descripcion: Controlador de reportes con exportacion PDF. */

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Models\Cliente;
use App\Models\Producto;
use App\Services\ReportService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends Controller
{
    public function __construct(private readonly ReportService $reportService) {}

    /**
     * Funcionamiento: muestra la vista de filtros para reporte de ventas.
     * Tablas: clientes.
     * Flujo: consulta clientes activos y entrega el formulario.
     */
    public function ventas(): View
    {
        Gate::authorize(Permission::ReportsVentasView->value);

        return view('reports.ventas', [
            'clientes' => Cliente::query()->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    /**
     * Funcionamiento: muestra la vista de filtros para reporte de inventario.
     * Tablas: productos.
     * Flujo: consulta productos disponibles y entrega el formulario.
     */
    public function inventario(): View
    {
        Gate::authorize(Permission::ReportsInventarioView->value);

        return view('reports.inventario', [
            'productos' => Producto::query()->orderBy('nombre')->get(['id', 'nombre', 'sku']),
        ]);
    }

    /**
     * Funcionamiento: muestra la vista de filtros para reporte de cuentas por cobrar.
     * Tablas: clientes.
     * Flujo: consulta clientes y entrega el formulario.
     */
    public function cxc(): View
    {
        Gate::authorize(Permission::ReportsCxcView->value);

        return view('reports.cxc', [
            'clientes' => Cliente::query()->orderBy('nombre')->get(['id', 'nombre']),
        ]);
    }

    /**
     * Funcionamiento: exporta el reporte solicitado en PDF desde la plantilla base.
     * Tablas: ventas, productos, movimientos_inventario, cuentas_por_cobrar.
     * Flujo: resuelve filtros, obtiene datos del servicio y retorna descarga PDF.
     */
    public function exportPdf(string $type, Request $request): Response
    {
        Gate::authorize(Permission::ReportsExport->value);
        Gate::authorize($this->permissionForReportType($type)->value);

        $report = $this->resolveReportData($type, $request->all());

        $pdf = Pdf::loadView('reports.pdf_template', [
            'type' => $type,
            'report' => $report,
            'generatedAt' => now(),
        ]);

        return $pdf->download('reporte-'.$type.'-'.now()->format('Ymd_His').'.pdf');
    }

    /**
     * Funcionamiento: direcciona el tipo de reporte al metodo correcto del servicio.
     */
    private function resolveReportData(string $type, array $filters): array
    {
        return match ($type) {
            'ventas' => $this->reportService->getVentasReportData($filters),
            'inventario' => $this->reportService->getInventoryReportData($filters),
            'cxc' => $this->reportService->getCxcReportData($filters),
            default => abort(404),
        };
    }

    private function permissionForReportType(string $type): Permission
    {
        return match ($type) {
            'ventas' => Permission::ReportsVentasView,
            'inventario' => Permission::ReportsInventarioView,
            'cxc' => Permission::ReportsCxcView,
            default => abort(404),
        };
    }
}
