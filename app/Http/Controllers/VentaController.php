<?php

namespace App\Http\Controllers;

use App\Http\Requests\CloseVentaRequest;
use App\Http\Requests\StoreVentaRequest;
use App\Http\Requests\UpdateVentaRequest;
use App\Models\Venta;
use App\Repositories\VentaRepositoryInterface;
use App\Services\VentaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class VentaController extends Controller
{
    public function __construct(
        private readonly VentaRepositoryInterface $ventas,
        private readonly VentaService $ventaService
    ) {}

    public function index(): View
    {
        return view('ventas.index');
    }

    public function create(): View
    {
        return view('ventas.create');
    }

    public function show(Venta $venta): View
    {
        return view('ventas.show', [
            'venta' => $this->ventas->findWithRelations($venta),
        ]);
    }

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

    public function edit(Venta $venta): View
    {
        return view('ventas.edit', [
            'venta' => $this->ventas->findWithRelations($venta),
        ]);
    }

    public function update(UpdateVentaRequest $request, Venta $venta): RedirectResponse
    {
        $validated = $request->validated();
        $detalles = $validated['detalles'] ?? [];

        $venta = $this->ventaService->updateVenta($venta, $validated, $detalles);

        return redirect()
            ->route('ventas.show', $venta)
            ->with('success', 'Venta actualizada correctamente.');
    }

    public function destroy(Venta $venta): RedirectResponse
    {
        $this->ventas->delete($venta);

        return redirect()
            ->route('ventas.index')
            ->with('success', 'Venta eliminada correctamente.');
    }

    public function close(CloseVentaRequest $request, Venta $venta): RedirectResponse
    {
        $venta = $this->ventaService->closeVenta($venta);

        return redirect()
            ->route('ventas.show', $venta)
            ->with('success', 'Venta cerrada y stock descontado correctamente.');
    }
}
