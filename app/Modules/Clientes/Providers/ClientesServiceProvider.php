<?php

namespace App\Modules\Clientes\Providers;

use App\Modules\Clientes\Contracts\CustomerDataProviderContract;
use App\Modules\Clientes\Contracts\CustomerRepositoryContract;
use App\Modules\Clientes\Services\CustomerDataProvider;
use App\Modules\Clientes\Services\CustomerRepository;
use App\Modules\Clientes\Services\CustomerService;
use App\Modules\Support\Providers\BaseModuleServiceProvider;

class ClientesServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'Clientes';

    protected function bindings(): array
    {
        return [
            CustomerDataProviderContract::class => CustomerDataProvider::class,
            CustomerRepositoryContract::class => CustomerRepository::class,
        ];
    }

    protected function singletons(): array
    {
        return [
            CustomerService::class => CustomerService::class,
        ];
    }
}
