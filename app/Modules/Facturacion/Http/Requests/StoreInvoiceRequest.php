<?php

declare(strict_types=1);

namespace App\Modules\Facturacion\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    /**
     * Tolerancia monetaria aceptada al comparar la suma de pagos con el total.
     */
    private const PAYMENT_TOLERANCE = 0.01;

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
            'payments.*.payment_method_code' => ['required', 'string', Rule::in(array_keys(config('billing.payment_methods')))],
            'payments.*.amount' => ['required', 'numeric', 'gt:0'],
            'payments.*.reference_number' => ['nullable', 'string', 'max:100'],
            'payments.*.card_last_four' => ['nullable', 'string', 'max:4'],
            'payments.*.card_approval_code' => ['nullable', 'string', 'max:50'],
            'payments.*.bank_entity' => ['nullable', 'string', 'max:100'],
            'payments.*.cash_received' => ['nullable', 'numeric', 'min:0'],
            'payments.*.cash_change' => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $this->validatePaymentMethods($validator);
            $this->validatePaymentTotal($validator);
        });
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Debes agregar al menos un ítem a la factura.',
            'items.*.description.required' => 'Cada ítem debe tener una descripción.',
            'items.*.quantity.min' => 'La cantidad debe ser al menos 1.',
            'payments.required' => 'Debes registrar al menos un pago.',
            'payments.*.payment_method_code.required' => 'Cada pago debe indicar un método.',
            'payments.*.payment_method_code.in' => 'El método de pago no es válido.',
            'payments.*.amount.gt' => 'Cada pago debe tener un monto mayor a cero.',
        ];
    }

    /**
     * Valida los campos condicionales según el método de pago.
     */
    private function validatePaymentMethods(Validator $validator): void
    {
        foreach ($this->input('payments', []) as $index => $payment) {
            $method = $payment['payment_method_code'] ?? null;
            $amount = (float) ($payment['amount'] ?? 0);

            if ($method === 'cash') {
                $this->validateCashPayment($validator, $index, $payment, $amount);
            }

            if (in_array($method, ['debit_card', 'credit_card'], true)) {
                $this->validateCardPayment($validator, $index, $payment);
            }

            if ($method === 'transfer') {
                $this->validateTransferPayment($validator, $index, $payment);
            }
        }
    }

    private function validateCashPayment(Validator $validator, int|string $index, array $payment, float $amount): void
    {
        if (! isset($payment['cash_received']) || $payment['cash_received'] === '') {
            $validator->errors()->add(
                "payments.{$index}.cash_received",
                'Debes indicar el monto recibido en efectivo.',
            );

            return;
        }

        if ((float) $payment['cash_received'] < $amount) {
            $validator->errors()->add(
                "payments.{$index}.cash_received",
                'El monto recibido no puede ser menor al monto del pago.',
            );

            return;
        }

        if (isset($payment['cash_change']) && $payment['cash_change'] !== ''
            && abs(((float) $payment['cash_received'] - $amount) - (float) $payment['cash_change']) > self::PAYMENT_TOLERANCE) {
            $validator->errors()->add(
                "payments.{$index}.cash_change",
                'El cambio debe ser la diferencia entre el monto recibido y el monto del pago.',
            );
        }
    }

    private function validateCardPayment(Validator $validator, int|string $index, array $payment): void
    {
        if (empty($payment['card_last_four'])) {
            $validator->errors()->add(
                "payments.{$index}.card_last_four",
                'Debes indicar los últimos 4 dígitos de la tarjeta.',
            );
        }

        if (empty($payment['card_approval_code'])) {
            $validator->errors()->add(
                "payments.{$index}.card_approval_code",
                'Debes indicar el número de aprobación del terminal.',
            );
        }
    }

    private function validateTransferPayment(Validator $validator, int|string $index, array $payment): void
    {
        if (empty($payment['reference_number'])) {
            $validator->errors()->add(
                "payments.{$index}.reference_number",
                'Debes indicar el número de referencia de la transferencia.',
            );
        }

        if (empty($payment['bank_entity'])) {
            $validator->errors()->add(
                "payments.{$index}.bank_entity",
                'Debes indicar la entidad bancaria.',
            );
        }
    }

    /**
     * Verifica que la suma de los pagos coincida con el total de la factura.
     */
    private function validatePaymentTotal(Validator $validator): void
    {
        $items = $this->input('items', []);

        if (empty($items)) {
            return;
        }

        $expected = 0.0;

        foreach ($items as $item) {
            $lineSubtotal = (float) ($item['quantity'] ?? 0) * (float) ($item['unit_price'] ?? 0);
            $lineTax = $lineSubtotal * (float) ($item['tax_rate'] ?? 0) / 100;
            $lineDiscount = (float) ($item['discount'] ?? 0);

            $expected += $lineSubtotal + $lineTax - $lineDiscount;
        }

        $paid = 0.0;

        foreach ($this->input('payments', []) as $payment) {
            $paid += (float) ($payment['amount'] ?? 0);
        }

        if (abs($expected - $paid) > self::PAYMENT_TOLERANCE) {
            $validator->errors()->add(
                'payments',
                sprintf(
                    'La suma de los pagos (%s) debe coincidir con el total de la factura (%s).',
                    number_format($paid, 2),
                    number_format($expected, 2),
                ),
            );
        }
    }
}
