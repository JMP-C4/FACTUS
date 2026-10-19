<?php

namespace App\Modules\Organizacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Modules\Auditoria\Contracts\AuditLoggerContract;
use App\Modules\Organizacion\Http\Requests\StoreCompanyRequest;
use App\Modules\Organizacion\Http\Requests\UpdateCompanyRequest;
use App\Modules\Organizacion\Services\CompanyService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompanyController extends Controller
{
    public function __construct(
        private readonly CompanyService $companyService,
        private readonly AuditLoggerContract $auditLogger,
    ) {}

    public function index(Request $request): View
    {
        $companies = $this->companyService->list([
            'search' => $request->input('search'),
            'is_active' => $request->input('is_active'),
        ]);

        return view('organizacion::companies.index', compact('companies'));
    }

    public function create(): View
    {
        return view('organizacion::companies.create');
    }

    public function store(StoreCompanyRequest $request): RedirectResponse
    {
        $company = $this->companyService->create($request->validated());

        $this->auditLogger->log('created', 'organizacion', "Compañía creada: {$company->name}", $company);

        return redirect()
            ->route('empresas.index')
            ->with('success', 'Compañía creada exitosamente.');
    }

    public function show(Company $company): View
    {
        $company->loadCount(['branches', 'users']);
        $company->load('branches');

        return view('organizacion::companies.show', compact('company'));
    }

    public function edit(Company $company): View
    {
        return view('organizacion::companies.edit', compact('company'));
    }

    public function update(UpdateCompanyRequest $request, Company $company): RedirectResponse
    {
        $this->companyService->update($company, $request->validated());

        $this->auditLogger->log('updated', 'organizacion', "Compañía actualizada: {$company->name}", $company);

        return redirect()
            ->route('empresas.index')
            ->with('success', 'Compañía actualizada exitosamente.');
    }

    public function toggleActive(Company $company): RedirectResponse
    {
        $this->companyService->toggleActive($company);

        $status = $company->fresh()->is_active ? 'activada' : 'desactivada';

        $this->auditLogger->log('updated', 'organizacion', "Compañía {$status}: {$company->name}", $company);

        return redirect()
            ->route('empresas.index')
            ->with('success', "Compañía {$status} exitosamente.");
    }

    public function destroy(Company $company): RedirectResponse
    {
        $name = $company->name;
        $this->companyService->delete($company);

        $this->auditLogger->log('deleted', 'organizacion', "Compañía eliminada: {$name}", $company);

        return redirect()
            ->route('empresas.index')
            ->with('success', 'Compañía eliminada exitosamente.');
    }
}
