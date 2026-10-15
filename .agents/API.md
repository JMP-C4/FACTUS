
---

**Contexto del Proyecto:**
Actúa como un Desarrollador Senior Full-stack experto en integraciones de API y facturación electrónica en Colombia. Mi objetivo es completar el **"Reto FACTUS"**, el cual consiste en generar una factura electrónica exitosa utilizando su API en entorno Sandbox.

> **Nota (actualizado):** La integración con la API remota de Factus fue removida.
> El sistema opera con facturación **100% local** (`LocalBillingService`): numeración,
> CUFE y QR se generan sin depender de servicios externos. Las credenciales que existían
> en este documento fueron eliminadas por seguridad. Este archivo se conserva solo como
> contexto histórico del reto original.

**Información Técnica de la API (referencia histórica):**
* **Endpoint Base (Sandbox):** `https://api-sandbox.factus.com.co`
* **Autenticación:** OAuth2 (Client Credentials).
    * **CLIENT ID:** `<redactado>`
    * **CLIENT SECRET:** `<redactado>`
    * **Usuario/Password:** `<redactado>`
* **Documentación de referencia:** `https://developers.factus.com.co/`

**Tu Tarea:**
Ayúdame a estructurar el flujo lógico para emitir una factura. Necesito que me expliques y proporciones ejemplos de código (usando un stack moderno como Laravel con React o Node.js) para los siguientes puntos:

1.  **Flujo de Autenticación:** Cómo obtener el `access_token` correctamente.
2.  **Consulta de Datos Maestros:** Cómo obtener listados de unidades de medida, tipos de documentos y, especialmente, los **rangos de numeración**.
3.  **Estructura del JSON de Facturación:** Detalla los campos obligatorios para el emisor, adquirente (cliente) e ítems (productos con impuestos).
4.  **Envío y Validación:** Cómo realizar el POST y validar la respuesta exitosa para el video del reto.

**Requerimientos de implementación:**
* Uso de variables de entorno para las credenciales.
* Manejo de errores detallado.
* Diseño de interfaz minimalista para mostrar el proceso.

---

## 💡 Notas para el Desarrollador (Juan)

4.  **Personalización:** Aprovecha tus componentes con efectos *glassmorphism* para que la "consola" o el "historial de facturas" se vea increíble en el video.

_____________________________________________________________

┌─ BASE (ya existentes) ──────────────────────────────────────┐
│  users · cache · jobs · password_reset_tokens               │
└─────────────────────────┬───────────────────────────────────┘
                          │
┌─ 1. AUTH & PERMISOS ────▼───────────────────────────────────┐
│  permissions → roles → permission_role → role_user          │
└─────────────────────────┬───────────────────────────────────┘
                          │
┌─ 2. EMPRESA ────────────▼───────────────────────────────────┐
│  companies → branches → branch_user                         │
│  [AJUSTE] users.company_id + personal_access_tokens         │
└─────────────────────────┬───────────────────────────────────┘
                          │
┌─ 3. CATÁLOGOS MAESTROS ─▼───────────────────────────────────┐
│  identification_documents · legal_organizations · tributes  │
│  payment_forms · payment_methods · unit_measures            │
│  standard_codes · correction_concepts · operation_types     │
│  taxes · withholding_taxes · departments · municipalities   │
└─────────────────────────┬───────────────────────────────────┘
                          │
┌─ 4. CLIENTES ───────────▼──── 5. INVENTARIO ────────────────┐
│  customers              │      categories → brands          │
│                         │      → products                   │
│                         │      → product_taxes              │
│                         │      → product_withholding_taxes  │
│                         │      → warehouses → stocks        │
│                         │      → stock_movements (morphs)   │
└─────────────────────────┴───────────────────────────────────┘
                          │
┌─ 6. FACTURACIÓN ────────▼───────────────────────────────────┐
│  numbering_ranges                                           │
│  → invoices → invoice_items → invoice_item_taxes            │
│  → invoice_payments                                         │
│  → credit_notes → credit_note_items → credit_note_item_taxes│
└─────────────────────────┬───────────────────────────────────┘
                          │
┌─ 7. FACTURACIÓN LOCAL ──▼───────────────────────────────────┐
│  billing_logs (morphs) · local_billing (CUFE/QR simulados)  │
└─────────────────────────────────────────────────────────────┘
