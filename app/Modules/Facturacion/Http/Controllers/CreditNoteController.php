<?php

declare(strict_types=1);

namespace App\Modules\Facturacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Facturacion\Http\Requests\StoreCreditNoteRequest;
use App\Modules\Facturacion\Models\Invoice;
use App\Modules\Facturacion\Services\CreditNoteService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CreditNoteController extends Controller
{
    public function __construct(
        private readonly CreditNoteService $creditNotes,
    ) {}

    public function index(Request $request): View
    {
        $creditNotes = $this->creditNotes->list(
            $this->currentCompanyId($request),
            ['status' => $request->input('status')],
        );

        return view('facturacion::credit-notes.index', compact('creditNotes'));
    }

    public function store(StoreCreditNoteRequest $request, Invoice $invoice): RedirectResponse
    {
        $companyId = $this->currentCompanyId($request);

        if ($companyId === null) {
            return back()->with('error', 'Debes pertenecer a una compañía para emitir notas de crédito.');
        }

        abort_if($invoice->company_id !== $companyId, 403, 'No tienes acceso a esta factura.');

        $this->creditNotes->issue(
            $invoice,
            $request->user()->id,
            $request->validated('correction_concept_code'),
            $request->validated('reason'),
        );

        return redirect()
            ->route('facturacion.facturas.show', $invoice)
            ->with('success', 'Nota de crédito emitida exitosamente.');
    }
}
