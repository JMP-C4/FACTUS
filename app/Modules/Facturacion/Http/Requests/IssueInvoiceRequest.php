<?php

declare(strict_types=1);

namespace App\Modules\Facturacion\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class IssueInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_id' => ['required', 'integer', 'exists:invoices,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'invoice_id.required' => 'Debe especificar la factura a emitir.',
            'invoice_id.exists' => 'La factura especificada no existe.',
        ];
    }
}
