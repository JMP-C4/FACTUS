<?php

declare(strict_types=1);

namespace App\Modules\DiagramaER\Providers;

use App\Modules\DiagramaER\Services\SchemaExtractorService;
use App\Modules\Support\Providers\BaseModuleServiceProvider;

class DiagramaERServiceProvider extends BaseModuleServiceProvider
{
    protected string $moduleName = 'DiagramaER';

    protected function singletons(): array
    {
        return [
            SchemaExtractorService::class => SchemaExtractorService::class,
        ];
    }

    /**
     * El namespace de vistas lo registra automáticamente el proveedor base a
     * partir del nombre del módulo (`diagramaer`). No se registra un segundo
     * namespace con guión para evitar duplicidad y confusión de convenciones.
     */
}
