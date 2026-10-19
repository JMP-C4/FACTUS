<?php

declare(strict_types=1);

namespace App\Modules\Productos\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Productos\Http\Requests\StoreBrandRequest;
use App\Modules\Productos\Http\Requests\UpdateBrandRequest;
use App\Modules\Productos\Models\Brand;
use App\Modules\Productos\Services\BrandService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class BrandController extends Controller
{
    public function __construct(
        private readonly BrandService $brands,
    ) {}

    public function index(Request $request): View
    {
        $brands = $this->brands->list(
            $this->currentCompanyId($request),
            ['search' => $request->input('search')],
        );

        return view('productos::brands.index', compact('brands'));
    }

    public function create(): View
    {
        return view('productos::brands.create');
    }

    public function store(StoreBrandRequest $request): RedirectResponse
    {
        $companyId = $this->currentCompanyId($request);

        if ($companyId === null) {
            return back()->with('error', 'Debes pertenecer a una compañía para crear marcas.');
        }

        $this->brands->create($companyId, $request->validated());

        return redirect()
            ->route('productos.marcas.index')
            ->with('success', 'Marca creada exitosamente.');
    }

    public function edit(Request $request, Brand $brand): View
    {
        $this->authorizeBrand($brand, $request);

        return view('productos::brands.edit', compact('brand'));
    }

    public function update(UpdateBrandRequest $request, Brand $brand): RedirectResponse
    {
        $this->authorizeBrand($brand, $request);

        $this->brands->update($brand, $request->validated());

        return redirect()
            ->route('productos.marcas.index')
            ->with('success', 'Marca actualizada exitosamente.');
    }

    public function toggleActive(Request $request, Brand $brand): RedirectResponse
    {
        $this->authorizeBrand($brand, $request);

        $this->brands->toggleActive($brand);

        $status = $brand->fresh()->is_active ? 'activada' : 'desactivada';

        return redirect()
            ->route('productos.marcas.index')
            ->with('success', "Marca {$status} exitosamente.");
    }

    public function destroy(Request $request, Brand $brand): RedirectResponse
    {
        $this->authorizeBrand($brand, $request);

        $this->brands->delete($brand);

        return redirect()
            ->route('productos.marcas.index')
            ->with('success', 'Marca eliminada exitosamente.');
    }

    private function authorizeBrand(Brand $brand, Request $request): void
    {
        $companyId = $request->user()->company_id;

        abort_if(
            $companyId !== null && $brand->company_id !== $companyId,
            403,
            'No tienes acceso a esta marca.',
        );
    }
}
