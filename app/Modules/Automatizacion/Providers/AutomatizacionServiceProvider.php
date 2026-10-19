<?php

namespace App\Modules\Automatizacion\Providers;

use App\Modules\Automatizacion\Services\AutomationRegistry;
use App\Modules\Automatizacion\Services\AutomationRunner;
use App\Modules\Support\Providers\BaseModuleServiceProvider;

class AutomatizacionServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'Automatizacion';

    protected function singletons(): array
    {
        return [
            AutomationRegistry::class => AutomationRegistry::class,
            AutomationRunner::class => AutomationRunner::class,
        ];
    }
}
