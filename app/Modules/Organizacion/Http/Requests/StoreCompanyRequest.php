<?php

namespace App\Modules\Organizacion\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCompanyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nit' => ['required', 'string', 'max:20', 'unique:companies,nit'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'regime' => ['required', 'in:comun,simplificado'],
            'is_active' => ['boolean'],
            'trial_ends_at' => ['nullable', 'date'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la compañía es obligatorio.',
            'nit.required' => 'El NIT es obligatorio.',
            'nit.unique' => 'Ya existe una compañía con este NIT.',
            'regime.in' => 'El régimen debe ser común o simplificado.',
        ];
    }
}
