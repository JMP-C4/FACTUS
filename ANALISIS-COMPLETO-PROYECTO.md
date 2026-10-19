# Auditoría y Análisis Completo del Proyecto FACTUS (factus-app)

> **Fecha:** 2026-10-15  
> **Estado de la Suite de Pruebas:** **204 pruebas / 512 aserciones en verde (100% passing)** con `php artisan test`.  
> **Estilo y Linter:** Cumplimiento total de PSR-12 / Pint en el código de producción (`app/`).  
> **Arquitectura:** Monolito Modular en `app/Modules/*` desacoplado mediante Contratos y Service Providers.  
> **Diseño y UX:** Sistema Neo-brutalista de alto contraste con tokens dedicados, soporte para modo oscuro y cumplimiento de accesibilidad WCAG 2.2 AA.

---

## 1. Resumen Ejecutivo

Este documento consolida el análisis exhaustivo y la auditoría técnica del sistema **FACTUS (factus-app)** frente a los requerimientos y especificaciones arquitectónicas almacenadas en `.agents/specs-es/` y los pendientes registrados en `PENDIENTES-CRITICOS.md`.

El sistema opera como una plataforma POS y de gestión comercial multi-empresa con facturación electrónica 100% local (sin dependencias de APIs externas en tiempo de ejecución), gobernada por un modelo RBAC granular y persistencia en base de datos relacional (PostgreSQL / SQLite para testing).

---

## 2. Análisis Arquitectónico y de Ingeniería de Software

### 2.1 Arquitectura Monolito Modular (`laravel-specialist`, `laravel-patterns`)

La aplicación implementa una estructura modular estricta en `app/Modules/`, dividida en 11 contextos delimitados (*bounded contexts*):

```
app/Modules/
├── AccessControl     # RBAC (roles, permisos, asignación directa y scopes)
├── Auditoria         # Bitácora de actividad inmutable con redacción de secretos
├── Automatizacion    # Tareas y procesos operativos bajo demanda
├── Clientes          # Gestión comercial y fiscal de compradores
├── Facturacion       # Flujo POS, emisión local, notas de crédito y PDF
├── Inventario        # Almacenes, existencias y movimientos auditables
├── Organizacion      # Multi-tenancy: compañías (empresas) y sucursales
├── Productos         # Catálogo, categorías, marcas, impuestos (IVA) y códigos
├── Reportes          # Servicios de lectura y analítica de ventas/inventario
├── Support           # Proveedor base reutilizable (BaseModuleServiceProvider)
└── Usuarios          # Gestión de cuentas, perfiles y permisos directos
```

#### Reglas de Aislamiento y Comunicación Cross-Module:
- **BaseModuleServiceProvider:** Cada módulo extiende `App\Modules\Support\Providers\BaseModuleServiceProvider`, el cual registra automáticamente sus rutas (`Routes/web.php`), vistas Blade (`Views/`), migraciones (`Database/Migrations/`) y bindings de contratos.
- **Comunicación exclusivamente por Interfaces:** Ningún módulo consulta directamente los modelos Eloquent de otro módulo para operaciones de negocio. Por ejemplo:
  - `Facturacion` no toca `Stock` ni `Warehouse`; interactúa con `SaleStockContract` para validar existencias (`assertAvailable`) y descontarlas (`deductForSale`).
  - `Facturacion` consulta datos fiscales a través de `CustomerDataProviderContract` y datos de catálogo vía `ProductCatalogContract`.
  - `Auditoria` expone `AuditLoggerContract` para registrar eventos sin exponer la tabla `activity_logs`.

---

### 2.2 Patrones PHP 8.3 y Clean Code (`php-best-practices`, `clean-code`)

1. **Tipado Estricto:**
   - Servicios de dominio y listeners declaran `declare(strict_types=1);`.
   - Uso intensivo de tipos de retorno nativos, parámetros tipados y clases `final` con propiedades `readonly` en constructores promovidos.
2. **Enums Tipados (*Backed Enums*):**
   - Estados de documentos formalizados con enums respaldados:
     - `InvoiceStatus` (`draft`, `pending`, `issued`, `validated`, `cancelled`, `error`).
     - `CreditNoteStatus` (`pending`, `applied`, `rejected`, `error`).
3. **Controladores Delgados (*Thin Controllers*):**
   - Los controladores (`PosController`, `InvoiceController`, `CreditNoteController`, etc.) delegan la orquestación a servicios especializados (`InvoiceService`, `CreditNoteService`, `SaleStockService`).
