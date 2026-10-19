# Pendientes y hallazgos críticos

Auditoría del estado real del sistema frente a los specs en `.agents/specs-es/`.
Verificado con `php artisan test` → **204 pruebas / 512 aserciones en verde**.

Los módulos de negocio (productos, clientes, inventario, facturación local, POS
básico, reportes, RBAC, organización, usuarios, auditoría y automatización)
están implementados y con rutas, vistas y pruebas. Sin embargo, existen
**desconexiones de negocio** que dejan requisitos explícitos sin cumplir.

> **Actualización:** se resolvieron los puntos **1.1 (descuento de stock en
> venta)**, **1.2 (notas de crédito)**, **1.3 (multi-pago + validación)** y
> **1.4 (Error 500 por `company_id` nulo en super_admin global)**; se integraron
> diálogos de confirmación (feedback) en las acciones de estado, se invirtió el
> botón de tema, se añadió el botón de **Automatización** al dashboard
> restringido por permiso, se implementaron los **catálogos maestros**
> (impuestos/IVA, categorías, marcas y almacenes) con CRUD completo y se añadió
> la **exportación CSV y filtros de reportes** (cajero/método de pago).
> **Continuación (3.1–3.3):** se reemplazó el **dashboard demo por datos reales**
> (`DashboardService` + `DashboardController`, métricas por alcance y bitácora),
> se instrumentó la **auditoría de login/logout/accesos fallidos y de
> facturación/notas de crédito** (`AuthEventListener` + `AuditLogger`), y se
> añadió **baja rotación + última fecha de movimiento** al reporte de inventario.
> Ver detalle en cada sección. Las secciones se conservan como registro.

---

## 1. Problemas críticos a resolver

### 1.1 El stock NO se descuenta al vender 🔴 — ✅ RESUELTO

- `InventoryMovementGatewayContract` existe y está bindeado
  (`app/Modules/Inventario/Services/InventoryMovementGateway.php:30` `exit()`),
  pero **ningún código de Facturación lo consume**.
- `InvoiceService` / `PosController` no llaman a Inventario en ningún punto.
- Incumple el criterio global: *"El stock se descuenta solo después de una
  transacción de venta válida."*
- Incumple HU-FAC-01 (validar y descontar stock al emitir).
- **Acción:** inyectar el gateway/`InventoryService` en `InvoiceService::issue()`
  (o en un `SaleService`) y descontar dentro de la transacción de emisión; añadir
  método de salida por venta en `InventoryService` y registrar
  `StockMovement` con `reference_type = Invoice`.
- **Implementado:** contrato cross-módulo `SaleStockContract` +
  `SaleStockService` (`app/Modules/Inventario/`), bindeado en
  `InventarioServiceProvider`. `InvoiceService::issue()` valida disponibilidad
  (`assertAvailable`) antes de emitir y descuenta (`deductForSale`) tras una
  emisión exitosa, con `reference_type = Invoice::class`. `exit()` del gateway
  ahora acepta `referenceType`/`referenceId`. `PosController::checkout()` se
  envolvió en transacción para revertir el borrador si falla la emisión.
  Política: sólo se descuentan productos con registro de stock (rastreados); si
  la compañía no tiene almacén, no se bloquea la venta. Pruebas añadidas
  (descuento y stock insuficiente con rollback).

### 1.2 Notas de crédito: no existen 🟠 — ✅ RESUELTO

- Permiso `facturacion.nota_credito` **sembrado y asignado** (admin/supervisor),
  e `InvoiceService::cancel()` sugiere *"Use una nota de crédito"*
  (`InvoiceService.php:96`).
- Previsto en `design/04-modelos-datos.md:75` (`CreditNote`) y
  `design/03-rutas-controladores.md:43` (`CreditNoteController`).
