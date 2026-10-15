<?php

namespace App\Modules\Support\Providers;

use Illuminate\Support\ServiceProvider;
use ReflectionClass;

abstract class BaseModuleServiceProvider extends ServiceProvider
{
    protected string $moduleName = '';

    public function register(): void
    {
        // Registro centralizado de dependencias del módulo.
        // Cada módulo puede declarar sus bindings sin repetir la lógica de registro.
        $this->registerBindings();
        $this->registerSingletons();
    }

    // Boot: Carga rutas, vistas, migraciones del módulo
    public function boot(): void
    {
        $modulePath = $this->modulePath();
        $routesPath = $modulePath.'/Routes/web.php';
        $viewsPath = $modulePath.'/Views';
        $migrationsPath = $modulePath.'/Database/Migrations';

        // Carga rutas si existen
        if (file_exists($routesPath)) {
            $this->loadRoutesFrom($routesPath);
        }

        // Carga vistas si existen.
        // Se registran dos rutas para el namespace del módulo:
        // 1. app/Modules/{Modulo}/Views (vistas propias del módulo).
        // 2. resources/views/{modulo} (fallback de vistas de la aplicación).
        // El orden importa: la primera coincidencia gana.
        $viewPaths = [];

        if (is_dir($viewsPath)) {
            $viewPaths[] = $viewsPath;
        }

        $fallbackPath = resource_path('views/'.strtolower($this->moduleName));

        if (is_dir($fallbackPath)) {
            $viewPaths[] = $fallbackPath;
        }

        if ($viewPaths !== []) {
            $this->loadViewsFrom($viewPaths, strtolower($this->moduleName));
        }

        // Carga migraciones si existen
        if (is_dir($migrationsPath)) {
            $this->loadMigrationsFrom($migrationsPath);
        }
    }

    // Obtiene el path del módulo usando Reflection
    protected function modulePath(): string
    {
        return dirname((new ReflectionClass($this))->getFileName(), 2);
    }

    /**
     * Lista de bindings normales del módulo.
     *
     * Convención:
     * - La llave es el contrato o abstracción.
     * - El valor es la implementación concreta.
     *
     * Ejemplo futuro:
     * return [
     *     ProductCatalogContract::class => ProductCatalogService::class,
     * ];
     */
    protected function bindings(): array
    {
        return [];
    }

    /**
     * Lista de singletons del módulo.
     *
     * Convención:
     * - Se usa para servicios compartidos que deben vivir una sola vez
     *   dentro del ciclo de vida del contenedor.
     */
    protected function singletons(): array
    {
        return [];
    }

    /**
     * Recorre y registra los bindings definidos por el módulo.
     */
    protected function registerBindings(): void
    {
        foreach ($this->bindings() as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }

    /**
     * Recorre y registra los singletons definidos por el módulo.
     */
    protected function registerSingletons(): void
    {
        foreach ($this->singletons() as $abstract => $concrete) {
            $this->app->singleton($abstract, $concrete);
        }
    }
}
