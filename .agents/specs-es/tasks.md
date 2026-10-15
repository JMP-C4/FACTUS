# Roadmap de Implementación

## Convención de estado

- [x] Completado
- [-] En progreso
- [ ] Pendiente

## Fase 0 - Fundaciones

 - [x] Crear estructura `app/Modules/*` con service providers, rutas y namespaces.
- [x] Configurar Laravel Breeze con layout base neo-brutalista.
- [x] Definir modelo RBAC con roles `super_admin`, `admin`, `supervisor` y `cashier`.
- [x] Definir bindings de contratos cross-module y convención de migraciones por módulo.
 - [x] Configurar variables de entorno de facturación local y archivo de configuración dedicado.
- [x] Crear seed o procedimiento de bootstrap para el primer usuario `super_admin`.

Definition of Done:

- [x] La aplicación arranca con autenticación funcional.
- [x] Los módulos cargan rutas, vistas y migraciones de forma independiente.
- [x] El acceso distingue alcance global de plataforma y alcance por compañía/sucursal.
- [x] Existe una guía mínima de bootstrap para nuevos módulos.

## Fase 1 - Catálogos y Productos

- [x] Implementar migraciones de categorías, marcas, productos e impuestos asociados.
- [x] Construir CRUD de productos con validaciones, búsqueda y activación.
- [x] Implementar contratos públicos para consulta de datos comerciales de producto.
- [x] Crear vistas Blade administrativas y componentes reutilizables.

Definition of Done:

- [x] El catálogo puede administrarse desde UI.
- [x] Las búsquedas devuelven resultados correctos.
- [x] Hay pruebas feature para altas, edición y unicidad.

## Fase 2 - Clientes y Datos Fiscales

- [x] Implementar migraciones y modelo de clientes.
- [x] Construir alta rápida desde POS y CRUD completo administrativo.
- [x] Sincronizar catálogos maestros mínimos de identificación y tributos.
- [x] Exponer contrato de consulta fiscal para facturación.

Definition of Done:

- [x] Los clientes pueden crearse y reutilizarse en ventas.
- [x] El sistema distingue clientes completos e incompletos para facturación.
- [x] La auditoría de cambios fiscales queda cubierta.

## Fase 3 - Inventario

- [x] Implementar almacenes, stocks y movimientos.
- [x] Crear servicios para entrada, ajuste, reserva, confirmación y reversa.
- [x] Agregar alertas de stock mínimo y vistas de movimientos.
- [x] Cubrir políticas de stock negativo según configuración.

Definition of Done:

- [x] El stock cambia de forma consistente con movimientos.
- [x] Los movimientos quedan auditados con origen y usuario.
- [x] Existen pruebas para escenarios de insuficiencia y reversa.

## Fase 4 - Facturación local

- [x] Implementar `LocalBillingService` y el contrato `BillingGatewayContract`.
- [x] Persistir bitácora de facturación y rangos de numeración.
- [x] Construir flujo de emisión de factura 100% local (sin servicios externos).
- [x] Implementar anulación y emisión de comprobantes.

Definition of Done:

- [x] Se puede emitir una factura local con respuesta persistida.
- [x] Los campos `external_id`, `cufe`, `qr_url` y `status_dian` quedan almacenados.
- [x] Los errores de emisión son visibles y reprocesables.

## Fase 5 - POS y Reportes

- [x] Construir pantalla POS con búsqueda rápida, carrito y checkout.
- [x] Implementar reportes de ventas e inventario con filtros.
- [x] Agregar exportación básica y métricas de resumen.
- [x] Afinar design tokens, estados de carga y mensajes del sistema.

Definition of Done:

- [x] El cajero puede completar una venta de punta a punta.
- [x] Los reportes reflejan ventas, impuestos y estado del inventario.
- [x] La UI es consistente con el lenguaje neo-brutalista definido.

## Fase 6 - Endurecimiento

- [x] Revisar seguridad, permisos y sanitización de logs.
- [x] Auditar privilegios de `super_admin` y separación efectiva de responsabilidades por rol.
- [x] Ejecutar suite de pruebas prioritaria y corregir regresiones.
- [x] Documentar procedimientos operativos de emisión y soporte.
- [x] Preparar checklist de despliegue para PostgreSQL local y variables de entorno.

Definition of Done:

- [x] El sistema cumple requisitos funcionales y no funcionales principales.
- [x] Los módulos críticos tienen cobertura automatizada suficiente.
- [x] La documentación SDD permite continuar el desarrollo de forma guiada.
