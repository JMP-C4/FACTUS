<?php

namespace App\Modules\Productos\Services;

use App\Modules\Productos\Contracts\BrandRepositoryContract;
use App\Modules\Productos\Models\Brand;
use Illuminate\Support\Collection;

class BrandRepository implements BrandRepositoryContract
{
    public function getActive(?int $companyId): Collection
    {
        return Brand::forCompany($companyId)
            ->active()
            ->orderBy('name')
            ->get();
    }

    public function find(int $id, int $companyId): ?Brand
    {
        return Brand::where('id', $id)
            ->forCompany($companyId)
            ->first();
    }
}