4. **Validaciones en Form Requests:**
   - Las reglas complejas residen en Form Requests dedicados. Destaca `StoreInvoiceRequest`, que implementa un hook `withValidator` para verificar que la suma de pagos combinados coincida exactamente con el total de la factura (tolerancia de 0.01).
5. **Transaccionalidad e Idempotencia:**
   - La emisión de facturas y notas de crédito opera dentro de `DB::transaction()`. Si el registro local, el descuento de existencias o la simulación de firma fallan, se realiza rollback completo.
6. **Seguridad y Privacidad en Logs:**
   - `AuditLogger` sanitiza de forma recursiva cualquier propiedad con nombres sensibles (`password`, `token`, `secret`, etc.), sustituyéndolos por `[redacted]`.

---

### 2.3 Estética Frontend, Design Tokens y Accesibilidad (`frontend-design`, `tailwind-css-patterns`, `accessibility`)

1. **Línea Visual Neo-Brutalista:**
   - Tokens definidos en `resources/css/app.css` y extendidos en `tailwind.config.js`:
     - **Bordes:** `var(--border-thick)` (3px) y `var(--border-regular)` (2px) en negro sólido.
     - **Sombras Duras Desplazadas:** `8px 8px 0 0 var(--color-primary)` (`brutal-blue`), `var(--shadow-brutal-amber)` y `var(--shadow-brutal-black)`.
     - **Tipografía:** Encabezados en **Space Grotesk** (display font) y cuerpo en **Inter Tight**.
     - **Labels y Kickers:** Mayúsculas con tracking ancho (`tracking-[0.28em]`).
2. **Modo Claro / Modo Oscuro:**
   - Implementado mediante la clase `.dark` sobre CSS custom properties.
   - Botón de alternancia de tema (`x-theme-toggle`) con diseño invertido de alto contraste.
   - Script de prevención de FOUC (*Flash of Unstyled Color*) en el `<head>` del layout principal.
3. **Accesibilidad Web (WCAG 2.2 Nivel AA):**
   - **Operabilidad de Teclado:** Estados `:focus-visible` resaltados con sombra ámbar de alto contraste.
   - **Nombres Accesibles:** Componente `x-action-icon` incluye atributos `title` y `aria-label` en todas las acciones tabulares (ver, editar, eliminar, descargar PDF).
   - **Diálogos Accesibles:** Modales con atributos `role="dialog"`, `aria-modal="true"` y nombres accesibles asociados.
   - **Preferencia de Movimiento:** Regla `@media (prefers-reduced-motion: reduce)` que inhibe transiciones y animaciones para usuarios con sensibilidad vestibular.

---

## 3. Matriz de Cumplimiento de Especificaciones (Specs vs. Estado Real)

