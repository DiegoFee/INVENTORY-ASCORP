<?php

namespace App\Models;

use App\Enums\CompraEstadoEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * // Autor: Diego Méndez - Fecha: 21/05/2026
 */
class Compra extends Model
{
    use HasFactory, SoftDeletes;

    public const EstadoBorrador = CompraEstadoEnum::BORRADOR;

    public const EstadoConfirmada = CompraEstadoEnum::CONFIRMADA;

    public const EstadoRecibida = CompraEstadoEnum::RECIBIDA;

    protected $table = 'compras';

    protected $fillable = [
        'proveedor_id',
        'codigo',
        'estado',
        'fecha_compra',
        'fecha_recepcion',
        'observaciones',
        'total',
    ];

    protected function casts(): array
    {
        return [
            'estado' => CompraEstadoEnum::class,
            'fecha_compra' => 'date',
            'fecha_recepcion' => 'datetime',
            'total' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Supplier::class, 'proveedor_id');
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleCompra::class, 'compra_id');
    }
}
