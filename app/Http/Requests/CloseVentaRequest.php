<?php

namespace App\Http\Requests;

use App\Enums\Permission;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class CloseVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Gate::allows(Permission::VentasClose->value);
    }

    public function rules(): array
    {
        return [];
    }
}
