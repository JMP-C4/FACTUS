<?php

namespace App\Modules\AccessControl\Providers;

use App\Modules\Support\Providers\BaseModuleServiceProvider;

class AccessControlServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Nombre del modulo.
     * Se usa para registrar namespace de vistas y para mantener consistencia
     * con el cargador base de modulos.
     */
    protected string $moduleName = 'AccessControl';
}
