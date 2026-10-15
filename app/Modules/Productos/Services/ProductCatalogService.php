<?php

namespace App\Modules\Productos\Services;

use App\Modules\Productos\Contracts\ProductCatalogContract;
use App\Modules\Productos\Models\Product;
use Illuminate\Support\Collection;

class ProductCatalogService implements ProductCatalogContract
{
    public function search(int $companyId, string $term): Collection
    {
        return Product::forCompany($companyId)
            ->active()
            ->search($term)
            ->with(['category', 'brand'])
            ->orderBy('name')
            ->get();
    }

    public function findByBarcode(int $companyId, string $barcode): ?object
    {
        $product = Product::forCompany($companyId)
            ->active()
            ->findByBarcode($barcode)
            ->with(['category', 'brand'])
            ->first();

        return $product ? (object) $product->only([
            'id',
            'name',
            'sku',
            'barcode',
            'price',
            'tax_rate',
            'unit_measure_code',
            'category',
            'brand',
        ]) : null;
    }

    public function getForInvoicing(int $productId, int $companyId): ?object
    {
        $product = Product::where('id', $productId)
            ->forCompany($companyId)
            ->active()
            ->with(['category', 'brand'])
            ->first();

        return $product ? (object) $product->only([
            'id',
            'name',
            'description',
            'sku',
            'barcode',
            'price',
            'tax_rate',
            'unit_measure_code',
            'standard_code',
            'category',
            'brand',
        ]) : null;
    }

    public function getActive(int $companyId): Collection
    {
        return Product::forCompany($companyId)
            ->active()
            ->with(['category', 'brand'])
            ->orderBy('name')
            ->get();
    }
}
