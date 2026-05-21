<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * // Autor: Diego Méndez - Fecha: 20/05/2026
 */
class Producto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'productos';

    protected $fillable = [
        'sku',
        'nombre',
        'stock_actual',
        'stock_minimo',
        'precio_costo',
        'precio_venta',
        'unidad_medida',
        'categoria',
        'activo',
    ];

    protected function casts(): array
    {
        return [
            'stock_actual' => 'integer',
            'stock_minimo' => 'integer',
            'precio_costo' => 'decimal:2',
            'precio_venta' => 'decimal:2',
            'activo' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function detallesVenta(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function detallesCompra(): HasMany
    {
        return $this->hasMany(DetalleCompra::class);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function detallesServicio(): HasMany
    {
        return $this->hasMany(DetalleServicio::class);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function scopeLowStock(Builder $query): Builder
    {
        return $query->whereColumn('stock_actual', '<=', 'stock_minimo');
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('activo', true);
    }
}