- Incumple HU-FAC-02 (corregir ventas facturadas con trazabilidad).
- **Implementado:** tabla/modelo `CreditNote` + enum `CreditNoteStatus`,
  relación `Invoice::creditNotes()`, `CreditNoteService` (lista + emisión con
  validación de elegibilidad: solo facturas `issued`/`validated` y sin nota
  aplicada previa), `StoreCreditNoteRequest` (concepto 1..5 + motivo),
  `CreditNoteController`, rutas `GET /facturacion/notas-credito` y
  `POST /facturacion/facturas/{invoice}/notas-credito`, vistas de índice y
  sección de emisión en el detalle de factura. `BillingGatewayContract` +
  `LocalBillingService` ampliados (`buildCreditNotePayload`/`issueCreditNote`,
  log en `billing_logs` con `loggable_type = CreditNote`). `ReportService::salesSummary()`
  ahora descuenta las notas aplicadas (Venta Bruta / Notas de Crédito / Venta Neta).
  Pruebas: `tests/Feature/Facturacion/CreditNoteTest.php` (7 casos).

### 1.3 Multi-pago incompleto 🟠 — ✅ RESUELTO

- La tabla/modelo `InvoicePayment` soportan `card_last_four`,
  `card_approval_code`, `bank_entity`, `cash_received`, `cash_change`.
- Pero `StoreInvoiceRequest` **no valida** esos campos ni la regla
  *"la suma de pagos debe igualar el total"* (`design/03-rutas-controladores.md:96`).
- El POS sólo envía **un pago en efectivo hardcodeado**
  (`resources/views/facturacion/pos.blade.php:358`); no hay UI multi-método.
- Incumple HU-FAC-01 (medios de pago) y `requirements/07` (pagos combinados).
- **Acción:** validar suma == total, exigir campos condicionales por método y
  construir la UI de pagos combinados (efectivo/tarjeta/transferencia).
- **Implementado:** `StoreInvoiceRequest` valida `in:cash,debit_card,credit_card,transfer`,
  monto `gt:0`, campos condicionales por método (`cash_received`/`cash_change`,
  `card_last_four`/`card_approval_code`, `reference_number`/`bank_entity`) y, vía
  `withValidator`, que **la suma de pagos coincida con el total** (tolerancia
  0.01). El POS ahora tiene un panel de **pago dividido** (efectivo/tarjeta/
  transferencia) con cambio calculado y validación client-side; el formulario
   manual envía `cash_received`/`cash_change`. Pruebas añadidas para pago
   combinado, suma incorrecta y campos de tarjeta.

### 1.4 Error 500 con `company_id` nulo (super_admin global) 🔴 — ✅ RESUELTO

- Un usuario `super_admin` **sin compañía** (`company_id = null`) hacía fallar
  con `TypeError` los servicios que tipaban `int $companyId`: inventario
  (`listMovements`), reportes (`salesSummary`), facturación (`list`) y productos
  (`list`), además de los árboles de vista asociados.
- **Solución:** se alineó con el patrón ya existente del módulo Usuarios
  (`isGloballyScoped()` → scope `null` = sin filtro de compañía). Los scopes
  `forCompany()` de los 9 modelos y las lecturas de servicios/contratos
  (`ProductService`, `CategoryRepository`, `BrandRepository`, `CustomerService`,
  `CustomerDataRepository`, `ProductCatalog`, `InventoryService`, `ReportService`,
  `InvoiceService`) ahora aceptan `?int $companyId` y **omiten el filtro cuando
  es nulo** (el super_admin global ve todas las compañías). Se permitió además
  que el super_admin global vea/edite productos y clientes de cualquier compañía.
- **Escrituras:** al no estar asociado a una compañía, el super_admin global
  recibe un error claro (no un 500) al intentar crear productos/clientes/
  facturas/stock, redirigiendo con mensaje.
- Bonus: se corrigió que el listado de **productos** y **clientes** filtraba por
  `is_active`/`is_facturable` = `false` por defecto (ocultaba registros activos);
  ahora sólo filtra si el parámetro viene en la petición.
- Pruebas: `tests/Feature/Ui/GlobalScopeAccessTest.php` (12 rutas + visibilidad
  multiempresa + guarda de escritura).

---

## 2. Desconexiones críticas (capacidades sembradas sin implementar)

Permisos en `PermissionSeeder` que **no tienen ruta, controlador ni vista**
(los catálogos de productos y almacenes ya están resueltos):

