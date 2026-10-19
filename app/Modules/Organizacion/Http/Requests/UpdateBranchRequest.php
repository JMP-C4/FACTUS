<?php

namespace App\Modules\Organizacion\Http\Requests;

use App\Models\Branch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var Branch $branch */
        $branch = $this->route('branch');

        return [
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:10', Rule::unique('branches', 'code')->ignore($branch->id)],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'pos_prefix' => ['required', 'string', 'max:3'],
            'resolution_from' => ['required', 'integer', 'min:1'],
            'resolution_to' => ['required', 'integer', 'gte:resolution_from'],
            'resolution_date' => ['nullable', 'date'],
            'allows_negative_stock' => ['boolean'],
            'is_main' => ['boolean'],
            'is_warehouse' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'allows_negative_stock' => $this->boolean('allows_negative_stock'),
            'is_main' => $this->boolean('is_main'),
            'is_warehouse' => $this->boolean('is_warehouse'),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre de la sucursal es obligatorio.',
            'code.required' => 'El código de la sucursal es obligatorio.',
            'code.unique' => 'Ya existe una sucursal con este código.',
            'pos_prefix.required' => 'El prefijo POS es obligatorio.',
            'resolution_to.gte' => 'El rango final debe ser mayor o igual al inicial.',
        ];
    }
}
