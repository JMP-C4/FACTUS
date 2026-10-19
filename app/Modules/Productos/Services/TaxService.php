<?php

declare(strict_types=1);

namespace App\Modules\Productos\Services;

use App\Modules\Productos\Models\Tax;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class TaxService
{
    /**
     * Lista los impuestos visibles para una compañía.
     */
    public function list(?int $companyId, array $filters = []): LengthAwarePaginator
    {
        return Tax::visibleForCompany($companyId)
            ->when(! empty($filters['search']), fn ($q) => $q->where(function ($inner) use ($filters): void {
                $inner->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('code', 'like', '%'.$filters['search'].'%');
            }))
            ->orderBy('rate')
            ->orderBy('name')
            ->paginate(15);
    }

    public function getActive(?int $companyId): Collection
    {
        return Tax::visibleForCompany($companyId)
            ->where('is_active', true)
            ->orderBy('rate')
            ->orderBy('name')
            ->get();
    }

    /**
     * Crea un impuesto propio de la compañía.
     */
    public function create(?int $companyId, array $data): Tax
    {
        return Tax::create(array_merge($data, [
            'company_id' => $companyId,
            'is_active' => $data['is_active'] ?? true,
        ]));
    }

    public function update(Tax $tax, array $data): Tax
    {
        $tax->update($data);

        return $tax->fresh();
    }

    public function toggleActive(Tax $tax): Tax
    {
        $tax->update(['is_active' => ! $tax->is_active]);

        return $tax->fresh();
    }

    public function delete(Tax $tax): bool
    {
        return $tax->delete();
    }
}
