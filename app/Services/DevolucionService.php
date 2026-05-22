<?php

/** Autor: Arandi Hurtado, Fecha: 21/05/2026, Descripción: Servicio de devoluciones con nota de credito y ajuste de saldos. */

namespace App\Services;

use App\Models\CuentaPorCobrar;
use App\Models\Devolucion;
use App\Models\Venta;
use App\Repositories\DevolucionRepositoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DevolucionService
{
    public function __construct(
        private readonly DevolucionRepositoryInterface $devoluciones,
        private readonly InventoryMovementService $inventoryService
    ) {}

    /**
     * Funcionamiento: inyecta repositorio de devoluciones y servicio de inventario.
     * Tablas: devoluciones, movimientos_inventario.
     * Flujo: habilita operaciones de registro, procesamiento y rechazo de devoluciones.
     */
    public function createDevolucion(array $data, array $detalles): Devolucion
    {
        return DB::transaction(function () use ($data, $detalles): Devolucion {
            $detalleNormalizado = $this->normalizarDetalles($detalles);
            $monto = $this->calcularTotal($detalleNormalizado);

            $payload = array_merge($this->filtrarDevolucionData($data), [
                'monto' => $monto,
                'estado' => $data['estado'] ?? Devolucion::EstadoPendiente,
            ]);

            return $this->devoluciones->create($payload, $detalleNormalizado);
        });
    }

    /**
     * Funcionamiento: crea devolucion con detalles y monto total.
     * Tablas: devoluciones, detalles_devolucion.
     * Flujo: normaliza detalles, calcula monto y persiste en transaccion.
     */
    public function updateDevolucion(Devolucion $devolucion, array $data, array $detalles): Devolucion
    {
        if ($devolucion->estado === Devolucion::EstadoProcesada) {
            throw ValidationException::withMessages([
                'estado' => 'No se puede editar una devolución procesada.',
            ]);
        }

        return DB::transaction(function () use ($devolucion, $data, $detalles): Devolucion {
            $detalleNormalizado = $this->normalizarDetalles($detalles);
            $monto = $this->calcularTotal($detalleNormalizado);

            $payload = array_merge($this->filtrarDevolucionData($data), [
                'monto' => $monto,
                'estado' => $data['estado'] ?? $devolucion->estado,
            ]);

            return $this->devoluciones->update($devolucion, $payload, $detalleNormalizado);
        });
    }

    /**
     * Funcionamiento: actualiza una devolucion pendiente y sus detalles.
     * Tablas: devoluciones, detalles_devolucion.
     * Flujo: valida estado, recalcula monto y reemplaza detalles en transaccion.
     */
    public function procesarDevolucion(Devolucion $devolucion): Devolucion
    {
        if ($devolucion->estado !== Devolucion::EstadoPendiente) {
            throw ValidationException::withMessages([
                'estado' => 'Solo se pueden procesar devoluciones pendientes.',
            ]);
        }

        return DB::transaction(function () use ($devolucion): Devolucion {
            $devolucionConDetalles = $this->devoluciones->findWithRelations($devolucion);

            if ($devolucionConDetalles->detalles->isEmpty()) {
                throw ValidationException::withMessages([
                    'detalles' => 'La devolución no tiene detalles para procesar.',
                ]);
            }

            foreach ($devolucionConDetalles->detalles as $detalle) {
                $this->inventoryService->registerEntradaFromDevolucion($devolucionConDetalles, $detalle);
            }

            $this->ajustarCuentaPorCobrarPorNotaCredito($devolucionConDetalles);

            return $this->devoluciones->updateStatus(
                $devolucionConDetalles,
                Devolucion::EstadoProcesada
            );
        });
    }

    /**
     * Funcionamiento: procesa devolucion, actualiza stock y descuenta saldo por nota de credito.
     * Tablas: devoluciones, detalles_devolucion, movimientos_inventario, cuenta_por_cobrar.
     * Flujo: valida detalles, registra entradas, ajusta saldo y marca estado procesado.
     */
    public function rejectDevolucion(Devolucion $devolucion, ?string $motivo = null): Devolucion
    {
        if ($devolucion->estado !== Devolucion::EstadoPendiente) {
            throw ValidationException::withMessages([
                'estado' => 'Solo se pueden rechazar devoluciones pendientes.',
            ]);
        }

        return $this->devoluciones->updateStatus(
            $devolucion,
            Devolucion::EstadoRechazada
        );
    }

    /**
     * Funcionamiento: rechaza devolucion pendiente sin afectar inventario ni saldo.
     * Tablas: devoluciones.
     * Flujo: valida estado y actualiza el campo estado a rechazada.
     */
    private function filtrarDevolucionData(array $data): array
    {
        return Arr::only($data, ['venta_id', 'user_id', 'motivo']);
    }

    /**
     * Funcionamiento: filtra campos permitidos para la devolucion.
     * Tablas: devoluciones.
     * Flujo: limita el payload a columnas aprobadas antes de crear/actualizar.
     */
    private function normalizarDetalles(array $detalles): array
    {
        $normalizados = collect($detalles)
            ->filter(fn (array $detalle): bool => (int) ($detalle['producto_id'] ?? 0) > 0)
            ->map(function (array $detalle): array {
                $cantidad = max(1, (int) ($detalle['cantidad'] ?? 0));
                $precioUnitario = (float) ($detalle['precio_unitario'] ?? 0);
                $descuento = (float) ($detalle['descuento'] ?? 0);
                $subtotal = $cantidad * $precioUnitario - $descuento;

                return [
                    'producto_id' => (int) $detalle['producto_id'],
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precioUnitario,
                    'descuento' => $descuento,
                    'subtotal' => max(0, $subtotal),
                ];
            })
            ->values()
            ->all();

        if ($normalizados === []) {
            throw ValidationException::withMessages([
                'detalles' => 'Debes agregar al menos un producto a la devolución.',
            ]);
        }

        return $normalizados;
    }

    /**
     * Funcionamiento: valida y normaliza detalle de devolucion con subtotal por linea.
     * Tablas: detalles_devolucion.
     * Flujo: filtra productos validos, recalcula cantidades y subtotal y devuelve arreglo limpio.
     */
    private function calcularTotal(array $detalles): float
    {
        return (float) collect($detalles)->sum('subtotal');
    }

    /**
     * Funcionamiento: suma subtotales para obtener el monto total de devolucion.
     * Tablas: detalles_devolucion.
     * Flujo: agrega subtotales ya normalizados y retorna monto numerico.
     */
    private function ajustarCuentaPorCobrarPorNotaCredito(Devolucion $devolucion): CuentaPorCobrar
    {
        $venta = $devolucion->venta;

        if ($venta === null) {
            throw ValidationException::withMessages([
                'venta' => 'La devolución no tiene venta asociada para ajustar saldo.',
            ]);
        }

        $cuenta = $this->resolveCuentaPorCobrar($venta);
        $saldoActual = (float) $cuenta->saldo;
        $montoNota = (float) $devolucion->monto;
        $saldoNuevo = max(0, $saldoActual - $montoNota);

        $cuenta->update([
            'saldo' => $saldoNuevo,
            'estado' => $saldoNuevo <= 0 ? CuentaPorCobrar::EstadoPagada : CuentaPorCobrar::EstadoPendiente,
            'observaciones' => 'Ajuste por nota de credito #'.$devolucion->getKey(),
        ]);

        return $cuenta->refresh();
    }

    /**
     * Funcionamiento: aplica la nota de credito descontando el saldo de la venta.
     * Tablas: cuenta_por_cobrar, devoluciones, ventas.
     * Flujo: obtiene cuenta por venta, descuenta monto y actualiza estado segun saldo.
     */
    private function resolveCuentaPorCobrar(Venta $venta): CuentaPorCobrar
    {
        return CuentaPorCobrar::query()->firstOrCreate(
            ['id_venta' => $venta->getKey()],
            [
                'monto_original' => (float) $venta->total,
                'saldo' => (float) $venta->total,
                'estado' => CuentaPorCobrar::EstadoPendiente,
                'observaciones' => 'Cuenta generada por nota de credito.',
            ]
        );
    }

    /**
     * Funcionamiento: garantiza una cuenta por cobrar existente para la venta.
     * Tablas: cuenta_por_cobrar, ventas.
     * Flujo: busca por id_venta y crea registro inicial con saldo igual al total.
     */
}
