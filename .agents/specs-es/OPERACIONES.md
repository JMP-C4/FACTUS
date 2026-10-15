# Procedimientos Operativos - FACTUS

El sistema opera con **facturación 100% local**: no existe dependencia de la API
remota de Factus ni de servicios externos. La emisión genera numeración, CUFE y
QR simulados de forma local mediante `LocalBillingService`.

## Flujo de emisión de factura

1. El cajero o sistema crea una factura en estado `draft`.
2. Se construye el payload del comprobante a partir de los datos locales del
   emisor, cliente e ítems.
3. `LocalBillingService::issue()` genera el comprobante localmente, sin HTTP.
4. La respuesta se persiste en `billing_response` de la factura.
5. Si es exitosa: se almacenan `external_id`, `cufe`, `qr_url` y `status_dian`.
6. Si falla: el error queda registrado en `billing_logs` con estado `error`.

## Bitácora de facturación

- Cada emisión o anulación se registra en la tabla `billing_logs`.
- El registro es polimórfico (`loggable_type` / `loggable_id`) y guarda endpoint
  lógico, método, cuerpo de solicitud, respuesta y estado HTTP simulado.
- No se almacenan credenciales ni secretos.

## Numeración

- La numeración se toma del `NumberingRange` activo de la compañía.
- Se valida vigencia (`valid_from` / `valid_until`) y consecutivo
  (`current_number` < `to_number`).
- El consecutivo se incrementa dentro de la transacción de creación.

## Reintentos y reproceso

- Al no existir llamadas externas, no hay reintentos de red.
- Si una emisión falla, la factura queda en estado `error` con su
  `billing_response` para diagnóstico y puede reprocesarse desde la UI.

## Monitoreo de errores

### Consulta de errores

```sql
-- Facturas con errores de emisión
SELECT id, invoice_number, status, status_dian, billing_response
FROM invoices
WHERE status = 'error'
ORDER BY updated_at DESC;

-- Últimos logs de error
SELECT endpoint, http_method, http_status, error_message, processed_at
FROM billing_logs
WHERE status = 'error'
ORDER BY processed_at DESC
LIMIT 20;
```

## Backup y recuperación

### Base de datos (PostgreSQL local)

- La base de datos es un PostgreSQL local (base `factus` por defecto).
- Backup recomendado:

  ```bash
  pg_dump -U factus -h 127.0.0.1 factus > backup_factus_$(date +%F).sql
  ```

- Restauración:

  ```bash
  psql -U factus -h 127.0.0.1 factus < backup_factus_AAAA-MM-DD.sql
  ```

- Las tablas críticas: `invoices`, `billing_logs`, `stocks`, `stock_movements`.

### Variables de entorno

- Nunca commitear `.env` en el repositorio.
- La configuración de facturación local (`BILLING_PREFIX`, `BILLING_ISSUER_NAME`)
  no contiene secretos.

## Procedimiento de emergencia

### Si una factura no se emite

1. La factura queda en estado `error` (no `pending`).
2. No se pierden datos: el payload queda en `billing_payload` y la respuesta en
   `billing_response`.
3. Corregir la causa y reprocesar la emisión desde la UI.

### Si se pierde una factura

1. Verificar `billing_logs` para el estado de la última operación.
2. Buscar por `external_id` o `cufe` en la base de datos.
3. Nunca reemitir sin verificar el estado anterior (evitar duplicados).
