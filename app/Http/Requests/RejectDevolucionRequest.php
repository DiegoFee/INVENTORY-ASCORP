<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;

class RejectDevolucionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::Admin, Role::Seller) ?? false;
    }

    public function rules(): array
    {
        return [
            'motivo' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
