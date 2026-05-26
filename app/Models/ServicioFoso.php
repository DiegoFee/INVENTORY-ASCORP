<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Autor: Ferlandy
 * Descripción: Modelo de servicios realizados en el foso.
 */
class ServicioFoso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'servicios_foso';

    protected $fillable = [
        'cliente_id',
        'user_id',
        'placa_vehiculo',
        'fecha',
        'estado',
        'total',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'total' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function tecnico(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleServicio::class, 'servicio_id');
    }
}