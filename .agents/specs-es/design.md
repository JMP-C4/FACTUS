# Diseño del Sistema POS Modular

## Propósito

Este documento define cómo se construirá el sistema a partir de los requisitos establecidos. La prioridad es preservar el aislamiento entre módulos, la trazabilidad operativa y una experiencia UI coherente con Blade + Tailwind en estilo neo-brutalista profesional.

## Principios de diseño

- Monolito modular antes que capas Laravel acopladas por convención.
- Comunicación entre módulos únicamente por contratos y servicios de aplicación.
- Casos de uso explícitos, controladores delgados y requests dedicados.
- Modelos Eloquent enfocados en persistencia, no en orquestación de negocio cruzada.
- UI SSR con Blade y componentes reutilizables.

## Decisiones técnicas clave

- `app/Modules` será la raíz funcional del negocio.
- Cada módulo tendrá su propio `ModuleServiceProvider`.
- Las rutas se cargarán por módulo y se agruparán por contexto (`web`, `api-internal` si aplica).
- El control de acceso seguirá un esquema RBAC con rol global `super_admin` y roles operativos acotados por compañía/sucursal.
- La facturación se resuelve con un emisor local (`LocalBillingService`) detrás del contrato `BillingGatewayContract`, sin cliente HTTP externo.
- Las migraciones se segmentarán por módulo para facilitar evolución independiente.

## Paquetes y capas previstas

- Laravel + Breeze para autenticación y baseline SSR.
- Tailwind CSS para tokens y utilidades visuales.
- PostgreSQL local como fuente de verdad transaccional.
- Jobs opcionales para reprocesos no críticos.

## Estado de implementación

| Fase | Estado |
|------|--------|
| 0 - Fundaciones | Completada |
| 1 - Catálogos y Productos | Completada |
| 2 - Clientes y Datos Fiscales | Completada |
| 3 - Inventario | Completada |
| 4 - Facturación local | Completada |
| 5 - POS y Reportes | Completada |
| 6 - Endurecimiento | Completada |

## Índice del diseño detallado

- [01-arquitectura](./design/01-arquitectura.md)
- [02-estructura-proyecto](./design/02-estructura-proyecto.md)
- [03-rutas-controladores](./design/03-rutas-controladores.md)
- [04-modelos-datos](./design/04-modelos-datos.md)
- [05-vistas-blade](./design/05-vistas-blade.md)
- [06-design-tokens](./design/06-design-tokens.md)
- [07-estrategia-testing](./design/07-estrategia-testing.md)

## Documentación operativa

- [Procedimientos operativos](./OPERACIONES.md)
- [Checklist de despliegue](./DESPLEGUE.md)
