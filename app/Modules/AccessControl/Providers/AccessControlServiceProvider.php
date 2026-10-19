<?php

namespace App\Modules\AccessControl\Providers;

use App\Modules\AccessControl\Console\Commands\CreateSuperAdminCommand;
use App\Modules\AccessControl\Services\RoleService;
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
     * Servicios compartidos del módulo.
     *
     * @return array<class-string, class-string>
     */
    protected function singletons(): array
    {
        return [
            RoleService::class => RoleService::class,
        ];
    }

    /**
     * Registra los servicios y comandos del módulo en el contenedor.
     *
     * Los comandos artisan definidos aquí estarán disponibles en la CLI
     * una vez que el módulo esté cargado.
     */
    public function register(): void
    {
        // Registra bindings/singletons declarados por el módulo.
        parent::register();

        // Registrar comandos del módulo si se ejecuta en línea de comandos
        if ($this->app->runningInConsole()) {
            $this->commands([
                CreateSuperAdminCommand::class,
            ]);
        }
    }
}