| Permiso | Módulo | Estado |
| --- | --- | --- |
| `categorias.manage` | productos | ✅ Resuelto: `CategoryService`/`CategoryController`, CRUD completo con slug automático |
| `marcas.manage` | productos | ✅ Resuelto: modelo `Brand`, CRUD completo (`BrandController`) |
| `impuestos.manage` | productos | ✅ Resuelto: modelo `Tax`, CRUD completo y tarifas de IVA sembradas (19/5/0/excluido) |
| `almacenes.manage` | inventario | ✅ Resuelto: CRUD `WarehouseController`, borrado protegido y marcado de predeterminado |
| `reportes.financiero` | reportes | Sin ruta |
| `reportes.exportar` | reportes | ✅ Resuelto: export CSV de ventas e inventario (`reportes.*.export`) |
| `facturacion.resincronizar` | facturacion | Sin uso (resabio de Factus) |
| `pos.devoluciones` | pos | Sin ruta ni UI |
| `pos.cierre_caja` | pos | Sin ruta ni UI |

**Impuestos (IVA):** se añadió la entidad `Tax` (`app/Modules/Productos/Models/Tax.php`)
con catálogo global sembrado por `TaxSeeder` (`IVA19`, `IVA5`, `IVA0` exento y
`EXCLUIDO`), CRUD gated por `impuestos.manage` (`TaxController`, rutas
`productos.impuestos.*`, vistas) y un **selector de impuesto** en el formulario
de producto que sincroniza el snapshot numérico `products.tax_rate`. Los
impuestos globales (`company_id` nulo) son de solo lectura para las compañías.
Pruebas: `tests/Feature/Productos/TaxTest.php`.

**Categorías, marcas y almacenes:** se implementó el CRUD completo. Categorías
(`CategoryService` + `CategoryController`, rutas `productos.categorias.*`, slug
automático) y marcas (`BrandService` + `BrandController`, rutas
`productos.marcas.*`), ambas gated por su permiso; almacenes
(`WarehouseService` + `WarehouseController`, rutas `inventario.almacenes.*`) con
marcado de predeterminado exclusivo y **borrado bloqueado si el almacén tiene
stock o movimientos**. Enlaces añadidos al sidebar y a la cabecera de Productos.
Pruebas: `tests/Feature/Productos/CategoryTest.php`,
`tests/Feature/Productos/BrandTest.php` y
`tests/Feature/Inventario/WarehouseTest.php`.

---

## 3. Hallazgos clave a corregir

### 3.1 Dashboard con datos demo — ✅ RESUELTO
`resources/views/dashboard.blade.php` mostraba datos hardcodeados. Ahora el
dashboard consume datos reales desde `DashboardService`
(`app/Services/DashboardService.php`) vía `DashboardController`
(`app/Http/Controllers/DashboardController.php`, ruta `/dashboard`):
resumen distinto para alcance **global** (compañías, usuarios, ventas del mes) y
de **compañía** (productos activos, ventas de hoy, facturas del mes); grid de
módulos con métricas reales y enlaces filtrados por permiso (incluye
**Automatización**); y tabla de **actividad reciente** desde `ActivityLog`
(filtrada por compañía). Se mantuvo el enlace de Automatización por rol. Pruebas:
`tests/Feature/Ui/DashboardTest.php`.

### 3.2 Auditoría incompleta — ✅ RESUELTO
Se añadió `AuthEventListener` (`app/Modules/Auditoria/Listeners/`) registrado en
`AuditoriaServiceProvider::boot()` para **login, logout y acceso fallido**
(eventos `Login`/`Logout`/`Failed`), que escribe en la bitácora vía
`AuditLoggerContract` (se añadió `user_id` explícito opcional al contrato para
soportar el logout). El flujo de **facturación ahora instrumenta** la bitácora:
`InvoiceService::issue()` registra `invoice_issued`/`invoice_issue_failed`,
`InvoiceService::cancel()` registra `invoice_cancelled`, y `CreditNoteService`
registra `credit_note_issued`/`credit_note_issue_failed`. Pruebas:
`tests/Feature/Auditoria/AuditLogTest.php`.

### 3.3 Reportes parciales (HU-REP)
- `ReportService::salesSummary()` ahora filtra por fecha, **cajero** (`user_id`)
  y **método de pago** (`invoice_payments`). Ya descuenta las notas de crédito
  aplicadas (Venta Bruta / Notas de Crédito / Venta Neta).
