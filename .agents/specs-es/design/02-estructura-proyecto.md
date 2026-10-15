# Estructura del Proyecto

## Estructura base propuesta

```text
app/
  Modules/
    Productos/
      Contracts/
      Database/
        Migrations/
        Seeders/
      Domain/
      Http/
        Controllers/
        Requests/
        ViewModels/
      Models/
      Providers/
      Routes/
      Services/
      Views/
    Clientes/
    Inventario/
    Facturacion/
    Reportes/
bootstrap/
config/
database/
resources/
  css/
  js/
  views/
docs/
```

## Convenciones internas

- Cada módulo define un `ModuleServiceProvider` para bindings, rutas, vistas y migraciones.
- Las vistas Blade del módulo viven en `Views/` y se cargan con namespace, por ejemplo `productos::index`.
- Los contratos públicos se alojan en `Contracts/` y sus implementaciones concretas en `Services/`.
- Los requests HTTP no deben contener reglas que dependan de infraestructura externa.

## Estructura sugerida por módulo de Facturación

```text
app/Modules/Facturacion/
  Contracts/
    CustomerDataProvider.php
    ProductPricingProvider.php
    InventoryMovementGateway.php
  Database/
    Migrations/
  Domain/
    Enums/
    Exceptions/
    ValueObjects/
  Http/
    Controllers/
      InvoiceController.php
      CreditNoteController.php
    Requests/
      StoreInvoiceRequest.php
      StoreCreditNoteRequest.php
  Models/
    BillingLog.php
    Invoice.php
    InvoiceItem.php
    InvoicePayment.php
    NumberingRange.php
  Providers/
    FacturacionServiceProvider.php
  Routes/
    web.php
  Services/
    InvoiceService.php
    LocalBillingService.php
  Views/
    invoices/
    pos/
```

## Convención de migraciones

- `Database/Migrations` por módulo contendrá tablas propias y pivotes asociados.
- Los nombres de tablas usarán prefijos de dominio semánticos, no el nombre del módulo.
- Las llaves foráneas cruzadas deben minimizarse y, cuando existan, reflejar relaciones estables.
