<?php

declare(strict_types=1);

/** Autor: Celvin Arandi, Descripcion: Servicio de reportes comerciales con filtros dinamicos. */

namespace App\Services;

use App\Models\CuentaPorCobrar;
use App\Models\MovimientoInventario;
use App\Models\Producto;
use App\Models\Venta;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Throwable;

class ReportService
{
    public function __construct(
        private readonly Venta $ventaModel,
        private readonly Producto $productoModel,
        private readonly MovimientoInventario $movimientoModel,
        private readonly CuentaPorCobrar $cuentaModel
    ) {}

    /**
     * Funcionamiento: aplica filtros de ventas cerradas y calcula sumatorias totales.
     *
     * @param  array{fecha_inicio?:string|null, fecha_fin?:string|null, cliente_id?:int|string|null, tipo_pago?:string|null}  $filters
     * @return array{title:string, filters:array{fecha_inicio:?string, fecha_fin:?string, cliente_id:?int, tipo_pago:?string}, rows:Collection<int, Venta>, totals:array{cantidad:int, total_ventas:float}}
     */
    public function getVentasReportData(array $filters): array
    {
        $fechaInicio = $this->normalizeDate($filters['fecha_inicio'] ?? null);
        $fechaFin = $this->normalizeDate($filters['fecha_fin'] ?? null);
        $clienteId = $this->normalizeInt($filters['cliente_id'] ?? null);
        $tipoPago = $this->normalizeString($filters['tipo_pago'] ?? null);

        $query = $this->ventaModel->newQuery()
            ->select(['id', 'cliente_id', 'total', 'descuento', 'estado', 'closed_at', 'tipo_pago', 'created_at'])
            ->with('cliente')
            ->where('estado', Venta::EstadoCerrada)
            ->when($fechaInicio, function (Builder $query, string $fechaInicio): void {
                $query->whereDate('closed_at', '>=', $fechaInicio);
            })
            ->when($fechaFin, function (Builder $query, string $fechaFin): void {
                $query->whereDate('closed_at', '<=', $fechaFin);
            })
            ->when($clienteId, function (Builder $query, int $clienteId): void {
                $query->where('cliente_id', $clienteId);
            })
            ->when($tipoPago, function (Builder $query, string $tipoPago): void {
                $query->where('tipo_pago', $tipoPago);
            });

        $totalVentas = (float) (clone $query)->sum('total');
        $ventas = $query->orderByDesc('closed_at')->get();

        return [
            'title' => 'Reporte de Ventas',
            'filters' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'cliente_id' => $clienteId,
                'tipo_pago' => $tipoPago,
            ],
            'rows' => $ventas,
            'totals' => [
                'cantidad' => $ventas->count(),
                'total_ventas' => $totalVentas,
            ],
        ];
    }

    /**
     * Funcionamiento: obtiene stock actual y cruza productos con movimientos criticos recientes.
     *
     * @param  array{stock_bajo?:bool|string|int|null, producto_id?:int|string|null}  $filters
     * @return array{title:string, filters:array{stock_bajo:bool, producto_id:?int}, rows:Collection<int, Producto>, movimientos_criticos:Collection<int, Collection<int, MovimientoInventario>>, totals:array{total_productos:int, total_stock:int}}
     */
    public function getInventoryReportData(array $filters): array
    {
        $stockBajo = $this->normalizeBoolean($filters['stock_bajo'] ?? false);
        $productoId = $this->normalizeInt($filters['producto_id'] ?? null);

        $productos = $this->productoModel->newQuery()
            ->select([
                'id',
                'sku',
                'nombre',
                'stock_actual',
                'stock_minimo',
                'precio_costo',
                'precio_venta',
                'unidad_medida',
                'categoria',
                'activo',
            ])
            ->when($productoId, function (Builder $query, int $productoId): void {
                $query->whereKey($productoId);
            })
            ->when($stockBajo, function (Builder $query): void {
                $query->lowStock();
            })
            ->orderBy('nombre')
            ->get();

        $movimientosCriticos = collect();

        if ($productos->isNotEmpty()) {
            $productosPorId = $productos->keyBy('id');
            $movimientos = $this->movimientoModel->newQuery()
                ->with('producto')
                ->whereIn('producto_id', $productos->pluck('id')->all())
                ->orderByDesc('id')
                ->limit(200)
                ->get();

            $movimientosCriticos = $movimientos
                ->filter(function (MovimientoInventario $movimiento) use ($productosPorId): bool {
                    $producto = $productosPorId->get($movimiento->producto_id);

                    return $producto !== null && $movimiento->stock_nuevo <= $producto->stock_minimo;
                })
                ->groupBy('producto_id')
                ->map(fn (Collection $items): Collection => $items->take(5)->values());
        }

        return [
            'title' => 'Reporte de Inventario',
            'filters' => [
                'stock_bajo' => $stockBajo,
                'producto_id' => $productoId,
            ],
            'rows' => $productos,
            'movimientos_criticos' => $movimientosCriticos,
            'totals' => [
                'total_productos' => $productos->count(),
                'total_stock' => (int) $productos->sum('stock_actual'),
            ],
        ];
    }

    /**
     * Funcionamiento: filtra cuentas por cobrar y calcula saldos acumulados vigentes.
     *
     * @param  array{cliente_id?:int|string|null, estado?:string|null, vencidos?:bool|string|int|null}  $filters
     * @return array{title:string, filters:array{cliente_id:?int, estado:?string, vencidos:bool}, rows:Collection<int, CuentaPorCobrar>, totals:array{cantidad:int, total_saldo:float}}
     */
    public function getCxcReportData(array $filters): array
    {
        $clienteId = $this->normalizeInt($filters['cliente_id'] ?? null);
        $estado = $this->normalizeCxcEstado($filters['estado'] ?? null);
        $vencidos = $this->normalizeBoolean($filters['vencidos'] ?? false);

        $query = $this->cuentaModel->newQuery()
            ->select(['id', 'venta_id', 'cliente_id', 'total', 'saldo', 'fecha_vencimiento', 'estado', 'created_at'])
            ->with(['cliente', 'venta'])
            ->when($clienteId, function (Builder $query, int $clienteId): void {
                $query->where('cliente_id', $clienteId);
            })
            ->when($estado, function (Builder $query, string $estado): void {
                if ($estado === 'saldada') {
                    $query->whereIn('estado', ['saldada', 'pagada']);

                    return;
                }

                $query->where('estado', $estado);
            })
            ->when($vencidos, function (Builder $query): void {
                $query->whereDate('fecha_vencimiento', '<', Carbon::today()->toDateString());
            });

        $totalSaldo = (float) (clone $query)->sum('saldo');
        $cuentas = $query->orderByDesc('fecha_vencimiento')->get();

        return [
            'title' => 'Reporte de Cuentas por Cobrar',
            'filters' => [
                'cliente_id' => $clienteId,
                'estado' => $estado,
                'vencidos' => $vencidos,
            ],
            'rows' => $cuentas,
            'totals' => [
                'cantidad' => $cuentas->count(),
                'total_saldo' => $totalSaldo,
            ],
        ];
    }

    private function normalizeDate(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (Throwable $exception) {
            return null;
        }
    }

    private function normalizeInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }

        if (is_numeric($value)) {
            $intValue = (int) $value;

            return $intValue > 0 ? $intValue : null;
        }

        return null;
    }

    private function normalizeString(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function normalizeBoolean(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value)) {
            return $value === 1;
        }

        if (is_string($value)) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return false;
    }

    private function normalizeCxcEstado(mixed $value): ?string
    {
        if (! is_string($value)) {
            return null;
        }

        $estado = trim($value);

        if ($estado === '') {
            return null;
        }

        if ($estado === 'pagada') {
            return 'saldada';
        }

        return $estado;
    }
}
