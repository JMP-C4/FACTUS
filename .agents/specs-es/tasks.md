# Roadmap de Implementación

## Convención de estado

- [x] Completado
- [-] En progreso
- [ ] Pendiente

## Estado real (auditoría 2026-10)

Los módulos de **negocio** están completos y la capa de **administración de
plataforma** (compañías, sucursales, usuarios, roles y auditoría) también quedó
implementada, más un módulo de **automatización de procesos** bajo demanda.
`php artisan test` → **113 pruebas / 265 aserciones en verde**;
`pint` limpio en los archivos de estos módulos.

Nota: los módulos nuevos `Organizacion`, `Usuarios` y `Auditoria` viven en
`app/Modules/*`; la UI de roles se añadió dentro de `AccessControl`. La
bitácora de auditoría integra las acciones de organización, usuarios y roles;
queda pendiente instrumentar login y facturación.

## Fase 0 - Fundaciones

- [x] Crear estructura `app/Modules/*` con service providers, rutas y namespaces.
- [x] Configurar Laravel Breeze con layout base neo-brutalista.
- [x] Definir modelo RBAC con roles `super_admin`, `admin`, `supervisor` y `cashier`.
- [x] Definir bindings de contratos cross-module y convención de migraciones por módulo.
- [x] Configurar variables de entorno de facturación local y archivo de configuración dedicado.
- [x] Crear seed o procedimiento de bootstrap para el primer usuario `super_admin`.
- [x] Desacoplar la API Factus (facturación 100% local) y migrar a PostgreSQL local.
- [x] Rediseño de navegación con sidebar neo-brutalista.

## Fase 1 - Catálogos y Productos [x]

- [x] Migraciones de categorías, marcas, productos e impuestos asociados.
- [x] CRUD de productos con validaciones, búsqueda y activación.
- [x] Contratos públicos para consulta de datos comerciales de producto.
- [x] Vistas Blade administrativas y componentes reutilizables.
- [x] Pruebas feature para altas, edición y unicidad.

## Fase 2 - Clientes y Datos Fiscales [x]

- [x] Migraciones y modelo de clientes.
- [x] Alta rápida desde POS y CRUD completo administrativo (index, create, store, show, edit, update, destroy).
- [x] Catálogos maestros mínimos de identificación y tributos.
- [x] Contrato de consulta fiscal para facturación.

## Fase 3 - Inventario [x]

- [x] Almacenes, stocks y movimientos.
- [x] Servicios para entrada, ajuste, reserva, confirmación y reversa.
- [x] Alertas de stock mínimo y vistas de movimientos.
- [x] Políticas de stock negativo según configuración.

## Fase 4 - Facturación local [x]

- [x] `LocalBillingService` y contrato `BillingGatewayContract`.
- [x] Bitácora de facturación y rangos de numeración.
- [x] Flujo de emisión de factura 100% local (sin servicios externos).
- [x] Anulación y emisión de comprobantes.

## Fase 5 - POS y Reportes [x]

- [x] Pantalla POS con búsqueda rápida, carrito y checkout.
- [x] Reportes de ventas e inventario con filtros.
- [x] Exportación básica y métricas de resumen.
- [x] Design tokens, estados de carga y mensajes del sistema.

## Fase 6 - Endurecimiento [x]

- [x] Revisión de seguridad, permisos y sanitización de logs.
- [x] Auditoría de privilegios de `super_admin` y separación por rol.
- [x] Suite de pruebas prioritaria y corrección de regresiones.
- [x] Procedimientos operativos de emisión y soporte.
- [x] Checklist de despliegue para PostgreSQL local y variables de entorno.

## Fase 7 - Administración de Organización [x] (nueva)

- [x] Módulo `Organizacion`: CRUD de compañías (empresas) — `CompanyService` + `CompanyController`.
- [x] Módulo `Organizacion`: CRUD de sucursales (scoped por compañía) — `BranchService` + `BranchController`.
- [x] Permisos `empresas.*` y `sucursales.*` sembrados y asignados (`empresas.*` a super_admin; `sucursales.*` a admin).
- [x] Vistas Blade neo-brutalistas para ambas entidades (index/create/edit/show + `_form`).
- [x] Pruebas feature de compañías y sucursales (8 + 7 casos).

Definition of Done:

- [x] Un super_admin puede crear/editar/eliminar compañías desde la UI.
- [x] Un admin puede crear/editar/eliminar sucursales de su compañía.
- [x] Las operaciones quedan sujetas a permisos RBAC.

## Fase 8 - Gestión de Usuarios [x] (nueva)

- [x] Módulo `Usuarios`: CRUD completo (index, create, store, show, edit, update, destroy).
- [x] Asignación de rol y alcance (compañía/sucursal) desde la UI (`UserService::syncRole`).
- [x] Edición de datos y cambio de contraseña desde la UI.
- [x] Vistas Blade neo-brutalistas.
- [x] Pruebas feature de usuarios (6 casos).

Definition of Done:

- [x] Se gestionan usuarios (crear, editar, listar, eliminar); no se permite auto-eliminarse.
- [x] Se pueden asignar/reemplazar roles con su scope.
- [x] El acceso está sujeto a `users.*` (el listado se restringe a la compañía del admin).

