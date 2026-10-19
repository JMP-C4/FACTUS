<?php

declare(strict_types=1);

namespace App\Modules\Facturacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Clientes\Contracts\CustomerDataProviderContract;
use App\Modules\Facturacion\Http\Requests\StoreInvoiceRequest;
use App\Modules\Facturacion\Models\Invoice;
use App\Modules\Facturacion\Services\InvoiceService;
use App\Modules\Productos\Contracts\ProductCatalogContract;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

final class InvoiceController extends Controller
{
    public function __construct(
        private readonly InvoiceService $invoiceService,
        private readonly CustomerDataProviderContract $customers,
        private readonly ProductCatalogContract $catalog,
    ) {}

    public function index(Request $request): View
    {
        $invoices = $this->invoiceService->list(
            $request->user()->company_id,
            ['status' => $request->input('status')],
        );

        return view('facturacion::invoices.index', compact('invoices'));
    }

    public function create(Request $request): View
    {
        $companyId = $request->user()->company_id;

        $customers = $this->customers->getFacturable($companyId);
        $products = $this->catalog->getActive($companyId);

        return view('facturacion::invoices.create', compact('customers', 'products'));
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $companyId = $this->currentCompanyId($request);

        if ($companyId === null) {
            return back()->with('error', 'Debes pertenecer a una compañía para crear facturas.');
        }

        $invoice = $this->invoiceService->create(
            $companyId,
            $request->user()->id,
            $request->validated(),
        );

        return redirect()
            ->route('facturacion.facturas.show', $invoice)
            ->with('success', 'Factura creada como borrador.');
    }

    public function show(Request $request, Invoice $invoice): View
    {
        $this->authorizeInvoice($invoice, $request);

        $invoice->load(['items', 'payments', 'customer', 'numberingRange', 'user', 'creditNotes.user']);

        return view('facturacion::invoices.show', compact('invoice'));
    }

    public function downloadPdf(Request $request, Invoice $invoice): Response
    {
        $this->authorizeInvoice($invoice, $request);

        $invoice->load(['items', 'payments', 'customer', 'numberingRange', 'user', 'company', 'branch']);

        $pdf = Pdf::loadView('facturacion::invoices.pdf', compact('invoice'));

        return $pdf->download("factura-{$invoice->invoice_number}.pdf");
    }

    public function issue(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeInvoice($invoice, $request);
        $this->invoiceService->issue($invoice);

        return redirect()
            ->route('facturacion.facturas.show', $invoice)
            ->with('success', 'Factura emitida exitosamente.');
    }

    public function cancel(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorizeInvoice($invoice, $request);
        $this->invoiceService->cancel($invoice);

        return redirect()
            ->route('facturacion.facturas.show', $invoice)
            ->with('success', 'Factura anulada exitosamente.');
    }

    /**
     * Verificación pública de una factura por CUFE (destino del código QR).
     */
    public function verify(string $cufe): View
    {
        $invoice = Invoice::where('cufe', $cufe)->firstOrFail();
        $invoice->load(['items', 'customer', 'company']);

        return view('facturacion::invoices.verify', compact('invoice'));
    }

    private function authorizeInvoice(Invoice $invoice, Request $request): void
    {
        $companyId = $request->user()->company_id;

        abort_if(
            $companyId !== null && $invoice->company_id !== $companyId,
            403,
            'No tienes acceso a esta factura.',
        );
    }
}
