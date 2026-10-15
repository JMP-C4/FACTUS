# Módulo de Inventario

## Objetivo

Controlar existencias, movimientos y ajustes por almacén para soportar ventas y reportes confiables.

## Historias de usuario

### HU-INV-01

Como administrador quiero registrar entradas y ajustes de inventario para mantener existencias reales.

Criterios de aceptación:

- WHEN se registre una entrada THE system SHALL aumentar el stock del producto en el almacén seleccionado.
- IF el movimiento requiera soporte documental THE system SHALL permitir asociar referencia externa y observaciones.
- WHEN se haga un ajuste manual THE system SHALL exigir motivo, usuario responsable y aprobación si la política lo requiere.

### HU-INV-02

Como cajero quiero que una venta descuente stock automáticamente para reflejar el inventario disponible.

Criterios de aceptación:

- WHEN una factura local se confirme THE system SHALL generar movimientos de salida por cada ítem vendido.
- IF el stock sea insuficiente y la política no permita negativos THE system SHALL bloquear la venta antes de facturar.
- WHILE el documento esté en estado borrador THE system SHALL no afectar existencias definitivas.

### HU-INV-03

Como supervisor quiero ver alertas de stock mínimo para anticipar reposiciones.

Criterios de aceptación:

- WHEN una existencia quede igual o por debajo del mínimo THE system SHALL generar una alerta visible.
- IF un producto esté inactivo THE system SHALL excluirlo de alertas operativas salvo consulta histórica.
- WHEN se reponga stock THE system SHALL cerrar o recalcular la alerta correspondiente.

## Reglas de negocio

- El stock se calcula por producto y almacén.
- Cada movimiento debe ser auditable y referenciar un origen de negocio.
- El módulo de Inventario expone operaciones de reserva, confirmación y reversa mediante contratos.
