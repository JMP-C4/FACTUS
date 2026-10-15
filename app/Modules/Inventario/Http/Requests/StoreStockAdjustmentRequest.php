<?php

namespace App\Modules\Inventario\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'product_id' => ['required', 'exists:products,id'],
            'new_quantity' => ['required', 'integer', 'min:0'],
            'reason' => ['required', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'warehouse_id.required' => 'El almacén es obligatorio.',
            'product_id.required' => 'El producto es obligatorio.',
            'new_quantity.required' => 'La cantidad nueva es obligatoria.',
            'new_quantity.min' => 'La cantidad no puede ser negativa.',
            'reason.required' => 'El motivo del ajuste es obligatorio.',
        ];
    }
}
