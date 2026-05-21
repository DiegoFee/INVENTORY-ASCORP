<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * // Autor: Diego Méndez - Fecha: 21/05/2026
 */
class MovimientoInventario extends Model
{
    use HasFactory;

    public const TipoEntrada = 'entrada';

    public const TipoSalida = 'salida';

    public const OrigenCompra = 'compra';

    public const OrigenManual = 'manual';

    public const OrigenSalida = 'salida';

    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'producto_id',
        'compra_id',
        'user_id',
        'tipo',
        'origen',
        'cantidad',
        'stock_anterior',
        'stock_nuevo',
        'costo_unitario',
        'observaciones',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'stock_anterior' => 'integer',
            'stock_nuevo' => 'integer',
            'costo_unitario' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
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
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
