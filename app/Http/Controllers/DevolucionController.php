<?php

namespace App\Http\Controllers;

use App\Http\Requests\RejectDevolucionRequest;
use App\Http\Requests\StoreDevolucionRequest;
use App\Models\Devolucion;
use App\Repositories\DevolucionRepositoryInterface;
use App\Services\DevolucionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DevolucionController extends Controller
{
    public function __construct(
        private readonly DevolucionRepositoryInterface $devoluciones,
        private readonly DevolucionService $devolucionService
    ) {}

    public function index(): View
    {
        return view('devoluciones.index');
    }

    public function show(Devolucion $devolucion): View
    {
        return view('devoluciones.show', [
            'devolucion' => $this->devoluciones->findWithRelations($devolucion),
        ]);
    }

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

    public function approve(Devolucion $devolucion): RedirectResponse
    {
        $devolucion = $this->devolucionService->procesarDevolucion($devolucion);

        return redirect()
            ->route('devoluciones.show', $devolucion)
            ->with('success', 'Devolución procesada y stock actualizado.');
    }

    public function reject(RejectDevolucionRequest $request, Devolucion $devolucion): RedirectResponse
    {
        $validated = $request->validated();
        $motivo = $validated['motivo'] ?? null;

        $devolucion = $this->devolucionService->rejectDevolucion($devolucion, $motivo);

        return redirect()
            ->route('devoluciones.show', $devolucion)
            ->with('success', 'Devolución rechazada.');
    }
}
