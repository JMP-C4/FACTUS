<?php

namespace App\Modules\Support\Providers;

use Illuminate\Support\ServiceProvider;
use ReflectionClass;

abstract class BaseModuleServiceProvider extends ServiceProvider
{
    protected string $moduleName = '';

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        $modulePath = $this->modulePath();
        $routesPath = $modulePath.'/Routes/web.php';
        $viewsPath = $modulePath.'/Views';
        $migrationsPath = $modulePath.'/Database/Migrations';

        if (file_exists($routesPath)) {
            $this->loadRoutesFrom($routesPath);
        }

        if (is_dir($viewsPath)) {
            $this->loadViewsFrom($viewsPath, strtolower($this->moduleName));
        }

        if (is_dir($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }
    }

    protected function modulePath(): string
    {
        return dirname((new ReflectionClass($this))->getFileName(), 2);
    }
}
