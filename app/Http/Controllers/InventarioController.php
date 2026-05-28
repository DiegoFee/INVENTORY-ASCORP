<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Models\Producto;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * // Autor: Diego Méndez - Fecha: 21/05/2026
 */
class InventarioController extends Controller
{
    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function index(): View
    {
        return view('inventario.index');
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function entrada(): View
    {
        Gate::authorize(Permission::InventarioEntrada->value);

        return view('inventario.entrada');
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function salida(): View
    {
        Gate::authorize(Permission::InventarioSalida->value);

        return view('inventario.salida');
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function kardex(Producto $producto): View
    {
        return view('inventario.kardex', compact('producto'));
    }
}
