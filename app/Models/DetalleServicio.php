<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Autor: Ferlandy
 * Descripción: Modelo de detalles (insumos) de un servicio de foso.
 */
class DetalleServicio extends Model
{
    use HasFactory;

    protected $table = 'detalle_servicio';

    protected $fillable = [
        'servicio_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'decimal:2',
        'subtotal' => 'decimal:2',
    ];

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(ServicioFoso::class, 'servicio_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
