# Requisitos Funcionales Transversales

## Autenticación y acceso

- El sistema debe usar Laravel Breeze con Blade para autenticación base.
- Debe existir separación por roles al menos para `super_admin`, `admin`, `cashier` y `supervisor`.
- El `super_admin` debe tener visibilidad transversal de todas las compañías, sucursales, usuarios, logs de integración y configuraciones críticas.
- El `admin` debe operar únicamente dentro de su compañía o sucursal autorizada.
- Los permisos deben soportar alcance global y alcance por compañía.
- El usuario autenticado debe operar dentro de una compañía y, si aplica, una sucursal activa.
- Debe existir un usuario inicial `super_admin` sembrado o creado por procedimiento controlado para la primera administración de la plataforma.

## Persistencia y catálogos

- El sistema debe administrar los catálogos maestros necesarios para facturar de forma local.
- Las migraciones deben organizarse por módulo y ejecutarse sin dependencias circulares.
- Debe existir auditoría para cambios críticos de precios, clientes fiscales, stock y documentos.

## Flujo POS

- El sistema debe permitir crear carrito, agregar ítems, recalcular totales, aplicar descuentos autorizados y seleccionar forma de pago.
- Debe soportar búsqueda rápida por código de barras.
- Debe mostrar validaciones antes de emitir una factura.
- **Búsqueda por código de barras**: El sistema debe permitir escanear códigos de barras mediante lector externo o ingresar manualmente el código. Al detectar un código de barras válido, debe agregar automáticamente el producto al carrito sin necesidad de navegación adicional. Si el código no existe en el catálogo, debe mostrar un mensaje de error claro y sugerir crear el producto.
- **Múltiples métodos de pago**: El sistema debe soportar pagos con efectivo, tarjeta débito/crédito y transferencia electrónica. Debe permitir combinar múltiples métodos de pago en una misma venta. Para cada método debe registrar la información específica requerida (monto recibido y cambio para efectivo, número de aprobación para tarjeta, referencia para transferencia).

## Facturación local

- Debe existir un `LocalBillingService` que implemente `BillingGatewayContract` con responsabilidad única sobre la emisión local.
- No debe existir dependencia de servicios externos ni gestión de tokens.
- Cada operación de facturación debe quedar trazada en `billing_logs` con request y response sin secretos.

## UX y diseño

- La interfaz debe reutilizar componentes Blade parciales y design tokens de Tailwind.
- Los encabezados, labels y CTA principales deben seguir una línea visual neo-brutalista.
- Debe existir feedback de carga, error y éxito para operaciones críticas.
