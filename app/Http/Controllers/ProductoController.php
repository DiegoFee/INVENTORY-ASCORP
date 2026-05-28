<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\Producto;
use App\Repositories\ProductoRepositoryInterface;
use App\Services\StockCalculationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * // Autor: Diego Méndez - Fecha: 20/05/2026
 */
class ProductoController extends Controller
{
    public function __construct(
        private readonly ProductoRepositoryInterface $productos,
        private readonly StockCalculationService $stockService
    ) {}

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function index(): View
    {
        return view('productos.index');
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function create(): View
    {
        Gate::authorize(Permission::ProductosCreate->value);

        return view('productos.create');
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function store(StoreProductoRequest $request): RedirectResponse
    {
        Gate::authorize(Permission::ProductosCreate->value);

        $data = $request->validated();

        if (! array_key_exists('activo', $data)) {
            $data['activo'] = true;
        }

        $producto = $this->productos->create($data);

        return redirect()
            ->route('productos.show', $producto)
            ->with('success', 'Producto creado correctamente.');
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function show(Producto $producto): View
    {
        return view('productos.show', compact('producto'));
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function edit(Producto $producto): View
    {
        Gate::authorize(Permission::ProductosUpdate->value);

        return view('productos.edit', compact('producto'));
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function update(UpdateProductoRequest $request, Producto $producto): RedirectResponse
    {
        Gate::authorize(Permission::ProductosUpdate->value);

        $data = $request->validated();
        $stockActual = (int) $data['stock_actual'];

        unset($data['stock_actual']);

        if ($stockActual !== $producto->stock_actual) {
            $diferencia = $stockActual - $producto->stock_actual;

            if ($diferencia > 0) {
                $producto = $this->stockService->increaseStock($producto, $diferencia);
            }

            if ($diferencia < 0) {
                $producto = $this->stockService->decreaseStock($producto, abs($diferencia));
            }
        }

        if ($data !== []) {
            $producto = $this->productos->update($producto, $data);
        }

        return redirect()
            ->route('productos.show', $producto)
            ->with('success', 'Producto actualizado correctamente.');
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function destroy(Producto $producto): RedirectResponse
    {
        Gate::authorize(Permission::ProductosDelete->value);

        $this->productos->delete($producto);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto eliminado correctamente.');
    }
}
