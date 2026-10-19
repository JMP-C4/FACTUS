<?php

declare(strict_types=1);

namespace App\Modules\Productos\Services;

use App\Modules\Productos\Models\Brand;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BrandService
{
    /**
     * Lista las marcas de una compañía.
     */
    public function list(?int $companyId, array $filters = []): LengthAwarePaginator
    {
        return Brand::forCompany($companyId)
            ->when(! empty($filters['search']), fn ($q) => $q->where('name', 'like', '%'.$filters['search'].'%'))
            ->withCount('products')
            ->orderBy('name')
            ->paginate(15);
    }

    /**
     * Crea una marca propia de la compañía.
     */
    public function create(int $companyId, array $data): Brand
    {
        return Brand::create(array_merge($data, [
            'company_id' => $companyId,
            'is_active' => $data['is_active'] ?? true,
        ]));
    }

    public function update(Brand $brand, array $data): Brand
    {
        $brand->update($data);

        return $brand->fresh();
    }

    public function toggleActive(Brand $brand): Brand
    {
        $brand->update(['is_active' => ! $brand->is_active]);

        return $brand->fresh();
    }

    public function delete(Brand $brand): bool
    {
        return $brand->delete();
    }
}
