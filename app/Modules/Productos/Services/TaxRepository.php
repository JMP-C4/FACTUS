<?php

declare(strict_types=1);

namespace App\Modules\Productos\Services;

use App\Modules\Productos\Contracts\TaxRepositoryContract;
use App\Modules\Productos\Models\Tax;
use Illuminate\Support\Collection;

class TaxRepository implements TaxRepositoryContract
{
    public function getActive(?int $companyId): Collection
    {
        return Tax::visibleForCompany($companyId)
            ->where('is_active', true)
            ->orderBy('rate')
            ->orderBy('name')
            ->get();
    }

    public function find(int $id, ?int $companyId): ?Tax
    {
        return Tax::where('id', $id)
            ->visibleForCompany($companyId)
            ->first();
    }
}
