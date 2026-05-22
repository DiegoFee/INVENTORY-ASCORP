<?php

/** Autor: Arandi Hurtado, Fecha: 21/05/2026, Descripción: Modelo de cuentas por cobrar vinculadas a ventas. */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuentaPorCobrar extends Model
{
    use HasFactory;

    public const EstadoPendiente = 'pendiente';

    public const EstadoPagada = 'pagada';

    protected $table = 'cuenta_por_cobrar';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'id_venta',
        'monto_original',
        'saldo',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'monto_original' => 'decimal:2',
            'saldo' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Funcionamiento: tipa los montos y fechas para consistencia en calculos y vistas.
     * Tablas: cuenta_por_cobrar.
     * Flujo: Eloquent castea valores al leer/escribir el modelo.
     */
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'id_venta');
    }

    /**
     * Funcionamiento: relaciona la cuenta por cobrar con su venta origen.
     * Tablas: cuenta_por_cobrar, ventas.
     * Flujo: usa id_venta como llave foranea para resolver el registro de ventas.
     */
}
