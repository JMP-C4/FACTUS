# Estrategia de Testing

## Objetivos

- Validar reglas de negocio modulares.
- Asegurar integridad entre venta, stock y emisión local.
- Detectar regresiones UI críticas en flujos SSR.

## Pirámide propuesta

- Unit tests para value objects, policies y reglas puras.
- Feature tests por módulo para controladores, requests y respuestas Blade.
- Feature tests de facturación local (POS, emisión, anulación, verificación).
- End-to-end smoke tests para login, venta básica y reporte de ventas.

## Cobertura actual (Fase 6)

| Módulo | Tests | Estado |
|--------|-------|--------|
| Auth | 4 | Login, registro, redirect, logout |
| Productos | 14 | CRUD, validación SKU, precio, impuestos, multi-tenant, búsqueda |
| Inventario | 14 | Entradas, ajustes, gateway stock, reversa, validaciones, multi-tenant |
| RBAC | 3 | Asignación de roles, verificación de permisos, bypass super_admin |
| Permisos | 3 | Middleware CheckPermission, acceso denegado, super_admin bypass |
| Facturación | 12 | POS, checkout, emisión, anulación, verificación, reportes |
| ExampleTests | 2 | Ruta raíz y smoke tests |
| Otros | 21 | Breeze defaults (registration, password reset, email verification, profile) |
| **Total** | **72** | **Todos pasan** |

## Cobertura por módulo

### Productos (14 tests)
- Crear producto con datos válidos
- Validación de campos requeridos
- Unicidad de SKU por compañía
- SKU compartido entre compañías (permitido)
- Precio no negativo
- Tasa de impuesto máxima
- Ver, editar, eliminar, toggle active
- Búsqueda por nombre
- Acceso cruzado entre compañías (403)
- Listado y formularios (vistas)

### Inventario (14 tests)
- Registro de entrada de stock
- Incremento de cantidad existente
- Validación de campos requeridos
- Validación de cantidad mínima
- Ajuste de stock
- Validación de motivo requerido
- Cantidad negativa rechazada
- Cero permitido
- Gateway: rechazo por stock insuficiente
- Gateway: salida con stock suficiente
- Gateway: reversa vía ajuste
- Gateway: auto-creación de stock
- Gateway: verificación de stock suficiente
- Acceso cruzado entre compañías (404)

## Cobertura prioritaria pendiente

- Notas de crédito (emisión y trazabilidad con factura origen).
- Clientes: validación fiscal mínima, duplicados, snapshot en factura.
- Reportes: filtros, totales y exportación.

## Estrategia de facturación local

- Probar la construcción del payload y la persistencia de `external_id`, `cufe` y `qr_url`.
- Verificar el manejo de errores y el estado `error` con su `billing_response`.
- Verificar la persistencia de la bitácora en `billing_logs` sin secretos.

## Definition of Ready para pruebas

- Cada service expone una responsabilidad clara.
- Existen factories para entidades principales.
- Los escenarios críticos están descritos en los requisitos EARS.
