<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * // Autor: Diego Méndez - Fecha: 26/05/2026
 */
class Alerta extends Model
{
    use HasFactory;

    public const TipoBajo = 'bajo';

    public const TipoCritico = 'critico';

    protected $table = 'alertas';

    protected $fillable = [
        'producto_id',
        'user_id',
        'tipo',
        'mensaje',
        'leida',
    ];

    protected function casts(): array
    {
        return [
            'leida' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * // Autor: Diego Méndez - Fecha: 26/05/2026
     */
    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    /**
     * // Autor: Diego Méndez - Fecha: 26/05/2026
     */
    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
