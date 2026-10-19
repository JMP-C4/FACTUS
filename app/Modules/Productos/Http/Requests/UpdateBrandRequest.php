<?php

declare(strict_types=1);

namespace App\Modules\Productos\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('brands', 'name')
                    ->where('company_id', $this->user()->company_id)
                    ->ignore($this->route('brand')->id),
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
