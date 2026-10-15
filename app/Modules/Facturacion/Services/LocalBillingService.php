<?php

declare(strict_types=1);

namespace App\Modules\Facturacion\Services;

use App\Modules\Facturacion\Contracts\BillingGatewayContract;
use App\Modules\Facturacion\Models\BillingLog;
use App\Modules\Facturacion\Models\Invoice;
use Illuminate\Support\Str;

/**
 * Emisor de facturación 100% local.
 *
 * No realiza peticiones HTTP: genera numeración, CUFE y QR simulados para que
 * el flujo de emisión funcione sin depender de servicios externos.
 */
final class LocalBillingService implements BillingGatewayContract
{
    public function driver(): string
    {
        return 'local';
    }

    public function buildBillPayload(Invoice $invoice): array
    {
        $invoice->loadMissing(['items', 'customer', 'numberingRange', 'company']);

        return [
            'document' => '01',
            'reference_code' => $invoice->invoice_number,
            'prefix' => $invoice->numberingRange?->prefix ?? config('billing.prefix', 'FAC'),
            'issuer' => [
                'name' => $invoice->company?->name ?? config('billing.issuer_name', 'FACTUS'),
                'identification' => $invoice->company?->nit ?? null,
            ],
            'payment_form' => $invoice->payment_form_code ?? '1',
            'payment_method' => $invoice->payment_method_code ?? '10',
            'customer' => $this->buildCustomer($invoice->customer),
            'items' => $this->buildItems($invoice->items),
            'subtotal' => (float) $invoice->subtotal,
            'discount_total' => (float) $invoice->discount_total,
            'tax_total' => (float) $invoice->tax_total,
            'total' => (float) $invoice->total,
            'generated_at' => now()->toIso8601String(),
        ];
    }

    public function issue(int $companyId, array $payload, ?int $invoiceId = null): array
    {
        $reference = (string) ($payload['reference_code'] ?? $invoiceId);

        $cufe = hash('sha256', implode('|', [
            $companyId,
            $reference,
            (string) ($payload['total'] ?? '0'),
            now()->toIso8601String(),
            Str::random(8),
        ]));

        $bill = [
            'id' => $invoiceId,
            'number' => $reference,
            'reference_code' => $reference,
            'cufe' => $cufe,
            'qr' => rtrim((string) config('app.url'), '/').'/facturacion/verificar/'.$cufe,
            'status' => 1,
            'environment' => config('billing.environment', 'local'),
            'driver' => $this->driver(),
            'validated_at' => now()->toIso8601String(),
            'totals' => [
                'subtotal' => $payload['subtotal'] ?? null,
                'discount_total' => $payload['discount_total'] ?? null,
                'tax_total' => $payload['tax_total'] ?? null,
                'total' => $payload['total'] ?? null,
            ],
        ];

        $response = ['data' => ['bill' => $bill]];

        $this->log($companyId, $invoiceId, '/local/bills/validate', 'POST', $payload, $response, 201);

        return $response;
    }

    public function cancel(int $companyId, string $referenceCode): array
    {
        $response = [
            'data' => [
                'bill' => [
                    'number' => $referenceCode,
                    'status' => 2,
                    'driver' => $this->driver(),
                    'cancelled_at' => now()->toIso8601String(),
                ],
            ],
        ];

        $this->log(
            $companyId,
            null,
            '/local/bills/cancel',
            'POST',
            ['reference_code' => $referenceCode],
            $response,
            200,
        );

        return $response;
    }

    private function buildCustomer(?object $customer): ?array
    {
        if ($customer === null) {
            return null;
        }

        return [
            'identification' => $customer->document_number ?? null,
            'name' => $customer->display_name ?? trim(($customer->first_name ?? '').' '.($customer->last_name ?? '')),
            'company' => $customer->company_name ?? '',
            'address' => $customer->address ?? '',
            'email' => $customer->email ?? '',
            'phone' => $customer->phone ?? '',
        ];
    }

    private function buildItems($items): array
    {
        return $items->map(fn ($item) => [
            'code_reference' => $item->product_id ? (string) $item->product_id : $item->description,
            'name' => $item->description,
            'quantity' => (int) $item->quantity,
            'unit_price' => (float) $item->unit_price,
            'discount_total' => (float) $item->discount_total,
            'tax_total' => (float) $item->tax_total,
            'line_total' => (float) $item->line_total,
            'unit_measure_code' => $item->unit_measure_code ?? '94',
        ])->toArray();
    }

    private function log(
        int $companyId,
        ?int $loggableId,
        string $endpoint,
        string $method,
        array $payload,
        array $response,
        int $httpStatus,
    ): void {
        BillingLog::create([
            'company_id' => $companyId,
            'loggable_type' => $loggableId ? Invoice::class : null,
            'loggable_id' => $loggableId,
            'endpoint' => $endpoint,
            'http_method' => $method,
            'request_body' => $payload ?: null,
            'response_body' => $response,
            'http_status' => $httpStatus,
            'status' => $httpStatus < 400 ? 'success' : 'error',
            'processed_at' => now(),
        ]);
    }
}
