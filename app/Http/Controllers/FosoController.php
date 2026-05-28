<?php

namespace App\Http\Controllers;

use App\Enums\Permission;
use App\Models\ServicioFoso;
use App\Services\InventoryMovementService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class FosoController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize(Permission::FosoView->value);

        $query = ServicioFoso::with('cliente', 'tecnico');

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('placa')) {
            $query->where('placa_vehiculo', 'like', '%'.$request->placa.'%');
        }

        $servicios = $query->orderBy('fecha', 'desc')->paginate(10)->appends($request->query());

        return view('foso.index', compact('servicios'));
    }

    public function create(): View
    {
        Gate::authorize(Permission::FosoCreate->value);

        return view('foso.create');
    }

    public function show(ServicioFoso $servicio): View
    {
        Gate::authorize(Permission::FosoView->value);

        $servicio->load('cliente', 'tecnico', 'detalles.producto');

        return view('foso.show', compact('servicio'));
    }

    public function destroy(ServicioFoso $servicio): RedirectResponse
    {
        Gate::authorize(Permission::FosoDelete->value);

        $servicio->delete();

        return redirect()->route('foso.index')->with('success', 'Servicio eliminado correctamente.');
    }

    public function close(ServicioFoso $servicio, InventoryMovementService $inventoryService): RedirectResponse
    {
        Gate::authorize(Permission::FosoClose->value);

        if ($servicio->estado === 'cerrado') {
            return redirect()->route('foso.show', $servicio)->with('error', 'El servicio ya está cerrado.');
        }

        foreach ($servicio->detalles as $detalle) {
            $inventoryService->registerSalida([
                'producto_id' => $detalle->producto_id,
                'cantidad' => $detalle->cantidad,
                'user_id' => Auth::id(),
                'observaciones' => 'Salida por servicio de foso #'.$servicio->id,
            ]);
        }

        $servicio->estado = 'cerrado';
        $servicio->save();

        return redirect()->route('foso.show', $servicio)->with('success', 'Servicio cerrado y stock actualizado.');
    }

    public function comprobante(ServicioFoso $servicio)
    {
        Gate::authorize(Permission::FosoExport->value);

        $servicio->load('cliente', 'detalles.producto');
        $pdf = Pdf::loadView('foso.comprobante', compact('servicio'));

        return $pdf->stream("comprobante-servicio-{$servicio->id}.pdf");
    }
}
