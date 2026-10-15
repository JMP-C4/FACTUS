<?php

namespace App\Modules\Facturacion\Providers;

use App\Modules\Facturacion\Contracts\BillingGatewayContract;
use App\Modules\Facturacion\Services\InvoiceService;
use App\Modules\Facturacion\Services\LocalBillingService;
use App\Modules\Support\Providers\BaseModuleServiceProvider;

class FacturacionServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'Facturacion';

    protected function bindings(): array
    {
        return [
            BillingGatewayContract::class => LocalBillingService::class,
        ];
    }

    protected function singletons(): array
    {
        return [
            InvoiceService::class => InvoiceService::class,
        ];
    }
}