| Módulo / Spec | Requisito Formal | Estado | Evidencia y Notas de Auditoría |
| :--- | :--- | :---: | :--- |
| **Productos**<br>[`02-modulo-productos.md`](.agents/specs-es/requirements/02-modulo-productos.md) | **HU-PRO-01:** Crear productos con SKU, código de barras, categoría, marca, precio, impuesto y stock mínimo.<br>**HU-PRO-02:** Búsqueda rápida por nombre, SKU o código de barras.<br>**HU-PRO-03:** Catálogo de Impuestos (IVA 19%, 5%, 0%, Excluido) con CRUD. | **CUMPLIDO** *(Observación menor)* | - Modelos `Product`, `Category`, `Brand`, `Tax`.<br>- Endpoint `GET /productos/buscar-codigo` y búsqueda en POS.<br>- Catálogo de impuestos administrable exclusivamente por `super_admin`.<br>*(Obs: No se implementa algoritmo de dígito de control Luhn/EAN-13 estricto, permitiendo códigos internos).* |
| **Clientes**<br>[`03-modulo-clientes.md`](.agents/specs-es/requirements/03-modulo-clientes.md) | **HU-CLI-01:** Datos fiscales (NIT/Cédula, dirección, municipio, responsabilidad tributaria).<br>**HU-CLI-02:** Búsqueda y alta rápida desde caja. | **CUMPLIDO (100%)** | - `CustomerDataProviderContract` implementado.<br>- Búsqueda asíncrona en POS (`/facturacion/pos/clientes`).<br>- Snapshot de datos fiscales guardado en la factura. |
| **Inventario**<br>[`05-modulo-inventario.md`](.agents/specs-es/requirements/05-modulo-inventario.md) | **HU-INV-01:** Movimientos de entrada y ajuste.<br>**HU-INV-02:** Descuento automático de stock tras venta válida.<br>**HU-INV-03:** Alertas de stock mínimo y almacén predeterminado. | **CUMPLIDO (100%)** | - `SaleStockService` descuenta existencias tras emisión confirmada vinculando `reference_type = Invoice::class`.<br>- Borrado de almacén bloqueado si contiene existencias o historial de movimientos. |
| **Facturación Local**<br>[`04-modulo-facturacion.md`](.agents/specs-es/requirements/04-modulo-facturacion.md) | **HU-FAC-01:** Emisión 100% local, CUFE SHA-256, QR simulado, bitácora `billing_logs`.<br>**HU-FAC-02:** Notas de crédito vinculadas a factura con conceptos DIAN.<br>**HU-FAC-03:** Rangos de numeración con consecutivo e incremento atómico.<br>**PDF:** Descarga de factura en PDF. | **CUMPLIDO (100%)** | - `LocalBillingService` implementa `BillingGatewayContract` sin peticiones HTTP externas.<br>- `CreditNoteService` y vistas `/facturacion/notas-credito`.<br>- Incremento de numeración dentro de la transacción de emisión.<br>- Descarga PDF con `dompdf` (`GET /facturacion/facturas/{invoice}/pdf`). |
| **Flujo POS**<br>[`07-requisitos-funcionales.md`](.agents/specs-es/requirements/07-requisitos-funcionales.md) | **Búsqueda y Carrito:** Búsqueda y escáner de código de barras.<br>**Multi-pago:** Pagos combinados (efectivo, débito, crédito, transferencia) con validación de suma.<br>**Autorización de Retiro:** Control de seguridad para retirar ítems.<br>**Cierre de caja y devoluciones:** Gestión de turnos y notas en caja. | **PARCIAL** | - Panel de pago dividido con ajuste automático en tiempo real (`clampPayment`) y validación de suma.<br>- Modal de retiro por código secreto (`POS_AUTHORIZATION_CODE`) o credenciales de supervisor.<br>❌ **Pendiente:** Sin rutas ni vistas para `pos.cierre_caja` ni `pos.devoluciones`.<br>⚠️ **Aviso:** Si un código escaneado no existe, no sugiere crear el producto en pantalla. |
| **Reportes**<br>[`06-modulo-reportes.md`](.agents/specs-es/requirements/06-modulo-reportes.md) | **HU-REP-01:** Ventas por fecha, cajero, método de pago, ventas netas (descontando notas de crédito), gráficas SVG nativas, exportación CSV.<br>**HU-REP-02:** Inventario con existencias, stock mínimo, costo, última fecha de movimiento, baja rotación (30 días), filtro por almacén y categoría. | **MAYORITARIAMENTE CUMPLIDO** | - `ReportService::salesSummary` calcula venta bruta, notas de crédito, venta neta, comparación con período anterior (flechas ▲/▼) y series diarias SVG.<br>- `ReportService::inventoryReport` calcula última fecha de movimiento y productos de baja rotación.<br>- Exportación CSV en ventas e inventario.<br>❌ **Pendiente:** Falta el parámetro y filtro por categoría en `inventoryReport`. |
| **Plataforma y RBAC**<br>[`07-requisitos-funcionales.md`](.agents/specs-es/requirements/07-requisitos-funcionales.md) | **Multi-tenancy:** Scope global para `super_admin` (`company_id = null`), scope de compañía para el resto.<br>**Permisos Directos:** Tabla pivote `permission_user` restringida por jerarquía.<br>**Auditoría:** Login, logout, accesos fallidos y facturación.<br>**Automatización:** Procesos bajo demanda con bitácora. | **CUMPLIDO (100%)** | - Scopes multi-tenant alineados (`User::isGloballyScoped()`).<br>- Asignación de permisos directos donde un admin solo otorga lo que posee.<br>- `AuthEventListener` registrando eventos de acceso.<br>- Módulo `/automatizacion` con 4 tareas bajo demanda ejecutables con un clic. |

---

## 4. Gaps y Pendientes Identificados

### 4.1 Gaps Funcionales
1. **Filtro por Categoría en Reporte de Inventario (HU-REP-02):**
   - El método `ReportService::inventoryReport(?int $companyId, ?int $warehouseId = null, ?string $status = null)` en `app/Modules/Reportes/Services/ReportService.php:228` no acepta ni procesa `$categoryId`.
   - La vista `resources/views/reportes/inventario/index.blade.php` no cuenta con el selector de categorías.
