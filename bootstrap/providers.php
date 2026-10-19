<?php

use App\Modules\AccessControl\Providers\AccessControlServiceProvider;
use App\Modules\Auditoria\Providers\AuditoriaServiceProvider;
use App\Modules\Automatizacion\Providers\AutomatizacionServiceProvider;
use App\Modules\Clientes\Providers\ClientesServiceProvider;
use App\Modules\DiagramaER\Providers\DiagramaERServiceProvider;
use App\Modules\Facturacion\Providers\FacturacionServiceProvider;
use App\Modules\Inventario\Providers\InventarioServiceProvider;
use App\Modules\Organizacion\Providers\OrganizacionServiceProvider;
use App\Modules\Productos\Providers\ProductosServiceProvider;
use App\Modules\Reportes\Providers\ReportesServiceProvider;
use App\Modules\Usuarios\Providers\UsuariosServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    AuditoriaServiceProvider::class,
    ProductosServiceProvider::class,
    ClientesServiceProvider::class,
    InventarioServiceProvider::class,
    FacturacionServiceProvider::class,
    ReportesServiceProvider::class,
    OrganizacionServiceProvider::class,
    UsuariosServiceProvider::class,
    AccessControlServiceProvider::class,
    AutomatizacionServiceProvider::class,
    DiagramaERServiceProvider::class,
];
