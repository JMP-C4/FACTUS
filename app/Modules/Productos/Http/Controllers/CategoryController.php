<?php

declare(strict_types=1);

namespace App\Modules\Productos\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Productos\Http\Requests\StoreCategoryRequest;
use App\Modules\Productos\Http\Requests\UpdateCategoryRequest;
use App\Modules\Productos\Models\Category;
use App\Modules\Productos\Services\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categories,
    ) {}

    public function index(Request $request): View
    {
        $categories = $this->categories->list(
            $this->currentCompanyId($request),
            ['search' => $request->input('search')],
        );

        return view('productos::categories.index', compact('categories'));
    }

    public function create(): View
    {
        return view('productos::categories.create');
    }

    public function store(StoreCategoryRequest $request): RedirectResponse
    {
        $companyId = $this->currentCompanyId($request);

        if ($companyId === null) {
            return back()->with('error', 'Debes pertenecer a una compañía para crear categorías.');
        }

        $this->categories->create($companyId, $request->validated());

        return redirect()
            ->route('productos.categorias.index')
            ->with('success', 'Categoría creada exitosamente.');
    }

    public function edit(Request $request, Category $category): View
    {
        $this->authorizeCategory($category, $request);

        return view('productos::categories.edit', compact('category'));
    }

    public function update(UpdateCategoryRequest $request, Category $category): RedirectResponse
    {
        $this->authorizeCategory($category, $request);

        $this->categories->update($category, $request->validated());

        return redirect()
            ->route('productos.categorias.index')
            ->with('success', 'Categoría actualizada exitosamente.');
    }

    public function toggleActive(Request $request, Category $category): RedirectResponse
    {
        $this->authorizeCategory($category, $request);

        $this->categories->toggleActive($category);

        $status = $category->fresh()->is_active ? 'activada' : 'desactivada';

        return redirect()
            ->route('productos.categorias.index')
            ->with('success', "Categoría {$status} exitosamente.");
    }

    public function destroy(Request $request, Category $category): RedirectResponse
    {
        $this->authorizeCategory($category, $request);

        $this->categories->delete($category);

        return redirect()
            ->route('productos.categorias.index')
            ->with('success', 'Categoría eliminada exitosamente.');
    }

    private function authorizeCategory(Category $category, Request $request): void
    {
        $companyId = $request->user()->company_id;

        abort_if(
            $companyId !== null && $category->company_id !== $companyId,
            403,
            'No tienes acceso a esta categoría.',
        );
    }
}
