<?php

namespace App\Models;

use Database\Factories\ClienteFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

// Autor: Celvin
// Descripcion: Modelo para clientes.
class Cliente extends Model
{
    /** @use HasFactory<ClienteFactory> */
    use HasFactory;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'nit',
        'nombre',
        'direccion',
        'telefono',
    ];
}
