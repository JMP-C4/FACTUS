<?php

namespace App\Modules\Organizacion\Services;

use App\Models\Company;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CompanyService
{
    /**
     * Listado paginado de compañías con filtros.
     *
     * @param  array<string, mixed>  $filters
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Company::query()->withCount(['branches', 'users']);

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('nit', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%");
            });
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query->orderBy('name')->paginate(15);
    }

    /**
     * Crea una nueva compañía.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Company
    {
        return DB::transaction(fn (): Company => Company::create($data));
    }

    /**
     * Actualiza una compañía existente.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Company $company, array $data): Company
    {
        DB::transaction(function () use ($company, $data): void {
            $company->update($data);
        });

        return $company->fresh();
    }

    /**
     * Cambia el estado de activación de una compañía.
     */
    public function toggleActive(Company $company): Company
    {
        $company->update(['is_active' => ! $company->is_active]);

        return $company->fresh();
    }

    /**
     * Elimina (soft delete) una compañía.
     */
    public function delete(Company $company): bool
    {
        return $company->delete();
    }
}