- Inventario: filtro por categoría aún pendiente. Ya cuenta con **baja rotación**
  (sin movimiento en `LOW_ROTATION_DAYS = 30`) y **última fecha de movimiento**
  por almacén/producto (`ReportService::inventoryReport()`, columna en la vista y
  en el CSV). Se añadieron las métricas "Baja Rotación" y el estado `low_rotation`.
- **Exportación CSV** implementada para ventas e inventario, gated por
  `reportes.exportar` (rutas `reportes.ventas.export` y `reportes.inventario.export`).

### 3.4 Código sin uso
Detectado en la auditoría previa (`tasks.md:161`): `InventoryService::listStocks()`,
`Product::hasStock()`, `Product::getPriceWithTax()`, `Stock::isBelowMinimum()`,
`Warehouse::scopeDefault()`, `Invoice::isIssued()`, `Invoice::billingLogs()`,
`NumberingRange::isWithinRange()`, `UserRole::assignedBy()`.

### 3.5 Cobertura de pruebas
Se añadieron pruebas de **baja rotación y última fecha de movimiento** en
inventario (`tests/Feature/Reportes/ReportTest.php`), **auditoría**
(`tests/Feature/Auditoria/AuditLogTest.php`) y **dashboard real**
(`tests/Feature/Ui/DashboardTest.php`). Ya hay pruebas de **descuento de stock en
venta**, **multi-pago**, **acceso con scope global (`company_id` nulo)**,
**visibilidad del botón de automatización por rol**, **notas de crédito**,
**catálogo de impuestos**, **CRUD de categorías, marcas y almacenes**, **filtros
  y exportación de reportes**. Las **204 pruebas / 512 aserciones** actuales no
  cubren aún el filtro por categoría del reporte de inventario.

---

## 4. Otros

- `welcome.blade.php` es el remanente del scaffold de Laravel con su propio
  Tailwind inline; no forma parte del sistema neo-brutalista.
- El POS soporta ahora pago único (efectivo) y **pago dividido** con múltiples
  métodos y cálculo de cambio; falta redondeo explícito.
- Diálogos de confirmación (feedback) integrados en las acciones de estado de
  productos, compañías y sucursales (además de facturas, usuarios y
  automatización).
- Botón de cambio de tema invertido (oscuro sobre tema claro y viceversa) para
  asegurar contraste en `resources/views/components/theme-toggle.blade.php`.
- `reportes.ventas/index` y `reportes.inventario/index` ya tienen botón de
  exportar CSV.
- Falta UI de gestión para devoluciones y cierre de caja; los catálogos maestros
  (categorías, marcas, impuestos y almacenes) ya tienen UI.

---

## 5. Orden de trabajo sugerido

1. ~~**Stock en venta** (1.1)~~ — ✅ hecho.
2. ~~**Multi-pago + validación de suma** (1.3)~~ — ✅ hecho.
3. ~~**Notas de crédito** (1.2)~~ — ✅ hecho.
4. ~~**Catálogos maestros + almacenes** (sección 2): impuestos, categorías,
   marcas y almacenes~~ — ✅ hecho.
5. ~~**Exportación de reportes** y filtros por cajero/método de pago (2, 3.3)~~
   — ✅ hecho.
6. ~~**Dashboard con datos reales** (3.1)~~ — ✅ hecho.
7. ~~**Auditoría login/logout y facturación** (3.2)~~ — ✅ hecho.
8. ~~**Baja rotación y última fecha de movimiento en inventario** (3.3)~~ — ✅ hecho.
9. Pendiente: filtro por categoría en inventario, UI de devoluciones/cierre de
   caja y limpieza de código sin uso (3.4).

---

## 6. Continuación (3.4)

- **Impuestos solo super admin:** `TaxController` ahora exige `isSuperAdmin()`
  en `index/create/store/edit/update/toggleActive/destroy` (403 al resto). Se
  ocultó el botón en `productos/index.blade.php` y el enlace del sidebar
  (`layouts/sidebar.blade.php` con `'super_admin' => true`). Pruebas de
  `TaxTest` adaptadas a esta política.
