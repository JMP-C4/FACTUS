<?php

declare(strict_types=1);

namespace App\Modules\Inventario\Services;

use App\Modules\Inventario\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class WarehouseService
{
    /**
     * Lista los almacenes de una compañía.
     */
    public function list(?int $companyId, array $filters = []): LengthAwarePaginator
    {
        return Warehouse::forCompany($companyId)
            ->when(! empty($filters['search']), fn ($q) => $q->where(function ($inner) use ($filters): void {
                $inner->where('name', 'like', '%'.$filters['search'].'%')
                    ->orWhere('code', 'like', '%'.$filters['search'].'%');
            }))
            ->withCount('stocks')
            ->orderByDesc('is_default')
            ->orderBy('name')
            ->paginate(15);
    }

    /**
     * Crea un almacén. Si se marca por defecto, desmarca el resto.
     */
    public function create(int $companyId, array $data): Warehouse
    {
        return DB::transaction(function () use ($companyId, $data): Warehouse {
            $isDefault = (bool) ($data['is_default'] ?? false);

            if ($isDefault) {
                Warehouse::where('company_id', $companyId)->update(['is_default' => false]);
            }

            return Warehouse::create([
                'company_id' => $companyId,
                'name' => $data['name'],
                'code' => $data['code'],
                'is_default' => $isDefault,
            ]);
        });
    }

    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        return DB::transaction(function () use ($warehouse, $data): Warehouse {
            $isDefault = (bool) ($data['is_default'] ?? false);

            if ($isDefault) {
                Warehouse::where('company_id', $warehouse->company_id)
                    ->whereKeyNot($warehouse->getKey())
                    ->update(['is_default' => false]);
            }

            $warehouse->update([
                'name' => $data['name'],
                'code' => $data['code'],
                'is_default' => $isDefault,
            ]);

            return $warehouse->fresh();
        });
    }

    /**
     * Marca un almacén como el predeterminado de la compañía.
     */
    public function setDefault(Warehouse $warehouse): Warehouse
    {
        return DB::transaction(function () use ($warehouse): Warehouse {
            Warehouse::where('company_id', $warehouse->company_id)
                ->update(['is_default' => false]);

            $warehouse->update(['is_default' => true]);

            return $warehouse->fresh();
        });
    }

    public function hasActivity(Warehouse $warehouse): bool
    {
        return $warehouse->stocks()->exists() || $warehouse->movements()->exists();
    }

    public function delete(Warehouse $warehouse): bool
    {
        return $warehouse->delete();
    }
}
