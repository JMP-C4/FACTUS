<?php

declare(strict_types=1);

namespace App\Modules\Productos\Http\Requests;

use App\Modules\Productos\Models\Tax;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaxRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'name' => ['required', 'string', 'max:100'],
            'code' => [
                'required', 'string', 'max:30',
                Rule::unique('taxes', 'code')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'type' => ['required', 'string', Rule::in([Tax::TYPE_VAT, Tax::TYPE_EXEMPT, Tax::TYPE_EXCLUDED])],
            'description' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del impuesto es obligatorio.',
            'code.required' => 'El código del impuesto es obligatorio.',
            'code.unique' => 'Ya existe un impuesto con este código.',
            'rate.required' => 'La tarifa es obligatoria.',
            'rate.min' => 'La tarifa no puede ser negativa.',
            'rate.max' => 'La tarifa no puede superar el 100%.',
            'type.required' => 'El tipo de impuesto es obligatorio.',
            'type.in' => 'El tipo de impuesto no es válido.',
        ];
    }
}
