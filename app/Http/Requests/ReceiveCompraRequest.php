<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

/**
 * // Autor: Diego Méndez - Fecha: 21/05/2026
 */
class ReceiveCompraRequest extends FormRequest
{
    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::Admin, Role::Warehouse) ?? false;
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function rules(): array
    {
        return [];
    }
}
