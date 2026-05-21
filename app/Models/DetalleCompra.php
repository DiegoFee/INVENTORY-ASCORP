<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * // Autor: Diego Méndez - Fecha: 21/05/2026
 */
class DetalleCompra extends Model
{
    use HasFactory;

    protected $table = 'detalles_compra';

    protected $fillable = [
        'compra_id',
        'producto_id',
        'cantidad',
        'precio_costo',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'precio_costo' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function compra(): BelongsTo
    {
        return $this->belongsTo(Compra::class, 'compra_id');
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
