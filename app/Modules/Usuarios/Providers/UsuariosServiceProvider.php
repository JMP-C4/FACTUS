<?php

namespace App\Modules\Usuarios\Providers;

use App\Modules\Support\Providers\BaseModuleServiceProvider;
use App\Modules\Usuarios\Services\UserService;

class UsuariosServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'Usuarios';

    protected function singletons(): array
    {
        return [
            UserService::class => UserService::class,
        ];
    }
}
