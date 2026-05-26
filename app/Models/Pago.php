<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'cuenta_id', 'monto', 'fecha_pago', 'metodo_pago', 'referencia', 'observaciones',
    ];

    protected $casts = [
        'monto' => 'decimal:2',
        'fecha_pago' => 'date',
    ];

    public function cuenta(): BelongsTo
    {
        return $this->belongsTo(CuentaPorCobrar::class, 'cuenta_id');
    }
}
