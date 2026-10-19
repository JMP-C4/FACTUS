<?php

namespace App\Modules\Organizacion\Providers;

use App\Modules\Organizacion\Services\BranchService;
use App\Modules\Organizacion\Services\CompanyService;
use App\Modules\Support\Providers\BaseModuleServiceProvider;

class OrganizacionServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'Organizacion';

    protected function singletons(): array
    {
        return [
            CompanyService::class => CompanyService::class,
            BranchService::class => BranchService::class,
        ];
    }
}
