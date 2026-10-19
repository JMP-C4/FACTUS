<?php

namespace App\Modules\Automatizacion\Processes;

use App\Modules\Automatizacion\Automation\AutomationProcess;
use App\Modules\Automatizacion\Automation\AutomationResult;
use App\Modules\Facturacion\Enums\InvoiceStatus;
use App\Modules\Facturacion\Models\Invoice;
use App\Modules\Facturacion\Services\InvoiceService;

/**
 * Emite en lote todas las facturas en estado borrador de la compañía.
 */
class IssueDraftInvoicesProcess extends AutomationProcess
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
    ) {}

    public function key(): string
    {
        return 'facturacion.emitir_borradores';
    }

    public function label(): string
    {
        return 'Emitir facturas en borrador';
    }

    public function description(): string
    {
        return 'Emite todas las facturas en estado borrador de la compañía, una por una, registrando cada resultado.';
    }

    public function permission(): string
    {
        return 'facturacion.emitir';
    }

    public function icon(): string
    {
        return 'M7 3h7l5 5v13H7zM14 3v5h5M9 13h6M9 17h6';
    }

    public function handle(int $companyId, int $userId): AutomationResult
    {
        $drafts = Invoice::forCompany($companyId)
            ->byStatus(InvoiceStatus::Draft->value)
            ->get();

        $issued = 0;
        $failed = 0;

        foreach ($drafts as $invoice) {
            try {
                $this->invoiceService->issue($invoice);
                $issued++;
            } catch (\Throwable) {
                $failed++;
            }
        }

        $metrics = [
            'Procesadas' => $drafts->count(),
            'Emitidas' => $issued,
            'Con error' => $failed,
        ];

        if ($drafts->isEmpty()) {
            return AutomationResult::success('No había facturas en borrador para emitir.', $metrics);
        }

        return $failed === 0
            ? AutomationResult::success("Se emitieron {$issued} facturas en borrador.", $metrics)
            : AutomationResult::failure("Se emitieron {$issued} facturas; {$failed} fallaron.", $metrics);
    }
}
