<?php

namespace App\Http\Requests;

use App\Enums\Permission;
use App\Models\Compra;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * // Autor: Diego Méndez - Fecha: 21/05/2026
 */
class UpdateCompraRequest extends FormRequest
{
    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function authorize(): bool
    {
        return Gate::allows(Permission::ComprasUpdate->value);
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function rules(): array
    {
        return [
            'proveedor_id' => ['required', 'integer', 'exists:proveedores,id'],
            'estado' => ['required', 'string', Rule::in([Compra::EstadoBorrador, Compra::EstadoConfirmada])],
            'fecha_compra' => ['nullable', 'date'],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.producto_id' => ['required', 'integer', 'exists:productos,id'],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
            'detalles.*.precio_costo' => ['required', 'numeric', 'min:0'],
        ];
    }

    /**
     * // Autor: Diego Méndez - Fecha: 21/05/2026
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $detalles = $this->input('detalles', []);
            $validos = collect($detalles)
                ->filter(fn (array $detalle): bool => ! empty($detalle['producto_id']))
                ->count();

            if ($validos === 0) {
                $validator->errors()->add('detalles', 'Debes agregar al menos un producto.');
            }
        }];
    }
}
