<?php

/** Autor: Arandi Hurtado, Fecha: 21/05/2026, Descripción: Controlador de devoluciones con nota de credito PDF. */

namespace App\Http\Controllers;

use App\Http\Requests\RejectDevolucionRequest;
use App\Http\Requests\StoreDevolucionRequest;
use App\Models\Devolucion;
use App\Repositories\DevolucionRepositoryInterface;
use App\Services\DevolucionService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class DevolucionController extends Controller
{
    public function __construct(
        private readonly DevolucionRepositoryInterface $devoluciones,
        private readonly DevolucionService $devolucionService
    ) {}

    /**
     * Funcionamiento: inyecta repositorio y servicio de devoluciones.
     * Tablas: devoluciones.
     * Flujo: prepara dependencias para consultas y operaciones de negocio.
     */
    public function index(): View
    {
        Gate::authorize('viewAny', Devolucion::class);

        return view('devoluciones.index');
    }

    /**
     * Funcionamiento: muestra el listado general de devoluciones.
     * Tablas: devoluciones.
     * Flujo: entrega la vista que consume Livewire para listar.
     */
    public function show(Devolucion $devolucion): View
    {
        Gate::authorize('view', $devolucion);

        return view('devoluciones.show', [
            'devolucion' => $this->devoluciones->findWithRelations($devolucion),
        ]);
    }

    /**
     * Funcionamiento: muestra el detalle completo de una devolucion.
     * Tablas: devoluciones, detalles_devolucion, ventas.
     * Flujo: carga relaciones y entrega datos a la vista.
     */
    public function notaCredito(Devolucion $devolucion): Response
    {
        Gate::authorize('view', $devolucion);

        $devolucion = $this->devoluciones->findWithRelations($devolucion);

        $pdf = Pdf::loadView('devoluciones.nota-credito', [
            'devolucion' => $devolucion,
            'ivaTasa' => 0.12,
        ]);

        return $pdf->stream('nota-credito-'.$devolucion->getKey().'.pdf');
    }

    /**
     * Funcionamiento: genera PDF de nota de credito vinculada a la venta.
     * Tablas: devoluciones, detalles_devolucion, ventas, cuenta_por_cobrar.
     * Flujo: carga devolucion con venta y detalles, renderiza vista en DomPDF.
     */
    public function store(StoreDevolucionRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $validated['user_id'] = $request->user()->id;
        $detalles = $validated['detalles'] ?? [];

        $devolucion = $this->devolucionService->createDevolucion($validated, $detalles);

        return redirect()
            ->route('devoluciones.show', $devolucion)
            ->with('success', 'Devolución registrada correctamente.');
    }

    /**
     * Funcionamiento: registra una devolucion nueva desde formulario validado.
     * Tablas: devoluciones, detalles_devolucion.
     * Flujo: valida request, delega al servicio y redirige al detalle.
     */
    public function approve(Devolucion $devolucion): RedirectResponse
    {
        Gate::authorize('approve', $devolucion);

        $devolucion = $this->devolucionService->procesarDevolucion($devolucion);

        return redirect()
            ->route('devoluciones.show', $devolucion)
            ->with('success', 'Devolución procesada y stock actualizado.');
    }

    /**
     * Funcionamiento: procesa devolucion y actualiza inventario.
     * Tablas: devoluciones, detalles_devolucion, movimientos_inventario, cuenta_por_cobrar.
     * Flujo: delega al servicio y redirige al detalle.
     */
    public function reject(RejectDevolucionRequest $request, Devolucion $devolucion): RedirectResponse
    {
        Gate::authorize('reject', $devolucion);

        $validated = $request->validated();
        $motivo = $validated['motivo'] ?? null;

        $devolucion = $this->devolucionService->rejectDevolucion($devolucion, $motivo);

        return redirect()
            ->route('devoluciones.show', $devolucion)
            ->with('success', 'Devolución rechazada.');
    }

    /**
     * Funcionamiento: rechaza una devolucion pendiente.
     * Tablas: devoluciones.
     * Flujo: valida request, delega al servicio y redirige al detalle.
     */
}
