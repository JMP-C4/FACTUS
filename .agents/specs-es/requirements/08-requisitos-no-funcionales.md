# Requisitos No Funcionales

## Arquitectura y mantenibilidad

- El sistema SHALL aplicar principios SOLID en controladores, servicios, requests y contratos.
- Cada módulo SHALL encapsular `Controllers`, `Models`, `Services`, `Contracts`, `Requests`, `Routes`, `Views` y `Database`.
- Las dependencias entre módulos SHALL resolverse por interfaces registradas en service providers.

## Rendimiento

- La búsqueda de productos SHALL responder en tiempos adecuados para caja bajo carga habitual.
- Los catálogos maestros SHALL poder cachearse con vencimiento configurable.
- Los reportes pesados SHOULD ejecutarse con consultas optimizadas, índices y paginación.

## Seguridad

- La configuración sensible SHALL almacenarse exclusivamente en variables de entorno.
- La bitácora de facturación SHALL evitar secretos y datos sensibles innecesarios.
- Todas las acciones críticas SHALL quedar protegidas por autenticación, autorización y CSRF.

### Estado de cumplimiento (Fase 6)

- Configuración en `.env` con `.env.example` como plantilla segura.
- La bitácora `billing_logs` no persiste credenciales ni tokens.
- `SESSION_ENCRYPT=true` y `SESSION_SECURE_COOKIE=true` en configuración de producción.
- `APP_DEBUG=false` en `.env.example` para producción.
- Middleware `CheckPermission` protege todas las rutas con verificación de permisos.

## Confiabilidad

- La emisión de facturas SHALL usar transacciones para consistencia entre documento local y movimientos de inventario.
- La emisión local es determinista y síncrona; los fallos quedan en estado `error` con su `billing_response`.
- El sistema SHALL registrar eventos de fallo y diagnóstico suficientes para soporte.

## Calidad visual

- La UI SHALL usar una paleta de alto contraste con bordes negros notorios y sombras duras desplazadas.
- Los componentes SHALL conservar accesibilidad básica de contraste, foco y navegación por teclado.
- El sistema SHOULD mantener consistencia visual entre autenticación, POS, formularios CRUD y reportes.
