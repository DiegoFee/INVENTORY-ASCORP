<?php

namespace App\Http\Controllers;

use App\Http\Requests\SupplierRequest;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SupplierController extends Controller
{
    // El constructor ya no está aquí (el middleware está en la ruta)

    public function index(): View
    {
        $suppliers = Supplier::orderBy('nombre', 'asc')->paginate(10);
        return view('suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('suppliers.create');
    }

    public function store(SupplierRequest $request): RedirectResponse
    {
        Supplier::create($request->validated());
        return redirect()->route('suppliers.index')->with('success', 'Proveedor creado correctamente.');
    }

    public function show(Supplier $supplier): View
    {
        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier): View
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(SupplierRequest $request, Supplier $supplier): RedirectResponse
    {
        $supplier->update($request->validated());
        return redirect()->route('suppliers.index')->with('success', 'Proveedor actualizado correctamente.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        $supplier->delete();
        return redirect()->route('suppliers.index')->with('success', 'Proveedor eliminado correctamente.');
    }
}