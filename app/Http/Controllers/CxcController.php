<?php

namespace App\Http\Controllers;

use App\Models\CuentaPorCobrar;
use App\Models\Pago;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CxcController extends Controller
{
    public function index(Request $request): View
    {
        $query = CuentaPorCobrar::with('cliente', 'venta');

        if ($request->filled('cliente_id')) {
            $query->where('cliente_id', $request->cliente_id);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $cuentas = $query->orderBy('created_at', 'desc')->paginate(10)->appends($request->query());

        return view('cxc.index', compact('cuentas'));
    }

    public function show($id): View
    {
        $cuenta = CuentaPorCobrar::with(['cliente', 'venta', 'pagos'])->findOrFail($id);

        return view('cxc.show', compact('cuenta'));
    }

    public function storeAbono(Request $request, CuentaPorCobrar $cuenta): RedirectResponse
    {
        $request->validate([
            'monto' => 'required|numeric|min:0.01|max:'.$cuenta->saldo,
            'fecha_pago' => 'required|date',
            'metodo_pago' => 'required|string|max:50',
            'referencia' => 'nullable|string|max:100',
            'observaciones' => 'nullable|string',
        ]);

        $pago = new Pago;
        $pago->cuenta_id = $cuenta->id;
        $pago->monto = $request->monto;
        $pago->fecha_pago = $request->fecha_pago;
        $pago->metodo_pago = $request->metodo_pago;
        $pago->referencia = $request->referencia;
        $pago->observaciones = $request->observaciones;
        $pago->save();

        $nuevoSaldo = $cuenta->saldo - $request->monto;
        $cuenta->saldo = $nuevoSaldo;
        if ($nuevoSaldo <= 0) {
            $cuenta->estado = 'saldada';
        }
        $cuenta->save();

        return redirect()->route('cxc.show', $cuenta)->with('success', 'Abono registrado correctamente');
    }
}
