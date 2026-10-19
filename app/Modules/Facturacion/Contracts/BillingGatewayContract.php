<?php

declare(strict_types=1);

namespace App\Modules\Facturacion\Contracts;

use App\Modules\Facturacion\Models\Invoice;

interface BillingGatewayContract
{
    /**
     * Identificador del driver de facturación activo.
     */
    public function driver(): string;

    /**
     * Construye el payload de la factura para su emisión.
     */
    public function buildBillPayload(Invoice $invoice): array;

    /**
     * Emite/valida la factura y retorna la respuesta normalizada
     * con la estructura ['data' => ['bill' => [...]]].
     */
    public function issue(int $companyId, array $payload, ?int $invoiceId = null): array;

    /**
     * Anula una factura emitida usando su número de referencia.
     */
    public function cancel(int $companyId, string $referenceCode): array;

    /**
     * Construye el payload de una nota de crédito sobre una factura origen.
     */
    public function buildCreditNotePayload(Invoice $invoice, string $correctionConceptCode, ?string $reason = null): array;

    /**
     * Emite una nota de crédito local y retorna la respuesta normalizada.
     */
    public function issueCreditNote(int $companyId, array $payload, ?int $creditNoteId = null): array;
}
