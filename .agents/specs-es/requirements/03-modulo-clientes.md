# Módulo de Clientes

## Objetivo

Centralizar la información comercial y fiscal de los compradores para su reutilización en facturación electrónica y reportes.

## Historias de usuario

### HU-CLI-01

Como administrador quiero registrar clientes con cédula o NIT, dirección y datos tributarios para emitir facturas válidas.

Criterios de aceptación:

- WHEN el administrador cree un cliente THE system SHALL almacenar tipo de documento, número, nombre o razón social, correo, teléfono, dirección, departamento y municipio.
- IF falten datos fiscales obligatorios para facturación electrónica THE system SHALL marcar al cliente como incompleto para facturación.
- WHEN el cliente quede validado THE system SHALL permitir su selección en el flujo de venta.

### HU-CLI-02

Como cajero quiero buscar y seleccionar clientes rápidamente para no retrasar el cierre de la venta.

Criterios de aceptación:

- WHEN el cajero busque por nombre, documento o teléfono THE system SHALL mostrar coincidencias relevantes.
- IF no exista el cliente buscado THE system SHALL permitir crear uno nuevo desde el flujo de caja con validaciones mínimas.
- WHILE el cliente tenga datos incompletos THE system SHALL advertirlo antes de intentar facturar electrónicamente.

### HU-CLI-03

Como supervisor quiero mantener histórico de cambios fiscales para rastrear correcciones y rechazos.

Criterios de aceptación:

- WHEN se cambie el documento, tributo o razón social THE system SHALL dejar registro de auditoría.
- IF un cliente tenga facturas emitidas THE system SHALL conservar los datos snapshot usados por cada documento.
- WHEN se identifique un cliente duplicado THE system SHALL ofrecer una estrategia de fusión controlada sin perder trazabilidad.

## Reglas de negocio

- El documento del cliente debe ser único por compañía.
- Debe existir compatibilidad entre tipo de documento, tipo de organización y tributos usados por Factus.
- Los datos fiscales no deben resolverse directamente desde el módulo de Facturación; deben consumirse por contrato.
