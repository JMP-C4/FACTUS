<?php

namespace App\Modules\Productos\Services;

use App\Modules\Productos\Contracts\CategoryRepositoryContract;
use App\Modules\Productos\Models\Category;
use Illuminate\Support\Collection;

class CategoryRepository implements CategoryRepositoryContract
{
    public function getActive(int $companyId): Collection
    {
        return Category::forCompany($companyId)
            ->active()
            ->orderBy('name')
            ->get();
    }

    public function find(int $id, int $companyId): ?Category
    {
        return Category::where('id', $id)
            ->forCompany($companyId)
            ->first();
    }
}
