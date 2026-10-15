<?php

namespace App\Modules\AccessControl\Providers;

use App\Modules\AccessControl\Console\Commands\CreateSuperAdminCommand;
use App\Modules\Support\Providers\BaseModuleServiceProvider;

class AccessControlServiceProvider extends BaseModuleServiceProvider
{
    /**
     * Nombre del modulo.
     * Se usa para registrar namespace de vistas y para mantener consistencia
     * con el cargador base de modulos.
     */
    protected string $moduleName = 'AccessControl';

    /**
     * Registra los servicios y comandos del módulo en el contenedor.
     *
     * Los comandos artisan definidos aquí estarán disponibles en la CLI
     * una vez que el módulo esté cargado.
     */
    public function register(): void
    {
        // Registrar comandos del módulo si se ejecuta en línea de comandos
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateSuperAdminCommand::class,
            ]);
        }
    }
}
