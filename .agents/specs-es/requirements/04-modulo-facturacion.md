# Módulo de Facturación

## Objetivo

Orquestar la emisión de ventas, notas de crédito y pagos, generando el documento fiscal de forma local (sin servicios externos) con numeración, CUFE y QR simulados.

## Historias de usuario

### HU-FAC-01

Como cajero quiero emitir una factura a partir del carrito para cerrar una venta con soporte fiscal válido.

Criterios de aceptación:

- WHEN el cajero confirme una venta THE system SHALL validar cliente, ítems, impuestos, medios de pago y rango de numeración disponible.
- IF la validación local falle THE system SHALL impedir la emisión y explicar el error.
- WHEN la emisión local finalice exitosamente THE system SHALL persistir `external_id`, `cufe`, `qr_url`, `status_dian`, payload enviado y respuesta generada.

### HU-FAC-02

Como supervisor quiero emitir notas de crédito para corregir ventas ya facturadas sin romper la trazabilidad.

Criterios de aceptación:

- WHEN el supervisor solicite una nota de crédito THE system SHALL exigir una factura origen y un concepto de corrección.
- IF la factura origen no sea elegible THE system SHALL rechazar la operación antes de la emisión.
- WHEN la nota de crédito sea emitida THE system SHALL enlazarla con la factura original y actualizar saldos/reportes.

### HU-FAC-03

Como administrador quiero administrar los rangos de numeración locales para evitar errores operativos.

Criterios de aceptación:

- WHEN se registre un rango THE system SHALL almacenar prefijo, resolución, consecutivos y vigencia de forma local.
- IF no exista un rango activo vigente THE system SHALL impedir la emisión y explicar el error.
- WHILE un rango esté vigente THE system SHALL incrementar el consecutivo dentro de la transacción de emisión.

## Reglas de negocio

- La factura debe generarse primero como documento local en estado `draft` o `pending_submission`.
- El descuento de stock solo debe confirmarse cuando la transacción local quede comprometida.
- La numeración debe validar vigencia, prefijo y consecutivo actual.
- Los métodos de pago deben mapearse a los códigos fiscales locales soportados.
- **Múltiples métodos de pago**: Una venta puede pagarse utilizando uno o varios métodos de pago combinados:
  - **Efectivo**: Pago en efectivo con cálculo automático de cambio. Debe registrarse el monto recibido y el cambio entregado.
  - **Tarjeta débito/crédito**: Pago con tarjeta mediante terminal POS. Debe registrarse el número de aprobación y los últimos 4 dígitos de la tarjeta.
  - **Transferencia electrónica**: Pago por transferencia bancaria o PSE. Debe registrarse el número de referencia y la entidad bancaria.
  - Una misma factura puede combinarse con múltiples métodos de pago (ej: $50,000 en efectivo + $100,000 en tarjeta).
  - La suma de todos los pagos debe coincidir exactamente con el total de la factura.
