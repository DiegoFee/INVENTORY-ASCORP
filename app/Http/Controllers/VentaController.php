<?php

/** Autor: Arandi Hurtado, Fecha: 21/05/2026, Descripción: Controlador de ventas con facturas y comprobantes PDF. */

namespace App\Http\Controllers;

use App\Http\Requests\CloseVentaRequest;
use App\Http\Requests\StoreVentaRequest;
use App\Http\Requests\UpdateVentaRequest;
use App\Models\Venta;
use App\Repositories\VentaRepositoryInterface;
use App\Services\VentaService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class VentaController extends Controller
{
    public function __construct(
        private readonly VentaRepositoryInterface $ventas,
        private readonly VentaService $ventaService
    ) {}

    /**
     * Funcionamiento: inyecta repositorio y servicio de ventas.
     * Tablas: ventas.
     * Flujo: prepara dependencias para consultas y operaciones de negocio.
     */
    public function index(): View
    {
        return view('ventas.index');
    }

    /**
     * Funcionamiento: muestra el listado general de ventas.
     * Tablas: ventas.
     * Flujo: entrega la vista que consume Livewire para listar.
     */
    public function create(): View
    {
        return view('ventas.create');
    }

    /**
     * Funcionamiento: muestra formulario para crear una venta.
     * Tablas: ventas, productos.
     * Flujo: entrega la vista con el componente de formulario.
     */
    public function show(Venta $venta): View
    {
        return view('ventas.show', [
            'venta' => $this->ventas->findWithRelations($venta),
        ]);
    }

    /**
     * Funcionamiento: muestra el detalle completo de una venta con relaciones cargadas.
     * Tablas: ventas, detalles_venta, clientes, cajas.
     * Flujo: resuelve la venta con relaciones y la entrega a la vista.
     */
    public function factura(Venta $venta): Response
    {
        $venta = $this->ventas->findWithRelations($venta);
        $serie = 'FAC-001';
        $numero = str_pad((string) $venta->getKey(), 6, '0', STR_PAD_LEFT);

        $pdf = Pdf::loadView('ventas.factura', [
            'venta' => $venta,
            'serie' => $serie,
            'numero' => $numero,
            'ivaTasa' => 0.12,
        ]);

        return $pdf->stream('factura-'.$venta->getKey().'.pdf');
    }

    /**
     * Funcionamiento: genera el PDF de factura con datos fiscales y desglose de IVA.
     * Tablas: ventas, detalles_venta, clientes.
     * Flujo: carga venta, calcula serie/numero y renderiza la vista en DomPDF.
     */
    public function comprobante(Venta $venta): Response
    {
        $venta = $this->ventas->findWithRelations($venta);

        $pdf = Pdf::loadView('ventas.comprobante', [
            'venta' => $venta,
        ]);

        return $pdf->stream('comprobante-'.$venta->getKey().'.pdf');
    }

    /**
     * Funcionamiento: genera un comprobante PDF como resumen de entrega.
     * Tablas: ventas, detalles_venta, clientes.
     * Flujo: carga venta con detalles y renderiza vista simplificada.
     */
    public function store(StoreVentaRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;
        $detalles = $validated['detalles'] ?? [];

        $venta = $this->ventaService->createVenta($validated, $detalles);

        return redirect()
            ->route('ventas.show', $venta)
            ->with('success', 'Venta creada correctamente.');
    }

    /**
     * Funcionamiento: registra una venta nueva desde formulario validado.
     * Tablas: ventas, detalles_venta, cajas.
     * Flujo: valida request, delega al servicio y redirige al detalle.
     */
    public function edit(Venta $venta): View
    {
        return view('ventas.edit', [
            'venta' => $this->ventas->findWithRelations($venta),
        ]);
    }

    /**
     * Funcionamiento: muestra formulario de edicion con relaciones cargadas.
     * Tablas: ventas, detalles_venta, clientes.
     * Flujo: resuelve venta y entrega datos a la vista.
     */
    public function update(UpdateVentaRequest $request, Venta $venta): RedirectResponse
    {
        $validated = $request->validated();
        $detalles = $validated['detalles'] ?? [];

        $venta = $this->ventaService->updateVenta($venta, $validated, $detalles);

        return redirect()
            ->route('ventas.show', $venta)
            ->with('success', 'Venta actualizada correctamente.');
    }

    /**
     * Funcionamiento: actualiza una venta con datos validados.
     * Tablas: ventas, detalles_venta.
     * Flujo: valida request, delega al servicio y redirige al detalle.
     */
    public function destroy(Venta $venta): RedirectResponse
    {
        $this->ventas->delete($venta);

        return redirect()
            ->route('ventas.index')
            ->with('success', 'Venta eliminada correctamente.');
    }

    /**
     * Funcionamiento: elimina la venta seleccionada.
     * Tablas: ventas.
     * Flujo: delega al repositorio y redirige al listado.
     */
    public function close(CloseVentaRequest $request, Venta $venta): RedirectResponse
    {
        $venta = $this->ventaService->closeVenta($venta);

        return redirect()
            ->route('ventas.show', $venta)
            ->with('success', 'Venta cerrada y stock descontado correctamente.');
    }

    /**
     * Funcionamiento: cierra la venta y descuenta inventario.
     * Tablas: ventas, detalles_venta, movimientos_inventario, cuenta_por_cobrar.
     * Flujo: delega al servicio de ventas y redirige al detalle.
     */
}