2. **Capacidades Sembradas sin Implementación:**
   - Permiso `pos.cierre_caja`: No existe controlador, modelo ni vista para arqueos o cierres de caja.
   - Permiso `pos.devoluciones`: No existe flujo de devolución en caja (las correcciones se hacen vía Notas de Crédito administrativas).
   - Permiso `reportes.financiero`: Sembrado en `PermissionSeeder` pero sin ruta asociada.
   - Permiso `facturacion.resincronizar`: Remanente no utilizado de la integración previa con Factus.
3. **Mensaje de Escáner en POS:**
   - El requerimiento especifica que si un código de barras no coincide con ningún producto, debe desplegar un mensaje claro sugiriendo crear el producto. Actualmente se limpia el término de búsqueda sin emitir la alerta sugerida.

---

### 4.2 Código Huérfano / Sin Uso (Dead Code)
Se verificó la existencia de 9 funciones y relaciones declaradas en el código pero no referenciadas en ninguna vista, controlador, servicio o prueba:

1. `App\Modules\Inventario\Services\InventoryService::listStocks()`: Huérfano; el reporte o queries directos cubren la necesidad.
2. `App\Modules\Productos\Models\Product::hasStock()`: Huérfano y conceptualmente engañoso (retorna `$this->minimum_stock > 0` en lugar de evaluar existencias reales).
3. `App\Modules\Productos\Models\Product::getPriceWithTax()`: Huérfano; el cálculo de impuestos se realiza en la capa de servicios y en el carrito.
4. `App\Modules\Inventario\Models\Stock::isBelowMinimum()`: Huérfano; las alertas se evalúan por queries directas o procesos de automatización.
5. `App\Modules\Inventario\Models\Warehouse::scopeDefault()`: Huérfano; se utiliza `where('is_default', true)`.
6. `App\Modules\Facturacion\Models\Invoice::isIssued()`: Huérfano; se evalúa directamente el enum `status`.
7. `App\Modules\Facturacion\Models\Invoice::billingLogs()`: Huérfano; la relación vive en el modelo polimórfico `BillingLog`.
8. `App\Modules\Facturacion\Models\NumberingRange::isWithinRange()`: Huérfano; la validación se realiza al resolver el consecutivo.
9. `App\Modules\AccessControl\Models\UserRole::assignedBy()`: Huérfano; relación no consumida en vistas ni servicios.

---

## 5. Resultados de la Suite de Pruebas

Comando ejecutado: `php artisan test`
```json
{
  "tool": "phpunit",
  "result": "passed",
  "tests": 204,
  "passed": 204,
  "assertions": 512,
  "duration_ms": 28654
}
```

### Áreas Validadas:
- **Facturación:** Emisión de facturas locales, cálculo de cambio, validación de pagos múltiples divididos, generación y descarga de comprobantes en PDF.
- **Notas de Crédito:** Creación con validación de factura elegible (solo en estado emitido/validado), aplicación de conceptos de corrección y deducción de ventas netas.
- **Inventario:** Descuento de stock en venta, reversión por rollback en fallas de facturación, cálculo de productos de baja rotación (30 días) y última fecha de movimiento.
- **Seguridad y Acceso:** Acceso global multi-empresa para `super_admin` con protección contra escritura sin compañía, y concesión de permisos directos individuales limitados al alcance del administrador.
- **Auditoría y Automatización:** Registro de eventos de autenticación (login, logout, failed login) y ejecución manual de procesos bajo demanda.

---

## 6. Hoja de Ruta de Próximas Acciones

1. **Completar Requisito HU-REP-02 (Filtro por Categoría en Inventario):**
   - Incorporar el parámetro `?int $categoryId = null` en `ReportService::inventoryReport()`.
   - Agregar el filtro `<select name="category_id">` en la vista `reportes/inventario/index.blade.php`.
   - Añadir la prueba feature correspondiente en `tests/Feature/Reportes/ReportTest.php`.
2. **Refinar Feedback del Lector de Código de Barras en el POS:**
   - Si `searchByBarcode()` no encuentra resultados, emitir toast/alerta informativa: *"Código no encontrado. [Crear producto]"*.
3. **Limpieza de Código Huérfano:**
   - Remover los 9 métodos sin uso identificados para preservar los principios de Clean Code y evitar acumulación de deuda técnica.
4. **Saneamiento de Permisos Sembrados:**
   - Eliminar del seeder o implementar las pantallas para `pos.cierre_caja`, `pos.devoluciones` y `reportes.financiero`.
