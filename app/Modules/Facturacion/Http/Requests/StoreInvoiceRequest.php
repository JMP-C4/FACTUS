<?php

declare(strict_types=1);

namespace App\Modules\Facturacion\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
            'payment_form_code' => ['nullable', 'string', 'max:10'],
            'payment_method_code' => ['nullable', 'string', 'max:20'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'integer', 'exists:products,id'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'items.*.discount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_code' => ['nullable', 'string', 'max:20'],
            'items.*.unit_measure_code' => ['nullable', 'string', 'max:10'],

            'payments' => ['required', 'array', 'min:1'],
            'payments.*.payment_form_code' => ['nullable', 'string', 'max:10'],
            'payments.*.payment_method_code' => ['required', 'string', 'max:20'],
            'payments.*.amount' => ['required', 'numeric', 'min:0'],
            'payments.*.reference_number' => ['nullable', 'string', 'max:100'],
            'payments.*.cash_received' => ['nullable', 'numeric', 'min:0'],
            'payments.*.cash_change' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Debes agregar al menos un ítem a la factura.',
            'items.*.description.required' => 'Cada ítem debe tener una descripción.',
            'items.*.quantity.min' => 'La cantidad debe ser al menos 1.',
            'payments.required' => 'Debes registrar al menos un pago.',
            'payments.*.payment_method_code.required' => 'Cada pago debe indicar un método.',
        ];
    }
}
