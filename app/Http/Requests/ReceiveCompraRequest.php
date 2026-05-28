<?php

namespace App\Http\Requests;

use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

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
        return Gate::allows(Permission::ComprasReceive->value);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function rules(): array
    {
        return [];
    }
}
