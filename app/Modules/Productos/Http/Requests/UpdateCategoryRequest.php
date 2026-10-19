<?php

declare(strict_types=1);

namespace App\Modules\Productos\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCategoryRequest extends FormRequest
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
                Rule::unique('categories', 'name')
                    ->where('company_id', $this->user()->company_id)
                    ->ignore($this->route('category')->id),
            ],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
