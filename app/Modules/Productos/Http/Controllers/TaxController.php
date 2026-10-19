<?php

declare(strict_types=1);

namespace App\Modules\Productos\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Productos\Http\Requests\StoreTaxRequest;
use App\Modules\Productos\Http\Requests\UpdateTaxRequest;
use App\Modules\Productos\Models\Tax;
use App\Modules\Productos\Services\TaxService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class TaxController extends Controller
{
    public function __construct(
        private readonly TaxService $taxes,
    ) {}

    public function index(Request $request): View
    {
        $this->authorizeSuperAdmin($request);

        $taxes = $this->taxes->list(
            $this->currentCompanyId($request),
            ['search' => $request->input('search')],
        );

        return view('productos::taxes.index', compact('taxes'));
    }

    public function create(Request $request): View
    {
        $this->authorizeSuperAdmin($request);

        return view('productos::taxes.create');
    }

    public function store(StoreTaxRequest $request): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);

        $this->taxes->create($this->currentCompanyId($request), $request->validated());

        return redirect()
            ->route('productos.impuestos.index')
            ->with('success', 'Impuesto creado exitosamente.');
    }

    public function edit(Request $request, Tax $tax): View
    {
        $this->authorizeSuperAdmin($request);

        return view('productos::taxes.edit', compact('tax'));
    }

    public function update(UpdateTaxRequest $request, Tax $tax): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);

        $this->taxes->update($tax, $request->validated());

        return redirect()
            ->route('productos.impuestos.index')
            ->with('success', 'Impuesto actualizado exitosamente.');
    }

    public function toggleActive(Request $request, Tax $tax): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);

        $this->taxes->toggleActive($tax);

        $status = $tax->fresh()->is_active ? 'activado' : 'desactivado';

        return redirect()
            ->route('productos.impuestos.index')
            ->with('success', "Impuesto {$status} exitosamente.");
    }

    public function destroy(Request $request, Tax $tax): RedirectResponse
    {
        $this->authorizeSuperAdmin($request);

        $this->taxes->delete($tax);

        return redirect()
            ->route('productos.impuestos.index')
            ->with('success', 'Impuesto eliminado exitosamente.');
    }

    /**
     * Los impuestos son configuraciones críticas: sólo el super administrador
     * global puede visualizarlos y administrarlos.
     */
    private function authorizeSuperAdmin(Request $request): void
    {
        abort_unless(
            $request->user()?->isSuperAdmin() === true,
            403,
            'Sólo el super administrador puede administrar impuestos.',
        );
    }
}
