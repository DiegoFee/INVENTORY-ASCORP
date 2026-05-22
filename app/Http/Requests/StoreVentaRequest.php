<?php

namespace App\Http\Requests;

use App\Models\Role;
use App\Models\Venta;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::Admin, Role::Seller) ?? false;
    }

    public function rules(): array
    {
        return [
            'cliente_id' => ['nullable', 'integer', 'exists:clientes,id'],
            'descuento' => ['nullable', 'numeric', 'min:0'],
            'estado' => ['nullable', 'string', Rule::in([Venta::EstadoBorrador, Venta::EstadoConfirmada])],
            'observaciones' => ['nullable', 'string', 'max:1000'],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.producto_id' => ['required', 'integer', 'exists:productos,id'],
            'detalles.*.cantidad' => ['required', 'integer', 'min:1'],
            'detalles.*.precio_unitario' => ['required', 'numeric', 'min:0'],
            'detalles.*.descuento' => ['nullable', 'numeric', 'min:0'],
        ];
    }

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
