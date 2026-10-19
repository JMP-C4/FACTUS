<?php

namespace App\Modules\Organizacion\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Modules\Auditoria\Contracts\AuditLoggerContract;
use App\Modules\Organizacion\Http\Requests\StoreBranchRequest;
use App\Modules\Organizacion\Http\Requests\UpdateBranchRequest;
use App\Modules\Organizacion\Services\BranchService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function __construct(
        private readonly BranchService $branchService,
        private readonly AuditLoggerContract $auditLogger,
    ) {}

    public function index(Request $request): View
    {
        $companyId = $this->resolveCompanyId($request);

        $branches = $this->branchService->list($companyId, [
            'search' => $request->input('search'),
            'is_active' => $request->input('is_active'),
        ]);

        return view('organizacion::branches.index', compact('branches'));
    }

    public function create(Request $request): View
    {
        $companies = $request->user()->company_id ? collect() : $this->branchService->companies();

        return view('organizacion::branches.create', compact('companies'));
    }

    public function store(StoreBranchRequest $request): RedirectResponse
    {
        $companyId = $request->user()->company_id ?? (int) $request->input('company_id');

        $branch = $this->branchService->create($companyId, $request->safe()->except('company_id'));

        $this->auditLogger->log('created', 'organizacion', "Sucursal creada: {$branch->name}", $branch);

        return redirect()
            ->route('sucursales.index')
            ->with('success', 'Sucursal creada exitosamente.');
    }

    public function show(Request $request, Branch $branch): View
    {
        $this->authorizeBranch($branch, $request);

        return view('organizacion::branches.show', compact('branch'));
    }

    public function edit(Request $request, Branch $branch): View
    {
        $this->authorizeBranch($branch, $request);

        $companies = collect();

        return view('organizacion::branches.edit', compact('branch', 'companies'));
    }

    public function update(UpdateBranchRequest $request, Branch $branch): RedirectResponse
    {
        $this->authorizeBranch($branch, $request);

        $this->branchService->update($branch, $request->validated());

        $this->auditLogger->log('updated', 'organizacion', "Sucursal actualizada: {$branch->name}", $branch);

        return redirect()
            ->route('sucursales.index')
            ->with('success', 'Sucursal actualizada exitosamente.');
    }

    public function toggleActive(Request $request, Branch $branch): RedirectResponse
    {
        $this->authorizeBranch($branch, $request);

        $this->branchService->toggleActive($branch);

        $status = $branch->fresh()->is_active ? 'activada' : 'desactivada';

        $this->auditLogger->log('updated', 'organizacion', "Sucursal {$status}: {$branch->name}", $branch);

        return redirect()
            ->route('sucursales.index')
            ->with('success', "Sucursal {$status} exitosamente.");
    }

    public function destroy(Request $request, Branch $branch): RedirectResponse
    {
        $this->authorizeBranch($branch, $request);

        $name = $branch->name;
        $this->branchService->delete($branch);

        $this->auditLogger->log('deleted', 'organizacion', "Sucursal eliminada: {$name}", $branch);

        return redirect()
            ->route('sucursales.index')
            ->with('success', 'Sucursal eliminada exitosamente.');
    }

    /**
     * Determina la compañía activa. Los usuarios con compañía usan la suya;
     * el super_admin debe seleccionar una.
     */
    private function resolveCompanyId(Request $request): int
    {
        if ($request->user()->company_id) {
            return (int) $request->user()->company_id;
        }

        return (int) ($request->input('company_id') ?? optional($this->branchService->companies()->first())->id);
    }

    private function authorizeBranch(Branch $branch, Request $request): void
    {
        $companyId = $request->user()->company_id;

        abort_if(
            $companyId !== null && $branch->company_id !== $companyId,
            403,
            'No tienes acceso a esta sucursal.'
        );
    }
}
