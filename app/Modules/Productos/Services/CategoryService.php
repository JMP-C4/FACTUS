<?php

declare(strict_types=1);

namespace App\Modules\Productos\Services;

use App\Modules\Productos\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CategoryService
{
    /**
     * Lista las categorías de una compañía.
     */
    public function list(?int $companyId, array $filters = []): LengthAwarePaginator
    {
        return Category::forCompany($companyId)
            ->when(! empty($filters['search']), fn ($q) => $q->where('name', 'like', '%'.$filters['search'].'%'))
            ->withCount('products')
            ->orderBy('name')
            ->paginate(15);
    }

    /**
     * Crea una categoría propia de la compañía.
     */
    public function create(int $companyId, array $data): Category
    {
        return Category::create(array_merge($data, [
            'company_id' => $companyId,
            'is_active' => $data['is_active'] ?? true,
        ]));
    }

    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category->fresh();
    }

    public function toggleActive(Category $category): Category
    {
        $category->update(['is_active' => ! $category->is_active]);

        return $category->fresh();
    }

    public function delete(Category $category): bool
    {
        return $category->delete();
    }
}
