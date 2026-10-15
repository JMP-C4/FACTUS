# Módulo de Reportes

## Objetivo

Exponer métricas operativas de ventas e inventario para la gestión diaria del negocio.

## Historias de usuario

### HU-REP-01

Como administrador quiero ver reportes de ventas por fecha, cajero y método de pago para tomar decisiones operativas.

Criterios de aceptación:

- WHEN el usuario aplique filtros por rango de fechas THE system SHALL recalcular totales, subtotales, impuestos y número de documentos.
- IF existan anulaciones o notas de crédito THE system SHALL reflejar netos y ajustes separados.
- WHEN el usuario exporte el reporte THE system SHALL generar un archivo consistente con los filtros aplicados.

### HU-REP-02

Como supervisor quiero consultar el estado del inventario para detectar faltantes y productos de baja rotación.

Criterios de aceptación:

- WHEN se consulte el reporte de inventario THE system SHALL mostrar existencia actual, stock mínimo, costo referencial y última fecha de movimiento.
- IF un producto no tenga movimientos recientes THE system SHALL destacarlo como baja rotación según parámetro configurable.
- WHEN el usuario filtre por categoría o almacén THE system SHALL devolver solo el subconjunto solicitado.

## Reglas de negocio

- Los reportes deben leer datos consolidados sin romper el aislamiento modular.
- Las consultas pesadas deben poder ejecutarse mediante servicios de lectura optimizados.
- Los reportes deben respetar permisos y alcance por compañía o sucursal.
