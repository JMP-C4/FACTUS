<?php

use App\Modules\Clientes\Providers\ClientesServiceProvider;
use App\Modules\AccessControl\Providers\AccessControlServiceProvider;
use App\Modules\Facturacion\Providers\FacturacionServiceProvider;
use App\Modules\Inventario\Providers\InventarioServiceProvider;
use App\Modules\Productos\Providers\ProductosServiceProvider;
use App\Modules\Reportes\Providers\ReportesServiceProvider;
use App\Providers\AppServiceProvider;

return [
    AppServiceProvider::class,
    ProductosServiceProvider::class,
    ClientesServiceProvider::class,
    InventarioServiceProvider::class,
    FacturacionServiceProvider::class,
    ReportesServiceProvider::class,
    AccessControlServiceProvider::class,
];