- **Iconos SVG + badge de rol:** en `app/Modules/Usuarios/Views/index.blade.php`
  se corrigió el badge de rol (usaba `bg-secondary`, inexistente en
  `tailwind.config.js`, por lo que no se veía) con un mapeo por rol a colores
  válidos, y las acciones (ver/editar/eliminar) pasaron a iconos SVG con
  `title`/`aria-label`.
- **POS: autorización para retirar productos:** nuevo
  `POST /facturacion/pos/autorizar-retiro` (`pos.autorizarRetiro`, permiso
  `pos.vender`) que acepta un **código** (`config('pos.authorization_code')`,
  env `POS_AUTHORIZATION_CODE`) **o** **credenciales de supervisor**
  (super_admin/admin/supervisor). El modal del POS (`pos.blade.php`) permite
  alternar entre ambos métodos antes de retirar un ítem.
- **Ojito de contraseña en login:** botón mostrar/ocultar en
  `resources/views/auth/partials/login-form.blade.php`.
- **Flechas de ganancia/pérdida + gráficas en reportes de ventas:**
  `ReportService::salesSummary()` ahora calcula el período anterior (`previous`),
  la serie diaria (`daily`) y el desglose por método (`payment_breakdown`); la
  vista muestra flechas ▲/▼ comparando contra el período anterior y gráficas SVG
  nativas (barras por día y dona por método). `ReportesController::ventas()` usa
  por defecto el mes actual para habilitar la comparación.
- **Descarga de factura en PDF:** se instaló `barryvdh/laravel-dompdf` (`^3.0`).
  `InvoiceController::downloadPdf()` renderiza
  `resources/views/facturacion/invoices/pdf.blade.php` con CSS inline apto para
  dompdf y responde con `Pdf::loadView(...)->download(...)`. Nueva ruta
  `GET /facturacion/facturas/{invoice}/pdf` (`facturacion.facturas.pdf`, permiso
  `facturacion.view`) y botón de descarga (icono) tanto en el detalle como en el
  índice. Prueba `test_invoice_can_be_downloaded_as_pdf`
  (`tests/Feature/Facturacion/InvoiceFlowTest.php`).
- **POS: validación de pago dividido:** el input de cada pago ahora se limita con
  `:max="maxForPayment(payment)"` y `@input="clampPayment(payment)"`, muestra la
  línea **"Falta"** y **deshabilita "Cobrar"** mientras la suma no iguale el total
  (`!paymentsValid`). Al alternar a pago dividido el pago único se ajusta al total.
- **Permisos por usuario (directos, por compañía):** nueva tabla pivote
  `permission_user` (`AccessControl/Database/Migrations/2026_05_08_000004_*`) y
  relación `User::directPermissions()`. `HasPermissions::hasPermission()` y
  `getAllPermissions()` fusionan roles + permisos directos. `UserService` expone
  `permissionsGroupedByModule`/`rolePermissionIds`/`directPermissionIds`/
  `syncDirectPermissions`/`grantablePermissionIds`; `UserController` y los
  `*UserRequest` validan y sincronizan la selección. La UI de `Usuarios`
  (`_form`) lista permisos agrupados por módulo (heredados del rol marcados y
  deshabilitados) y `show` muestra las concesiones adicionales. Restricciones:
  por compañía y un admin **solo puede otorgar lo que él posee** (super_admin sin
  límite). Pruebas: `test_admin_can_grant_extra_permission_only_to_one_user`,
  `test_admin_cannot_grant_permission_they_do_not_have`.
- **Iconos SVG en tablas:** componente `resources/views/components/action-icon.blade.php`
  (`view/edit/delete/download/issue/cancel/toggle/receipt`) usado en las tablas de
  facturas, notas de crédito, productos, categorías, marcas, impuestos, almacenes,
  clientes, usuarios, empresas, sucursales y auditoría (con `title`/`aria-label`).
  Se corrigió un `:title` de Alpine mal usado en `productos/index` (debía ser
  `title` Blade).

**Estado de pruebas:** **204 pruebas / 512 aserciones en verde**
(`TaxTest`, `ReportTest`, `PosRemovalAuthorizationTest`, `InvoiceFlowTest` y
`UserManagementTest` ampliados/añadidos).
