# Glosario

## Términos del dominio

- Producto: artículo vendible con precio, impuestos y reglas mínimas de stock.
- Cliente: tercero con identificación, dirección y datos fiscales para facturación.
- Inventario: conjunto de existencias por almacén y sus movimientos.
- Movimiento de inventario: entrada, salida o ajuste que modifica existencias.
- Factura electrónica: comprobante fiscal emitido de forma local con numeración, CUFE y QR generados por el sistema.
- Nota de crédito: documento que corrige o reversa parcial o totalmente una factura.
- Rango de numeración: resolución autorizada para consecutivos de facturación.
- CUFE: código único de factura electrónica emitido y validado.
- DIAN: autoridad tributaria colombiana responsable de validación fiscal.
- POS: interfaz de punto de venta usada por el cajero.

## Términos técnicos

- Monolito modular: aplicación desplegada como una sola unidad, pero dividida en módulos cohesivos y aislados.
- Contract: interfaz pública que define cómo otro módulo puede consumir una capacidad.
- Service Layer: capa de aplicación donde viven casos de uso y orquestación.
- DTO: objeto de transferencia usado para desacoplar payloads internos y externos.
- PostgreSQL local: motor de base de datos usado como persistencia principal.
- Blade: motor de plantillas server-side de Laravel.
- Tailwind CSS: framework utility-first para el sistema de diseño.
- Neo-brutalismo: estilo visual de alto contraste, bordes notorios, sombras duras y tipografía expresiva.

## Facturación local

- `LocalBillingService`: emisor local que genera el comprobante sin llamadas HTTP.
- `BillingGatewayContract`: contrato que abstrae el emisor de facturación.
- `BillingLog`: bitácora local de operaciones de facturación y errores.
- `external_id`: identificador del documento asignado por el emisor (local).
- `status_dian`: estado de validación fiscal simulado localmente.
- `qr_url`: referencia al recurso QR de validación pública.
