<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Http\Requests\ReceiveCompraRequest;
use App\Http\Requests\StoreCompraRequest;
use App\Http\Requests\UpdateCompraRequest;
use App\Models\Compra;
use App\Repositories\CompraRepositoryInterface;
use App\Services\CompraService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * // Autor: Diego Méndez - Fecha: 21/05/2026
 */
class CompraController extends Controller
{
    public function __construct(
        private readonly CompraRepositoryInterface $compras,
        private readonly CompraService $compraService
    ) {}

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function index(): View
    {
        return view('compras.index');
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function create(): View
    {
        Gate::authorize(Permission::ComprasCreate->value);

        return view('compras.create');
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function store(StoreCompraRequest $request): RedirectResponse
    {
        Gate::authorize(Permission::ComprasCreate->value);

        $validated = $request->validated();
        $detalles = $validated['detalles'] ?? [];

        $compra = $this->compraService->createCompra($validated, $detalles);

        return redirect()
            ->route('compras.show', $compra)
            ->with('success', 'Compra registrada correctamente.');
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function show(Compra $compra): View
    {
        return view('compras.show', [
            'compra' => $this->compras->findWithRelations($compra),
        ]);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function edit(Compra $compra): View
    {
        Gate::authorize(Permission::ComprasUpdate->value);

        return view('compras.edit', [
            'compra' => $this->compras->findWithRelations($compra),
        ]);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function update(UpdateCompraRequest $request, Compra $compra): RedirectResponse
    {
        Gate::authorize(Permission::ComprasUpdate->value);

        $validated = $request->validated();
        $detalles = $validated['detalles'] ?? [];

        $compra = $this->compraService->updateCompra($compra, $validated, $detalles);

        return redirect()
            ->route('compras.show', $compra)
            ->with('success', 'Compra actualizada correctamente.');
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function receive(ReceiveCompraRequest $request, Compra $compra): RedirectResponse
    {
        Gate::authorize(Permission::ComprasReceive->value);

        $compra = $this->compraService->receiveCompra($compra);

        return redirect()
            ->route('compras.show', $compra)
            ->with('success', 'Mercaderia recibida y stock actualizado.');
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function destroy(Compra $compra): RedirectResponse
    {
        Gate::authorize(Permission::ComprasDelete->value);

        $this->compras->delete($compra);

        return redirect()
            ->route('compras.index')
            ->with('success', 'Compra eliminada correctamente.');
    }
}
