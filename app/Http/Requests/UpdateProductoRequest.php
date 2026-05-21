<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * // Autor: Diego Méndez - Fecha: 20/05/2026
 */
class UpdateProductoRequest extends FormRequest
{
    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::Admin, Role::Warehouse) ?? false;
    }

    /**
     * // Autor: Diego Méndez - Fecha: 20/05/2026
     */
    public function rules(): array
    {
        $producto = $this->route('producto');

        return [
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('productos', 'sku')->ignore($producto),
            ],
            'nombre' => ['required', 'string', 'max:255'],
            'stock_actual' => ['required', 'integer', 'min:0'],
            'stock_minimo' => ['required', 'integer', 'min:0'],
            'precio_costo' => ['required', 'numeric', 'min:0'],
            'precio_venta' => ['required', 'numeric', 'min:0', 'gte:precio_costo'],
            'unidad_medida' => ['required', 'string', 'max:50'],
            'categoria' => ['required', 'string', 'max:100'],
            'activo' => ['sometimes', 'boolean'],
        ];
    }
}
