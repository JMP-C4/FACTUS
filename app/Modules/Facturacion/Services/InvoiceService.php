<?php

declare(strict_types=1);

namespace App\Modules\Facturacion\Services;

use App\Modules\Auditoria\Contracts\AuditLoggerContract;
use App\Modules\Facturacion\Contracts\BillingGatewayContract;
use App\Modules\Facturacion\Enums\InvoiceStatus;
use App\Modules\Facturacion\Models\Invoice;
use App\Modules\Facturacion\Models\InvoiceItem;
use App\Modules\Facturacion\Models\InvoicePayment;
use App\Modules\Facturacion\Models\NumberingRange;
use App\Modules\Inventario\Contracts\SaleStockContract;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

final class InvoiceService
{
    public function __construct(
        private readonly BillingGatewayContract $billing,
        private readonly SaleStockContract $saleStock,
        private readonly AuditLoggerContract $auditLogger,
    ) {}

    public function list(?int $companyId, array $filters = []): LengthAwarePaginator
    {
        $query = Invoice::forCompany($companyId)
            ->with(['customer', 'numberingRange']);

        if (! empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        return $query->latest()->paginate(15);
    }

    public function create(int $companyId, int $userId, array $data): Invoice
    {
        return DB::transaction(function () use ($companyId, $userId, $data): Invoice {
            $numberingRange = $this->resolveNumberingRange($companyId);
            $invoiceNumber = $this->generateInvoiceNumber($numberingRange);
            $totals = $this->calculateTotals($data['items']);

            $invoice = Invoice::create(array_merge($totals, [
                'company_id' => $companyId,
                'branch_id' => $data['branch_id'] ?? null,
                'customer_id' => $data['customer_id'] ?? null,
                'numbering_range_id' => $numberingRange->id,
                'user_id' => $userId,
                'status' => InvoiceStatus::Draft->value,
                'invoice_number' => $invoiceNumber,
                'payment_form_code' => $data['payment_form_code'] ?? '1',
                'payment_method_code' => $data['payment_method_code'] ?? config('billing.default_payment_method'),
            ]));

            $this->createItems($invoice, $data['items']);
            $this->createPayments($invoice, $data['payments']);
            $numberingRange->incrementNumber();

            return $invoice->load(['items', 'payments', 'customer', 'numberingRange']);
        });
    }

    public function issue(Invoice $invoice): Invoice
    {
        if (! $invoice->status->isEmitable()) {
            abort(422, 'Solo se pueden emitir facturas en estado borrador.');
        }

        $invoice->load(['items', 'customer', 'numberingRange', 'company']);

        $lines = $this->saleLines($invoice);

        $this->saleStock->assertAvailable($invoice->company_id, $lines);

        $payload = $this->billing->buildBillPayload($invoice);

        $invoice->update([
            'billing_payload' => $payload,
            'status' => InvoiceStatus::Pending->value,
        ]);

        try {
            $response = $this->billing->issue(
                $invoice->company_id,
                $payload,
                $invoice->id,
            );
        } catch (\Exception $e) {
            $invoice->update([
                'status' => InvoiceStatus::Error->value,
                'billing_response' => ['error' => $e->getMessage()],
            ]);

            $this->auditLogger->log(
                'invoice_issue_failed',
                'facturacion',
                "Error al emitir factura {$invoice->invoice_number}: {$e->getMessage()}",
                $invoice,
                ['status' => InvoiceStatus::Error->value],
            );

            throw $e;
        }

        $result = $this->handleIssueResponse($invoice, $response);

        if (in_array($result->status, [InvoiceStatus::Issued, InvoiceStatus::Validated], true)) {
            $this->saleStock->deductForSale(
                $invoice->company_id,
                $lines,
                $invoice::class,
                $invoice->id,
                $invoice->user_id,
            );
        }

        $this->auditLogger->log(
            'invoice_issued',
            'facturacion',
            "Factura {$result->invoice_number} emitida (estado {$result->status->value}).",
            $result,
            ['status' => $result->status->value, 'total' => $result->total],
        );

        return $result;
    }

    public function cancel(Invoice $invoice): Invoice
    {
        if (! $invoice->status->isCancellable()) {
            abort(422, 'No se puede cancelar esta factura. Use una nota de crédito.');
        }

        $this->billing->cancel($invoice->company_id, $invoice->invoice_number);

        $invoice->update(['status' => InvoiceStatus::Cancelled->value]);

        $this->auditLogger->log(
            'invoice_cancelled',
            'facturacion',
            "Factura {$invoice->invoice_number} cancelada.",
            $invoice,
        );

        return $invoice->fresh();
    }

    // =========================================================================
    // Private Helpers
    // =========================================================================

    /**
     * @return array<int, array{product_id: int|null, quantity: int}>
     */
    private function saleLines(Invoice $invoice): array
    {
        return $invoice->items
            ->map(fn (InvoiceItem $item): array => [
                'product_id' => $item->product_id,
                'quantity' => (int) $item->quantity,
            ])
            ->all();
    }

    private function resolveNumberingRange(int $companyId): NumberingRange
    {
        $numberingRange = NumberingRange::forCompany($companyId)
            ->active()
            ->first();

        if ($numberingRange === null || ! $numberingRange->isCurrentlyValid()) {
            abort(422, 'No hay rango de numeración activo para facturar.');
        }

        return $numberingRange;
    }

    private function generateInvoiceNumber(NumberingRange $numberingRange): string
    {
        $nextNumber = $numberingRange->getNextNumber();

        return "{$numberingRange->prefix}-{$nextNumber}";
    }

    private function calculateTotals(array $items): array
    {
        $subtotal = 0.0;
        $taxTotal = 0.0;
        $discountTotal = 0.0;

        foreach ($items as $item) {
            $lineSubtotal = $item['quantity'] * $item['unit_price'];
            $lineTax = $lineSubtotal * ($item['tax_rate'] ?? 0) / 100;
            $lineDiscount = $item['discount'] ?? 0;

            $subtotal += $lineSubtotal;
            $taxTotal += $lineTax;
            $discountTotal += $lineDiscount;
        }

        return [
            'subtotal' => $subtotal,
            'tax_total' => $taxTotal,
            'discount_total' => $discountTotal,
            'total' => $subtotal + $taxTotal - $discountTotal,
        ];
    }

    private function createItems(Invoice $invoice, array $items): void
    {
        foreach ($items as $item) {
            $lineSubtotal = $item['quantity'] * $item['unit_price'];
            $lineTax = $lineSubtotal * ($item['tax_rate'] ?? 0) / 100;

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $item['product_id'] ?? null,
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price' => $item['unit_price'],
                'discount_total' => $item['discount'] ?? 0,
                'tax_total' => $lineTax,
                'line_total' => $lineSubtotal + $lineTax - ($item['discount'] ?? 0),
                'tax_code' => $item['tax_code'] ?? null,
                'unit_measure_code' => $item['unit_measure_code'] ?? config('billing.default_unit_measure_code'),
                'product_snapshot' => $item['product_snapshot'] ?? null,
            ]);
        }
    }

    private function createPayments(Invoice $invoice, array $payments): void
    {
        foreach ($payments as $payment) {
            InvoicePayment::create(array_merge($payment, [
                'invoice_id' => $invoice->id,
            ]));
        }
    }

    private function handleIssueResponse(Invoice $invoice, array $response): Invoice
    {
        $billData = $response['data']['bill'] ?? null;

        if ($billData === null) {
            $invoice->update([
                'status' => InvoiceStatus::Error->value,
                'billing_response' => $response,
            ]);

            return $invoice->fresh();
        }

        $status = $billData['status'] == 1
            ? InvoiceStatus::Issued
            : InvoiceStatus::Validated;

        $invoice->update([
            'status' => $status->value,
            'status_dian' => $billData['status'] == 1 ? 'accepted' : 'pending',
            'external_id' => $billData['id'] ?? null,
            'cufe' => $billData['cufe'] ?? null,
            'qr_url' => $billData['qr'] ?? null,
            'invoice_number' => $billData['number'] ?? $invoice->invoice_number,
            'billing_response' => $response,
            'issued_at' => now(),
        ]);

        return $invoice->fresh();
    }
}
