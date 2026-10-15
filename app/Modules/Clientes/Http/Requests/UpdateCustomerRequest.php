<?php

namespace App\Modules\Clientes\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $customer = $this->route('customer');
        $companyId = $this->user()->company_id;

        return [
            'identification_document_code' => ['sometimes', 'required', 'string', 'max:10'],
            'document_number' => [
                'sometimes',
                'required',
                'string',
                'max:20',
                Rule::unique('customers', 'document_number')
                    ->where('company_id', $companyId)
                    ->ignore($customer->id),
            ],
            'legal_organization_code' => ['sometimes', 'required', 'string', 'max:10'],
            'tribute_code' => ['sometimes', 'required', 'string', 'max:10'],
            'municipality_code' => ['nullable', 'string', 'max:10'],
            'first_name' => ['sometimes', 'required', 'string', 'max:100'],
            'last_name' => ['nullable', 'string', 'max:100'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'billing_email' => ['nullable', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'identification_document_code.required' => 'El tipo de documento es obligatorio.',
            'document_number.required' => 'El número de documento es obligatorio.',
            'document_number.unique' => 'Este número de documento ya existe en tu catálogo.',
            'legal_organization_code.required' => 'El tipo de organización legal es obligatorio.',
            'tribute_code.required' => 'El tipo de tributo es obligatorio.',
            'first_name.required' => 'El nombre es obligatorio.',
            'email.email' => 'El correo electrónico no es válido.',
            'billing_email.email' => 'El correo de facturación no es válido.',
        ];
    }
}
