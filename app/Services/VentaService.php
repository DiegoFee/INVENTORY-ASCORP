<?php

/** Autor: Arandi Hurtado, Fecha: 21/05/2026, Descripción: Servicio de ventas con cierre y cuentas por cobrar. */

namespace App\Services;

use App\Models\Caja;
use App\Models\CuentaPorCobrar;
use App\Models\Venta;
use App\Repositories\VentaRepositoryInterface;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VentaService
{
    public function __construct(
        private readonly VentaRepositoryInterface $ventas,
        private readonly InventoryMovementService $inventoryService
    ) {}

    /**
     * Funcionamiento: inyecta repositorio de ventas y servicio de inventario.
     * Tablas: ventas, movimientos_inventario.
     * Flujo: prepara dependencias para crear/actualizar/ cerrar ventas.
     */
    public function createVenta(array $data, array $detalles): Venta
    {
        return DB::transaction(function () use ($data, $detalles): Venta {
            $detalleNormalizado = $this->normalizarDetalles($detalles);
            $descuentoGlobal = (float) ($data['descuento'] ?? 0);
            $total = max(0, $this->calcularTotal($detalleNormalizado) - $descuentoGlobal);

            $caja = $this->resolveCajaParaUsuario((int) ($data['user_id'] ?? 0));

            $payload = array_merge($this->filtrarVentaData($data), [
                'caja_id' => $caja->id,
                'total' => $total,
                'estado' => $data['estado'] ?? Venta::EstadoBorrador,
                'opened_at' => $data['opened_at'] ?? now()->toDateTimeString(),
            ]);

            return $this->ventas->create($payload, $detalleNormalizado);
        });
    }

    /**
     * Funcionamiento: crea venta y detalles con total calculado y caja resuelta.
     * Tablas: ventas, detalles_venta, cajas.
     * Flujo: normaliza detalles, calcula total, asigna caja y persiste en transaccion.
     */
    public function updateVenta(Venta $venta, array $data, array $detalles): Venta
    {
        if ($venta->estado === Venta::EstadoCerrada) {
            throw ValidationException::withMessages([
                'estado' => 'No se puede editar una venta cerrada.',
            ]);
        }

        return DB::transaction(function () use ($venta, $data, $detalles): Venta {
            $detalleNormalizado = $this->normalizarDetalles($detalles);
            $descuentoGlobal = (float) ($data['descuento'] ?? 0);
            $total = max(0, $this->calcularTotal($detalleNormalizado) - $descuentoGlobal);

            $payload = array_merge($this->filtrarVentaData($data), [
                'total' => $total,
                'estado' => $data['estado'] ?? $venta->estado,
            ]);

            return $this->ventas->update($venta, $payload, $detalleNormalizado);
        });
    }

    /**
     * Funcionamiento: actualiza la venta mientras no este cerrada.
     * Tablas: ventas, detalles_venta.
     * Flujo: valida estado, recalcula totales y reemplaza detalles en transaccion.
     */
    public function confirmVenta(Venta $venta): Venta
    {
        if ($venta->estado !== Venta::EstadoBorrador) {
            return $this->ventas->findWithRelations($venta);
        }

        return $this->ventas->updateStatus(
            $venta,
            Venta::EstadoConfirmada,
            null
        );
    }

    /**
     * Funcionamiento: confirma venta en borrador y carga relaciones.
     * Tablas: ventas, detalles_venta, clientes, cajas.
     * Flujo: si esta en borrador, actualiza estado; si no, retorna venta actual.
     */
    public function closeVenta(Venta $venta): Venta
    {
        if ($venta->estado !== Venta::EstadoConfirmada) {
            throw ValidationException::withMessages([
                'estado' => 'Solo se pueden cerrar ventas confirmadas.',
            ]);
        }

        return DB::transaction(function () use ($venta): Venta {
            $ventaConDetalles = $this->ventas->findWithRelations($venta);

            if ($ventaConDetalles->detalles->isEmpty()) {
                throw ValidationException::withMessages([
                    'detalles' => 'La venta no tiene detalles para cerrar.',
                ]);
            }

            foreach ($ventaConDetalles->detalles as $detalle) {
                $this->inventoryService->registerSalidaFromVenta($ventaConDetalles, $detalle);
            }

            $ventaCerrada = $this->ventas->updateStatus(
                $ventaConDetalles,
                Venta::EstadoCerrada,
                now()->toDateTimeString()
            );

            $this->ensureCuentaPorCobrar($ventaCerrada);

            return $ventaCerrada;
        });
    }

    /**
     * Funcionamiento: cierra la venta, descuenta inventario y asegura la cuenta por cobrar.
     * Tablas: ventas, detalles_venta, movimientos_inventario, cuenta_por_cobrar, productos.
     * Flujo: valida estado, carga detalles, registra salidas, marca cierre y crea saldo pendiente.
     */
    private function filtrarVentaData(array $data): array
    {
        return Arr::only($data, ['user_id', 'cliente_id', 'descuento', 'observaciones']);
    }

    /**
     * Funcionamiento: filtra campos permitidos para persistir ventas.
     * Tablas: ventas.
     * Flujo: limita el payload a columnas aprobadas antes de crear/actualizar.
     */
    private function resolveCajaParaUsuario(int $userId): Caja
    {
        if ($userId < 1) {
            throw ValidationException::withMessages([
                'user_id' => 'Usuario no autenticado para resolver caja.',
            ]);
        }

        $caja = Caja::query()
            ->where('user_id_open', $userId)
            ->whereNull('closed_at')
            ->first();

        if ($caja !== null) {
            return $caja;
        }

        return Caja::query()->create([
            'user_id_open' => $userId,
            'opened_at' => now()->toDateTimeString(),
            'saldo_apertura' => 0,
            'status' => 'open',
        ]);
    }

    /**
     * Funcionamiento: obtiene o crea caja abierta para el usuario vendedor.
     * Tablas: cajas.
     * Flujo: consulta caja activa; si no existe, crea una nueva con saldo inicial.
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
                'detalles' => 'Debes agregar al menos un producto a la venta.',
            ]);
        }

        return $normalizados;
    }

    /**
     * Funcionamiento: valida y normaliza el detalle de venta con subtotal por linea.
     * Tablas: detalles_venta.
     * Flujo: filtra productos validos, recalcula cantidades y subtotal y devuelve arreglo limpio.
     */
    private function calcularTotal(array $detalles): float
    {
        return (float) collect($detalles)->sum('subtotal');
    }

 /**
     * Descripción: Asegura la existencia de una cuenta por cobrar vinculada a la venta con montos fiscales en Quetzales.
     */
    private function ensureCuentaPorCobrar(Venta $venta): CuentaPorCobrar
    {
        $existing = CuentaPorCobrar::query()->where('id_venta', $venta->getKey())->first();

        if ($existing !== null) {
            return $existing;
        }

        // El total ya viene neteado con descuentos globales en Quetzales (Q)
        return CuentaPorCobrar::query()->create([
            'id_venta'       => $venta->getKey(),
            'monto_original' => (float) $venta->total,
            'saldo'          => (float) $venta->total,
            'estado'         => CuentaPorCobrar::EstadoPendiente,
            'observaciones'  => 'Cuenta generada automáticamente al cerrar la venta comercial.',
        ]);
    }
}

    /**
     * Funcionamiento: crea la cuenta por cobrar si no existe para la venta cerrada.
     * Tablas: cuenta_por_cobrar, ventas.
     * Flujo: busca por id_venta y, si no hay registro, crea saldo pendiente con total de venta.
     */