## Fase 9 - Roles y Permisos (UI) [x] (nueva)

- [x] Módulo `AccessControl`: pantalla de administración de roles (`RoleController` + `RoleService`).
- [x] Matriz de permisos por rol (ver y editar, agrupada por módulo con "seleccionar todos").
- [x] Permisos `roles.view` y `roles.edit` sembrados.
- [x] Pruebas feature de administración de roles (3 casos).

Definition of Done:

- [x] Se listan roles con su alcance y permisos.
- [x] Se puede actualizar la matriz de permisos de un rol.
- [x] Solo usuarios autorizados acceden.

## Fase 10 - Auditoría / Logs del sistema [x] (nueva)

- [x] Módulo `Auditoria`: tabla `activity_logs` y modelo `ActivityLog`.
- [x] Contrato `AuditLoggerContract` + servicio `AuditLogger` (redacta claves sensibles).
- [x] Integración con acciones de organización, usuarios y roles.
- [x] Visor de logs con filtros (usuario, evento, módulo, fecha).
- [x] Pruebas feature del registro y visor de auditoría (4 casos).

Definition of Done:

- [x] Las acciones críticas quedan registradas sin exponer datos sensibles.
- [x] Un super_admin puede consultar y filtrar la bitácora.
- [x] La bitácora es de solo lectura.

## Fase 11 - Automatización de procesos [x] (nueva)

- [x] Módulo `Automatizacion`: registro de procesos (`AutomationRegistry`) y ejecutor (`AutomationRunner`).
- [x] Procesos bajo demanda: emitir facturas en borrador, recalcular stock, revisar stock mínimo y depurar bitácora.
- [x] Panel con botón "Ejecutar" por proceso (los que modifican datos piden confirmación) y panel de métricas del resultado.
- [x] Permiso `automatizacion.view` sembrado y asignado; enlace en el sidebar.
- [x] Cada ejecución queda auditada en la bitácora (`automation.run`).
- [x] Pruebas feature de automatización (7 casos).

Definition of Done:

- [x] Ningún proceso corre solo: todos se inician con un clic.
- [x] El acceso está sujeto a permisos RBAC (panel y por proceso).
- [x] El resultado se muestra al usuario y queda en la bitácora.

## Pendientes transversales (no bloqueantes)

- [x] Seed de nuevos permisos de administración (`empresas.*`, `sucursales.*`, `roles.view/edit`).
- [x] Registrar providers `Auditoria`, `Organizacion` y `Usuarios` en `bootstrap/providers.php`.
- [x] Enlaces de administración en el sidebar (gated por permisos) + rebuild de assets.
- [x] Sistema de feedback modular: `x-toast`/`x-toast-host` (avisos), `x-alert`/`x-flash` (banner/alerta en línea) y `x-dialog`/`x-confirm-dialog` (modales), cableado en los layouts y usado en los módulos de administración y facturación.
- [x] Sidebar colapsable con botón de flechas y estado persistido en `localStorage`.
- [ ] Limpiar métodos/relaciones sin uso detectados (ver auditoría abajo).
- [x] Instrumentar auditoría en login/logout y en el flujo de facturación.
- [x] Notas de crédito (emisión y trazabilidad con factura origen).
- [x] Exportación de reportes a CSV (Excel pendiente).
- [x] Dashboard con datos reales (hoy usa datos demo hardcodeados).
- [x] Cobertura de pruebas de notas de crédito y reportes.
- [ ] Filtro por categoría en el reporte de inventario.
- [ ] UI de devoluciones y cierre de caja del POS.
- [x] Descarga de factura en PDF (dompdf) y enlace en listado/detalle.
- [x] Validación de pago dividido en POS (suma == total, cobro bloqueado).
- [x] Permisos adicionales por usuario (pivote `permission_user`), restringidos
  por compañía y por lo que el actor puede otorgar.
- [x] Iconos SVG en las tablas (`x-action-icon`) con `title`/`aria-label`.

## Auditoría de funciones sin uso (2026-10)

Detectadas mediante búsqueda de referencias en `app/`, `routes/`, `resources/`
y `tests/`. Ninguna está referenciada fuera de su definición:

- `App\Modules\Inventario\Services\InventoryService::listStocks()`
- `App\Modules\Productos\Models\Product::hasStock()`
- `App\Modules\Productos\Models\Product::getPriceWithTax()`
- `App\Modules\Inventario\Models\Stock::isBelowMinimum()`
- `App\Modules\Inventario\Models\Warehouse::scopeDefault()`
- `App\Modules\Facturacion\Models\Invoice::isIssued()`
- `App\Modules\Facturacion\Models\Invoice::billingLogs()`
- `App\Modules\Facturacion\Models\NumberingRange::isWithinRange()`
- `App\Modules\AccessControl\Models\UserRole::assignedBy()`

Nota: `InventoryService::getLowStockAlerts()` estaba sin uso y ahora lo consume
el proceso `inventario.alertas_stock_minimo`. Los `scope*` `byType`,
`facturable`, `module` y `event` sí se usan (vía `->byType()`, `->facturable()`,
`->module()`, `->event()`).
