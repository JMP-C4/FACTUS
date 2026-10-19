<?php

namespace App\Modules\Organizacion\Services;

use App\Models\Branch;
use App\Models\Company;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BranchService
{
    /**
     * Listado paginado de sucursales de una compañía con filtros.
     *
     * @param  array<string, mixed>  $filters
     */
    public function list(int $companyId, array $filters = []): LengthAwarePaginator
    {
        $query = Branch::query()
            ->where('company_id', $companyId)
            ->with('company');

        if (! empty($filters['search'])) {
            $term = $filters['search'];
            $query->where(function ($q) use ($term): void {
                $q->where('name', 'like', "%{$term}%")
                    ->orWhere('code', 'like', "%{$term}%")
                    ->orWhere('city', 'like', "%{$term}%");
            });
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query->orderBy('name')->paginate(15);
    }

    /**
     * Crea una nueva sucursal dentro de una compañía.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(int $companyId, array $data): Branch
    {
        return DB::transaction(
            fn (): Branch => Branch::create(array_merge($data, ['company_id' => $companyId]))
        );
    }

    /**
     * Actualiza una sucursal existente.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Branch $branch, array $data): Branch
    {
        DB::transaction(function () use ($branch, $data): void {
            $branch->update($data);
        });

        return $branch->fresh();
    }

    /**
     * Cambia el estado de activación de una sucursal.
     */
    public function toggleActive(Branch $branch): Branch
    {
        $branch->update(['is_active' => ! $branch->is_active]);

        return $branch->fresh();
    }

    /**
     * Elimina (soft delete) una sucursal.
     */
    public function delete(Branch $branch): bool
    {
        return $branch->delete();
    }

    /**
     * Compañías disponibles para asignar una sucursal.
     */
    public function companies(): Collection
    {
        return Company::query()->orderBy('name')->get(['id', 'name']);
    }
}
