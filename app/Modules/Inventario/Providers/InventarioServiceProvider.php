<?php

namespace App\Modules\Inventario\Providers;

use App\Modules\Inventario\Contracts\InventoryMovementGatewayContract;
use App\Modules\Inventario\Contracts\SaleStockContract;
use App\Modules\Inventario\Services\InventoryMovementGateway;
use App\Modules\Inventario\Services\InventoryService;
use App\Modules\Inventario\Services\SaleStockService;
use App\Modules\Inventario\Services\WarehouseService;
use App\Modules\Support\Providers\BaseModuleServiceProvider;

class InventarioServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'Inventario';

    protected function bindings(): array
    {
        return [
            InventoryMovementGatewayContract::class => InventoryMovementGateway::class,
            SaleStockContract::class => SaleStockService::class,
        ];
    }

    protected function singletons(): array
    {
        return [
            InventoryService::class => InventoryService::class,
            WarehouseService::class => WarehouseService::class,
        ];
    }
}
