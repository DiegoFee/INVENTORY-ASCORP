<?php

namespace App\Http\Requests;

use App\Models\Supplier;
use Illuminate\Foundation\Http\FormRequest;

class SupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $supplier = $this->route('supplier'); // null en creación, modelo en edición

        return [
            'nombre' => 'required|string|max:255',

            'nit' => [
                'required',
                'string',
                'max:20',
                function ($attribute, $value, $fail) use ($supplier) {
                    $query = Supplier::where('nit', $value)->whereNull('deleted_at');
                    if ($supplier) {
                        $query->where('id', '!=', $supplier->id);
                    }
                    if ($query->exists()) {
                        $fail('Este NIT ya está registrado en un proveedor activo.');
                    }
                },
            ],

            'telefono' => 'required|string|max:20',

            'email' => [
                'required',
                'email',
                'max:255',
                function ($attribute, $value, $fail) use ($supplier) {
                    $query = Supplier::where('email', $value)->whereNull('deleted_at');
                    if ($supplier) {
                        $query->where('id', '!=', $supplier->id);
                    }
                    if ($query->exists()) {
                        $fail('Este correo ya está registrado en un proveedor activo.');
                    }
                },
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
            'telefono.required' => 'El teléfono es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'Ingrese un correo electrónico válido.',
            'direccion.required' => 'La dirección es obligatoria.',
        ];
    }
}
