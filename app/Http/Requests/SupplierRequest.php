<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $supplier = $this->route('supplier'); // Obtiene el modelo o el ID

        return [
            'nombre' => 'required|string|max:255',
            'nit' => [
                'required',
                'string',
                'max:20',
                Rule::unique('proveedores', 'nit')->ignore($supplier)
            ],
            'telefono' => 'required|string|max:20',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('proveedores', 'email')->ignore($supplier)
            ],
            'direccion' => 'required|string',
            'contacto_nombre' => 'nullable|string|max:255',
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre del proveedor es obligatorio.',
            'nit.required' => 'El NIT es obligatorio.',
            'nit.unique' => 'Este NIT ya está registrado.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingrese un correo electrónico válido.',
            'email.unique' => 'Este correo ya está registrado.',
            'direccion.required' => 'La dirección es obligatoria.',
        ];
    }
}