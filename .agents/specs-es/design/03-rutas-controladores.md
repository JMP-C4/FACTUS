# Rutas y Controladores

## Principios

- Controladores delgados.
- Cada endpoint delega en un service/action.
- Las rutas se agrupan por módulo y prefijo funcional.
- Los controladores no construyen payloads de Factus manualmente.

## Rutas web sugeridas

```php
Route::middleware(['auth'])->group(function () {
    Route::prefix('access-control')->name('access-control.')->middleware('can:platform.manage')->group(function () {
        Route::get('/companias', CompanyController::class.'@index')->name('companias.index');
        Route::get('/usuarios', UserAdministrationController::class.'@index')->name('usuarios.index');
        Route::get('/roles-permisos', RolePermissionController::class.'@index')->name('roles.index');
        Route::get('/integraciones/factus/logs', FactusIntegrationLogController::class.'@index')->name('factus.logs.index');
    });

    Route::prefix('productos')->name('productos.')->group(function () {
        Route::get('/', ProductController::class.'@index')->name('index');
        Route::get('/crear', ProductController::class.'@create')->name('create');
        Route::post('/', ProductController::class.'@store')->name('store');
        Route::get('/{product}/editar', ProductController::class.'@edit')->name('edit');
        Route::put('/{product}', ProductController::class.'@update')->name('update');
    });

    Route::prefix('clientes')->name('clientes.')->group(function () {
        Route::resource('/', CustomerController::class);
    });

    Route::prefix('inventario')->name('inventario.')->group(function () {
        Route::get('/movimientos', StockMovementController::class.'@index')->name('movimientos.index');
        Route::post('/entradas', StockEntryController::class.'@store')->name('entradas.store');
        Route::post('/ajustes', StockAdjustmentController::class.'@store')->name('ajustes.store');
    });

    Route::prefix('facturacion')->name('facturacion.')->group(function () {
        Route::get('/pos', PosController::class.'@show')->name('pos');
        Route::post('/facturas', InvoiceController::class.'@store')->name('facturas.store');
        Route::get('/facturas/{invoice}', InvoiceController::class.'@show')->name('facturas.show');
        Route::post('/notas-credito', CreditNoteController::class.'@store')->name('notas.store');
        Route::post('/sincronizar-catalogos', FactusCatalogController::class.'@sync')->name('catalogos.sync');
    });

    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/ventas', SalesReportController::class.'@index')->name('ventas.index');
        Route::get('/inventario', InventoryReportController::class.'@index')->name('inventario.index');
    });
});
```

## Responsabilidades por controlador

- `CompanyController`, `UserAdministrationController`, `RolePermissionController`: gestión de plataforma restringida a `super_admin`.
- `ProductController`: CRUD y búsqueda administrativa.
- `CustomerController`: CRUD y alta rápida desde caja.
- `PosController`: render del puesto de venta y preparación de datos iniciales.
- `InvoiceController`: crear factura local y coordinar envío a servicio de facturación.
- `CreditNoteController`: emitir notas de crédito.
- `FactusCatalogController`: sincronizar maestros y rangos.
- `SalesReportController` y `InventoryReportController`: consultas de lectura paginadas/exportables.

## Convención de requests

- `StoreInvoiceRequest` valida estructura del carrito, cliente y pagos.
- `SyncFactusCatalogRequest` valida permisos y contexto de compañía.
- Los requests de plataforma deben validar explícitamente que el usuario tenga privilegios `super_admin` o permiso global equivalente.
- Cada request debe devolver mensajes claros y consistentes para UI SSR.

## Detalle de requests

### StoreInvoiceRequest

Valida la estructura completa de una factura incluyendo múltiples métodos de pago:

```php
// Ejemplo de estructura esperada para pagos múltiples
'payments' => [
    [
        'payment_method_code' => 'cash',
        'amount' => 50000,
        'cash_received' => 50000,
        'cash_change' => 0,
    ],
    [
        'payment_method_code' => 'debit_card',
        'amount' => 100000,
        'card_last_four' => '1234',
        'card_approval_code' => 'ABC123',
    ],
]
```

Validaciones:
- La suma de `payments.*.amount` debe igualar `total`.
- Para efectivo: `cash_received` es obligatorio, `cash_change = cash_received - amount`.
- Para tarjeta: `card_last_four` y `card_approval_code` son obligatorios.
- Para transferencia: `reference_number` y `bank_entity` son obligatorios.

### BarcodeSearchRequest

Request dedicado para búsqueda rápida por código de barras:

```php
Route::get('/productos/buscar-codigo', ProductController::class.'@findByBarcode')
    ->name('productos.findByBarcode');

// Parámetros:
// - barcode: string (requerido, formato EAN-13, EAN-8, UPC-A o código interno)
// - Incluye validación de formato según estándar detectado
```

Respuestas:
- 200: Producto encontrado con datos básicos para agregar al carrito.
- 404: Código de barras no encontrado en el catálogo.
- 422: Formato de código de barras inválido.
