# Requisitos del Sistema POS Modular

## Propósito

Este documento define qué debe hacer el sistema de gestión de stock con facturación modular (POS) antes de iniciar la implementación. El alcance cubre productos, clientes, inventario, facturación electrónica local y reportes operativos bajo una arquitectura de monolito modular en Laravel.

## Objetivos de negocio

- Reducir el tiempo de emisión de una venta en caja.
- Mantener trazabilidad entre stock, documento fiscal y movimiento de inventario.
- Cumplir con el flujo de facturación electrónica colombiano de forma 100% local.
- Permitir crecimiento funcional sin romper el aislamiento entre módulos.
- Ofrecer una experiencia de usuario clara, de alto contraste y consistente con un sistema visual neo-brutalista.

## Actores

- Super Admin: gobierna la plataforma, compañías, administradores, permisos globales, configuraciones críticas y observabilidad.
- Cajero: registra ventas y consulta productos.
- Administrador: configura catálogos, clientes, inventario y reportes.
- Supervisor: aprueba ajustes, revisa errores de facturación y notas de crédito.
- Emisor local: `LocalBillingService` genera numeración, CUFE y QR sin servicios externos.

## Alcance funcional

- Módulo de Productos.
- Módulo de Clientes.
- Módulo de Inventario.
- Módulo de Facturación.
- Módulo de Reportes.
- Autenticación con Laravel Breeze.
- Gestión global de roles y permisos con alcance de plataforma y de compañía.
- Facturación local para numeración, emisión de comprobantes y anulación, sin dependencias externas.

## Restricciones y decisiones rectoras

- La arquitectura obligatoria es `app/Modules/{NombreModulo}`.
- Ningún módulo puede leer clases internas de otro módulo; la comunicación debe ocurrir mediante `Contracts` o `Services`.
- Las vistas deben renderizarse con Blade y Tailwind CSS.
- La persistencia principal será PostgreSQL local.
- Las migraciones deben organizarse por módulo.
- La emisión local debe persistir `external_id`, `cufe`, `qr_url`, `status_dian` y la bitácora en `billing_logs`.

## Criterios globales de éxito

- Todas las operaciones críticas de venta quedan auditadas.
- El stock se descuenta solo después de una transacción de venta válida.
- La factura electrónica registra estado local y estado de validación fiscal.
- Los errores de emisión son recuperables y visibles para el personal autorizado.
- La UI mantiene tokens visuales reutilizables en lugar de estilos aislados por pantalla.

## Estructura de requisitos

- [01-glosario](./requirements/01-glosario.md)
- [02-modulo-productos](./requirements/02-modulo-productos.md)
- [03-modulo-clientes](./requirements/03-modulo-clientes.md)
- [04-modulo-facturacion](./requirements/04-modulo-facturacion.md)
- [05-modulo-inventario](./requirements/05-modulo-inventario.md)
- [06-modulo-reportes](./requirements/06-modulo-reportes.md)
- [07-requisitos-funcionales](./requirements/07-requisitos-funcionales.md)
- [08-requisitos-no-funcionales](./requirements/08-requisitos-no-funcionales.md)

## Trazabilidad inicial

| Dominio | Resultado esperado |
| --- | --- |
| Productos | Catálogo confiable para búsqueda, facturación y control de stock |
| Clientes | Datos fiscales válidos y reutilizables |
| Inventario | Existencias y movimientos auditables |
| Facturación | Emisión electrónica conforme y rastreable |
| Reportes | Visibilidad operativa para decisiones diarias |
