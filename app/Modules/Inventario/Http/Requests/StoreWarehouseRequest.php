<?php

declare(strict_types=1);

namespace App\Modules\Inventario\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('warehouses', 'code')->where('company_id', $this->user()->company_id),
            ],
            'is_default' => ['nullable', 'boolean'],
        ];
    }
}
