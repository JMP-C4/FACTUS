<?php

namespace App\Modules\Productos\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $companyId = $this->user()->company_id;

        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'sku' => [
                'required', 'string', 'max:50',
                Rule::unique('products', 'sku')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'barcode' => [
                'nullable', 'string', 'max:50',
                Rule::unique('products', 'barcode')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'unit_measure_code' => ['required', 'string', 'max:10'],
            'standard_code' => ['nullable', 'string', 'max:10'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'tax_id' => ['nullable', 'integer', 'exists:taxes,id'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'minimum_stock' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'El nombre del producto es obligatorio.',
            'sku.required' => 'El SKU es obligatorio.',
            'sku.unique' => 'Este SKU ya existe en tu catálogo.',
            'barcode.unique' => 'Este código de barras ya existe en tu catálogo.',
            'price.required' => 'El precio es obligatorio.',
            'price.min' => 'El precio no puede ser negativo.',
            'tax_rate.max' => 'La tasa de impuesto no puede superar el 100%.',
            'minimum_stock.min' => 'El stock mínimo no puede ser negativo.',
        ];
    }
}
