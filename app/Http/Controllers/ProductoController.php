<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductoRequest;
use App\Http\Requests\UpdateProductoRequest;
use App\Models\Producto;
use App\Repositories\ProductoRepositoryInterface;
use App\Services\StockCalculationService;
use Illuminate\Http\RedirectResponse;
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
        return view('productos.create');
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function store(StoreProductoRequest $request): RedirectResponse
    {
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
        return view('productos.edit', compact('producto'));
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function update(UpdateProductoRequest $request, Producto $producto): RedirectResponse
    {
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
        $this->productos->delete($producto);

        return redirect()
            ->route('productos.index')
            ->with('success', 'Producto eliminado correctamente.');
    }
}
