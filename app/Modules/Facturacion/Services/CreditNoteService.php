<?php

declare(strict_types=1);

namespace App\Modules\Facturacion\Services;

use App\Modules\Auditoria\Contracts\AuditLoggerContract;
use App\Modules\Facturacion\Contracts\BillingGatewayContract;
use App\Modules\Facturacion\Enums\CreditNoteStatus;
use App\Modules\Facturacion\Enums\InvoiceStatus;
use App\Modules\Facturacion\Models\CreditNote;
use App\Modules\Facturacion\Models\Invoice;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpException;

final class CreditNoteService
{
    public function __construct(
        private readonly BillingGatewayContract $billing,
        private readonly AuditLoggerContract $auditLogger,
    ) {}

    /**
     * Lista notas de crédito paginadas con filtros.
     */
    public function list(?int $companyId, array $filters = []): LengthAwarePaginator
    {
        $query = CreditNote::forCompany($companyId)
            ->with(['invoice', 'user']);

        if (! empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        return $query->latest()->paginate(15);
    }

    /**
     * Emite una nota de crédito que corrige una factura ya emitida.
     *
     * @throws HttpException
     */
    public function issue(Invoice $invoice, int $userId, string $correctionConceptCode, ?string $reason = null): CreditNote
    {
        $this->assertInvoiceEligible($invoice);

        return DB::transaction(function () use ($invoice, $userId, $correctionConceptCode, $reason): CreditNote {
            $payload = $this->billing->buildCreditNotePayload($invoice, $correctionConceptCode, $reason);

            $creditNote = CreditNote::create([
                'company_id' => $invoice->company_id,
                'invoice_id' => $invoice->id,
                'user_id' => $userId,
                'status' => CreditNoteStatus::Pending->value,
                'correction_concept_code' => $correctionConceptCode,
                'reason' => $reason,
                'total' => $invoice->total,
                'billing_payload' => $payload,
            ]);

            try {
                $response = $this->billing->issueCreditNote(
                    $invoice->company_id,
                    $payload,
                    $creditNote->id,
                );
            } catch (\Exception $e) {
                $creditNote->update([
                    'status' => CreditNoteStatus::Error->value,
                    'billing_response' => ['error' => $e->getMessage()],
                ]);

                $this->auditLogger->log(
                    'credit_note_issue_failed',
                    'facturacion',
                    "Error al emitir nota de crédito para factura {$invoice->invoice_number}: {$e->getMessage()}",
                    $creditNote,
                );

                throw $e;
            }

            $result = $this->applyResponse($creditNote, $response);

            $this->auditLogger->log(
                'credit_note_issued',
                'facturacion',
                "Nota de crédito {$result->number} emitida para factura {$invoice->invoice_number}.",
                $result,
                ['status' => $result->status->value, 'total' => $result->total],
            );

            return $result;
        });
    }

    /**
     * Valida que la factura de origen sea corregible y no tenga ya una nota vigente.
     *
     * @throws HttpException
     */
    private function assertInvoiceEligible(Invoice $invoice): void
    {
        if (! in_array($invoice->status, [InvoiceStatus::Issued, InvoiceStatus::Validated], true)) {
            abort(422, 'Solo se pueden acreditar facturas emitidas o validadas.');
        }

        $hasActive = CreditNote::where('invoice_id', $invoice->id)
            ->applied()
            ->exists();

        if ($hasActive) {
            abort(422, 'Esta factura ya tiene una nota de crédito vigente.');
        }
    }

    private function applyResponse(CreditNote $creditNote, array $response): CreditNote
    {
        $bill = $response['data']['bill'] ?? null;

        if ($bill === null) {
            $creditNote->update([
                'status' => CreditNoteStatus::Error->value,
                'billing_response' => $response,
            ]);

            return $creditNote->fresh();
        }

        $creditNote->update([
            'status' => CreditNoteStatus::Issued->value,
            'status_dian' => $bill['status'] == 1 ? 'accepted' : 'pending',
            'number' => $bill['credit_note_number'] ?? $creditNote->number,
            'external_id' => $bill['reference_code'] ?? null,
            'cufe' => $bill['cufe'] ?? null,
            'qr_url' => $bill['qr'] ?? null,
            'billing_response' => $response,
            'issued_at' => now(),
        ]);

        return $creditNote->fresh(['invoice', 'user']);
    }
}
