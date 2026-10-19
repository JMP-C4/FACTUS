<?php

namespace App\Modules\Productos\Services;

use App\Modules\Productos\Contracts\ProductCatalogContract;
use App\Modules\Productos\Contracts\TaxRepositoryContract;
use App\Modules\Productos\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function __construct(
        private readonly CategoryRepository $categoryRepository,
        private readonly BrandRepository $brandRepository,
        private readonly ProductCatalogContract $catalogContract,
        private readonly TaxRepositoryContract $taxRepository,
    ) {}

    /**
     * Lista productos paginados con filtros.
     */
    public function list(?int $companyId, array $filters = []): LengthAwarePaginator
    {
        $query = Product::forCompany($companyId)
            ->with(['category', 'brand']);

        if (! empty($filters['search'])) {
            $query->search($filters['search']);
        }

        if (isset($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('name')->paginate(15);
    }

    /**
     * Crea un nuevo producto.
     */
    public function create(int $companyId, array $data): Product
    {
        return DB::transaction(function () use ($companyId, $data): Product {
            $data = $this->resolveTaxRate($data, $companyId);

            return Product::create(array_merge($data, [
                'company_id' => $companyId,
            ]));
        });
    }

    /**
     * Actualiza un producto existente.
     */
    public function update(Product $product, array $data): Product
    {
        $data = $this->resolveTaxRate($data, $product->company_id ?? null);

        DB::transaction(function () use ($product, $data): void {
            $product->update($data);
        });

        return $product->fresh(['category', 'brand', 'tax']);
    }

    /**
     * Cambia el estado de activación de un producto.
     */
    public function toggleActive(Product $product): Product
    {
        $product->update(['is_active' => ! $product->is_active]);

        return $product->fresh();
    }

    /**
     * Elimina un producto (soft delete futuro).
     */
    public function delete(Product $product): bool
    {
        return $product->delete();
    }

    /**
     * Obtiene categorías activas para selects.
     */
    public function getCategories(?int $companyId): Collection
    {
        return $this->categoryRepository->getActive($companyId);
    }

    /**
     * Obtiene marcas activas para selects.
     */
    public function getBrands(?int $companyId): Collection
    {
        return $this->brandRepository->getActive($companyId);
    }

    /**
     * Obtiene impuestos activos para selects.
     */
    public function getTaxes(?int $companyId): Collection
    {
        return $this->taxRepository->getActive($companyId);
    }

    /**
     * Si el producto referencia un impuesto del catálogo, sincroniza el
     * snapshot numérico `tax_rate` a partir de la tarifa del impuesto.
     */
    private function resolveTaxRate(array $data, ?int $companyId): array
    {
        if (empty($data['tax_id'])) {
            return $data;
        }

        $tax = $this->taxRepository->find((int) $data['tax_id'], $companyId);

        if ($tax !== null) {
            $data['tax_rate'] = (float) $tax->rate;
        }

        return $data;
    }

    /**
     * Expone el contrato de catálogo para búsquedas externas.
     */
    public function getCatalogContract(): ProductCatalogContract
    {
        return $this->catalogContract;
    }
}
