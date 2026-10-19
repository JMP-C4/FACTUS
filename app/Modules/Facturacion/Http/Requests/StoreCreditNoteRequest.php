<?php

declare(strict_types=1);

namespace App\Modules\Facturacion\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreCreditNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'correction_concept_code' => ['required', 'string', 'in:1,2,3,4,5'],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'correction_concept_code.required' => 'Debes seleccionar el concepto de la corrección.',
            'correction_concept_code.in' => 'El concepto de corrección no es válido.',
            'reason.required' => 'Debes indicar el motivo de la nota de crédito.',
            'reason.max' => 'El motivo no puede superar los 1000 caracteres.',
        ];
    }
}
