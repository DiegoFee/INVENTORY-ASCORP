<?php

namespace App\Http\Requests;

use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class RejectDevolucionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows(Permission::DevolucionesReject->value);
    }

    public function rules(): array
    {
        return [
            'motivo' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
