<?php

namespace App\Modules\Productos\Providers;

use App\Modules\Productos\Contracts\BrandRepositoryContract;
use App\Modules\Productos\Contracts\CategoryRepositoryContract;
use App\Modules\Productos\Contracts\ProductCatalogContract;
use App\Modules\Productos\Contracts\TaxRepositoryContract;
use App\Modules\Productos\Services\BrandRepository;
use App\Modules\Productos\Services\CategoryRepository;
use App\Modules\Productos\Services\ProductCatalogService;
use App\Modules\Productos\Services\ProductService;
use App\Modules\Productos\Services\TaxRepository;
use App\Modules\Support\Providers\BaseModuleServiceProvider;

class ProductosServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'Productos';

    protected function bindings(): array
    {
        return [
            ProductCatalogContract::class => ProductCatalogService::class,
            CategoryRepositoryContract::class => CategoryRepository::class,
            BrandRepositoryContract::class => BrandRepository::class,
            TaxRepositoryContract::class => TaxRepository::class,
        ];
    }

    protected function singletons(): array
    {
        return [
            ProductService::class => ProductService::class,
        ];
    }
}
