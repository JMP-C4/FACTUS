<?php

namespace App\Modules\Usuarios\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('users.create') ?? false;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')],
            'password' => ['required', 'confirmed', Password::min(8)],
            'company_id' => ['nullable', 'integer', 'exists:companies,id'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'role_id' => ['nullable', 'integer', 'exists:roles,id'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['integer', 'exists:permissions,id'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'company_id' => $this->input('company_id') ?: null,
            'branch_id' => $this->input('branch_id') ?: null,
            'role_id' => $this->input('role_id') ?: null,
        ]);
    }
}
