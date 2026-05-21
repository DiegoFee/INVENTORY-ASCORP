<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Nombre de la tabla asociada.
     */
    protected $table = 'proveedores';

    /**
     * Los atributos que se pueden asignar masivamente.
     */
    protected $fillable = [
        'nombre',
        'nit',
        'telefono',
        'email',
        'direccion',
        'contacto_nombre',
    ];

    /**
     * Los atributos que deben ser ocultos para las matrices.
     */
    protected $hidden = [];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Scope para búsqueda por nombre, NIT o email.
     */
    public function scopeSearch($query, $term)
    {
        return $query->where('nombre', 'LIKE', "%{$term}%")
            ->orWhere('nit', 'LIKE', "%{$term}%")
            ->orWhere('email', 'LIKE', "%{$term}%");
    }

    // Relaciones futuras (comentadas hasta que exista el modelo Compra)
    // public function compras()
    // {
    //     return $this->hasMany(Compra::class);
    // }
}
