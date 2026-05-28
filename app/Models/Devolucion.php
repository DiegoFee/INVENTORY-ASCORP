<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * // Autor: Diego Méndez - Fecha: 21/05/2026
 */
class Devolucion extends Model
{
    use HasFactory, SoftDeletes;

    public const EstadoPendiente = 'pendiente';

    public const EstadoProcesada = 'procesada';

    public const EstadoRechazada = 'rechazada';

    protected $table = 'devoluciones';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'venta_id',
        'user_id',
        'monto',
        'motivo',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'monto' => 'decimal:2',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
        ];
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleDevolucion::class, 'devolucion_id');
    }

    public function scopeWhereOwnedBy(Builder $query, User $user): Builder
    {
        if ($user->isAdministrator()) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }
}
