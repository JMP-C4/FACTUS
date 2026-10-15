# Modelos de Datos

## Principios

- Los modelos Eloquent representan persistencia.
- Los snapshots fiscales se guardan en las tablas documentales.
- Los campos de emisión fiscal (`external_id`, `cufe`, `qr_url`, `status_dian`) son obligatorios en entidades de facturación.

## Modelos principales

### Access Control y acceso

- `Role`: `id`, `name`, `scope_type`, `description`, timestamps.
- `Permission`: `id`, `name`, `module`, `description`, timestamps.
- `PermissionRole`: `role_id`, `permission_id`.
- `UserRole`: `id`, `user_id`, `role_id`, `company_id`, `branch_id`, `assigned_by`, timestamps.
- `Company`: `id`, `name`, `slug`, `tax_id`, `is_active`, timestamps.
- `Branch`: `id`, `company_id`, `name`, `code`, `address`, `is_active`, timestamps.

#### Reglas de alcance

- `super_admin` usa `scope_type = global` y no requiere `company_id` ni `branch_id` en `UserRole`.
- `admin`, `supervisor` y `cashier` usan `scope_type = company` o `branch` según corresponda.
- Un mismo usuario puede tener más de un rol si los alcances no entran en conflicto.

### Productos

- `Category`: `id`, `company_id`, `name`, `slug`, `is_active`, timestamps.
- `Brand`: `id`, `company_id`, `name`, `is_active`, timestamps.
- `Product`: `id`, `company_id`, `category_id`, `brand_id`, `name`, `description`, `sku`, `barcode`, `unit_measure_code`, `standard_code`, `price`, `tax_rate`, `minimum_stock`, `is_active`, timestamps.

#### Detalle de campos de código de barras

- `barcode`: Código de barras único del producto (opcional pero único si se proporciona).
  - Formatos soportados: EAN-13 (13 dígitos), EAN-8 (8 dígitos), UPC-A (12 dígitos), o código interno personalizado.
  - Validación: El sistema debe verificar el dígito de control según el formato detectado.
  - Restricción: Debe ser único por `company_id` para evitar duplicados en el catálogo.
  - Uso en POS: Permite escaneo con lector externo o ingreso manual para búsqueda rápida.

### Clientes

- `Customer`: `id`, `company_id`, `identification_document_code`, `document_number`, `legal_organization_code`, `tribute_code`, `municipality_code`, `first_name`, `last_name`, `company_name`, `email`, `phone`, `address`, `billing_email`, `is_facturable`, timestamps.

### Inventario

- `Warehouse`: `id`, `company_id`, `name`, `code`, `is_default`, timestamps.
- `Stock`: `id`, `warehouse_id`, `product_id`, `quantity`, `minimum_stock_snapshot`, timestamps.
- `StockMovement`: `id`, `warehouse_id`, `product_id`, `movement_type`, `quantity`, `reference_type`, `reference_id`, `reason`, `performed_by`, `meta_json`, timestamps.

### Facturación

- `NumberingRange`: `id`, `company_id`, `external_id`, `prefix`, `resolution_number`, `current_number`, `from_number`, `to_number`, `valid_from`, `valid_until`, `is_active`, `raw_payload`, timestamps.
- `Invoice`: `id`, `company_id`, `branch_id`, `customer_id`, `numbering_range_id`, `user_id`, `status`, `status_dian`, `external_id`, `cufe`, `qr_url`, `invoice_number`, `issued_at`, `subtotal`, `discount_total`, `tax_total`, `total`, `payment_form_code`, `payment_method_code`, `billing_payload`, `billing_response`, timestamps.
- `InvoiceItem`: `id`, `invoice_id`, `product_id`, `description`, `quantity`, `unit_price`, `discount_total`, `tax_total`, `line_total`, `tax_code`, `unit_measure_code`, `product_snapshot`, timestamps.
- `InvoicePayment`: `id`, `invoice_id`, `payment_form_code`, `payment_method_code`, `amount`, `due_date`, `reference_number`, `card_last_four`, `card_approval_code`, `bank_entity`, `cash_received`, `cash_change`, timestamps.

#### Detalle de campos de métodos de pago

El modelo `InvoicePayment` soporta múltiples métodos de pago por factura. Una factura puede tener varios registros de `InvoicePayment` que sumen el total.

- `payment_form_code`: Código de forma de pago según catálogo Factus (ej: 1=Efectivo, 2=Instrumento no monetario).
- `payment_method_code`: Código de método de pago específico:
  - **Efectivo**: `cash` - Pago en efectivo con cálculo de cambio.
    - `cash_received`: Monto recibido del cliente.
    - `cash_change`: Cambio entregado al cliente.
  - **Tarjeta débito**: `debit_card` - Pago con tarjeta débito.
  - **Tarjeta crédito**: `credit_card` - Pago con tarjeta de crédito.
    - `card_last_four`: Últimos 4 dígitos de la tarjeta (para trazabilidad).
    - `card_approval_code`: Número de aprobación del terminal POS.
  - **Transferencia/PSE**: `transfer` - Transferencia bancaria o PSE.
    - `reference_number`: Número de referencia de la transferencia.
    - `bank_entity`: Entidad bancaria origen/destino.
- `amount`: Monto pagado con este método.
- **Restricción**: La suma de todos los `InvoicePayment` de una factura debe coincidir con `Invoice.total`.
- `CreditNote` (previsto): `id`, `invoice_id`, `external_id`, `cufe`, `qr_url`, `status`, `status_dian`, `correction_concept_code`, `reason`, `total`, `billing_payload`, `billing_response`, timestamps.

### Facturación local

- `BillingLog`: `id`, `company_id`, `loggable_type`, `loggable_id`, `endpoint`, `http_method`, `request_body`, `response_body`, `http_status`, `status`, `error_message`, `processed_at`, timestamps.

## Relaciones clave

- `UserRole` pertenece a `User`, `Role`, `Company` y opcionalmente `Branch`.
- `Role` tiene muchos `Permission` mediante `PermissionRole`.
- `Invoice` pertenece a `Customer`, `NumberingRange` y `User`.
- `Invoice` tiene muchos `InvoiceItem` e `InvoicePayment`.
- `CreditNote` pertenece a `Invoice`.
- `StockMovement` referencia el origen de negocio mediante `reference_type/reference_id`.

## Consideraciones de facturación

- `billing_payload` y `billing_response` pueden vivir como JSONB.
- `status_dian` debe indexarse para monitoreo y reportes.
- `cufe` debe ser único cuando exista.
