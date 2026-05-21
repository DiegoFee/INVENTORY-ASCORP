<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * // Autor: Diego Méndez - Fecha: 21/05/2026
 */
class Venta extends Model
{
    use HasFactory, SoftDeletes;

    public const EstadoBorrador = 'borrador';

    public const EstadoConfirmada = 'confirmada';

    public const EstadoCerrada = 'cerrada';

    protected $table = 'ventas';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'caja_id',
        'user_id',
        'cliente_id',
        'total',
        'descuento',
        'estado',
        'observaciones',
        'opened_at',
        'closed_at',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'descuento' => 'decimal:2',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class, 'venta_id');
    }

    public function devoluciones(): HasMany
    {
        return $this->hasMany(Devolucion::class, 'venta_id');
    }
}
