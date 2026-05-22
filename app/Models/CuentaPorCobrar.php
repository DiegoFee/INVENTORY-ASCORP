<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CuentaPorCobrar extends Model
{
    use SoftDeletes;

    protected $table = 'cuentas_por_cobrar';

    protected $fillable = [
        'venta_id',
        'cliente_id',
        'total',
        'saldo',
        'fecha_vencimiento',
        'estado'
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'saldo' => 'decimal:2',
        'fecha_vencimiento' => 'date',
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class, 'cuenta_id');
    }
}