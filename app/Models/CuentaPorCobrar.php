<?php

/** Autor: Arandi Hurtado, Fecha: 21/05/2026, Descripción: Modelo de cuentas por cobrar vinculado a ventas y clientes. */

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class CuentaPorCobrar extends Model
{
    use HasFactory, SoftDeletes;

    public const EstadoPendiente = 'pendiente';
    public const EstadoPagada = 'pagada';

    protected $table = 'cuentas_por_cobrar';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'venta_id',
        'cliente_id',
        'total',
        'saldo',
        'fecha_vencimiento',
        'estado',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'saldo' => 'decimal:2',
            'fecha_vencimiento' => 'date',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Funcionamiento: relaciona la cuenta por cobrar con su venta origen.
     */
    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    /**
     * Funcionamiento: relaciona la cuenta por cobrar con el cliente deudor.
     */
    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    /**
     * Funcionamiento: obtiene todos los pagos aplicados a esta cuenta.
     */
    public function pagos(): HasMany
    {
        return $this->hasMany(Pago::class, 'cuenta_id');
    }
}