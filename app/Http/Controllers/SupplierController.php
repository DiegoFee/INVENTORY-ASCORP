<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class SupplierController extends Controller
{
    /**
     * Muestra el listado de proveedores con búsqueda y filtros.
     */
    public function index(Request $request): View
    {
        $query = Supplier::query();

        // Búsqueda por nombre, NIT o email
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('nombre', 'like', "%{$search}%")
                    ->orWhere('nit', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtro por si tiene contacto o no
        if ($request->filled('has_contact')) {
            if ($request->has_contact == 'yes') {
                $query->whereNotNull('contacto_nombre');
            } elseif ($request->has_contact == 'no') {
                $query->whereNull('contacto_nombre');
            }
        }

        // Ordenar y paginar, manteniendo los parámetros de la URL
        $suppliers = $query->orderBy('nombre', 'asc')->paginate(10)->appends($request->query());

        return view('suppliers.index', compact('suppliers'));
    }

    /**
     * Muestra el formulario para crear un nuevo proveedor.
     */
    public function create(): View
    {
        Gate::authorize(Permission::SuppliersCreate->value);

        return view('suppliers.create');
    }

    /**
     * Guarda un nuevo proveedor o restaura uno eliminado con el mismo NIT.
     */
    public function store(SupplierRequest $request): RedirectResponse
    {
        Gate::authorize(Permission::SuppliersCreate->value);

        $data = $request->validated();

        // Buscar si ya existe un proveedor con ese NIT (incluyendo eliminados)
        $existing = Supplier::withTrashed()->where('nit', $data['nit'])->first();

        if ($existing && $existing->trashed()) {
            // Restaurar y actualizar con los nuevos datos
            $existing->restore();
            $existing->update($data);
            $message = 'Proveedor restaurado y actualizado correctamente.';
        } elseif ($existing && ! $existing->trashed()) {
            // No debería ocurrir por la validación, pero por seguridad:
            return redirect()->back()->withInput()->withErrors(['nit' => 'El NIT ya está en uso por un proveedor activo.']);
        } else {
            // Crear nuevo
            Supplier::create($data);
            $message = 'Proveedor creado correctamente.';
        }

        return redirect()->route('suppliers.index')->with('success', $message);
    }

    /**
     * Muestra el detalle de un proveedor.
     */
    public function show(Supplier $supplier): View
    {
        return view('suppliers.show', compact('supplier'));
    }

    /**
     * Muestra el formulario para editar un proveedor.
     */
    public function edit(Supplier $supplier): View
    {
        Gate::authorize(Permission::SuppliersUpdate->value);

        return view('suppliers.edit', compact('supplier'));
    }

    /**
     * Actualiza un proveedor existente.
     */
    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        Gate::authorize(Permission::SuppliersUpdate->value);

        $supplier->update($request->validated());

        return redirect()->route('suppliers.index')->with('success', 'Proveedor actualizado correctamente.');
    }

    /**
     * Elimina suavemente un proveedor (soft delete).
     */
    public function destroy(Supplier $supplier): RedirectResponse
    {
        Gate::authorize(Permission::SuppliersDelete->value);

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', 'Proveedor eliminado correctamente.');
    }
}
