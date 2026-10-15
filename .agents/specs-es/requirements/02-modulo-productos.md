# Módulo de Productos

## Objetivo

Administrar el catálogo comercial que alimenta la búsqueda en POS, el cálculo de precios en facturación y el control de stock mínimo.

## Historias de usuario

### HU-PRO-01

Como administrador quiero crear productos con categoría, precio, stock mínimo y código de barras para venderlos y controlarlos de forma uniforme.

Criterios de aceptación:

- WHEN el administrador registre un producto completo THE system SHALL almacenar nombre, SKU interno, código de barras, categoría, unidad de medida, precio base, impuesto y stock mínimo.
- IF el código de barras ya existe THE system SHALL rechazar el alta y mostrar un mensaje de unicidad.
- WHEN el producto quede activo THE system SHALL dejarlo disponible para búsqueda en POS y facturación.

### HU-PRO-02

Como cajero quiero buscar productos por nombre, categoría o código de barras para agregarlos rápidamente a una venta.

Criterios de aceptación:

- WHEN el cajero escriba un término de búsqueda THE system SHALL devolver coincidencias activas ordenadas por relevancia.
- IF la entrada corresponde exactamente a un código de barras THE system SHALL priorizar un único producto coincidente.
- WHILE el producto no tenga stock disponible THE system SHALL mostrar el estado de disponibilidad antes de agregarlo al carrito.

### HU-PRO-03

Como administrador quiero actualizar precios y atributos de venta para mantener vigente el catálogo sin afectar la integridad del sistema.

Criterios de aceptación:

- WHEN se modifique el precio o impuestos THE system SHALL registrar auditoría con usuario, fecha y valores anteriores.
- IF el producto participa en facturas emitidas THE system SHALL preservar historial documental sin alterar ítems históricos.
- WHEN el producto sea desactivado THE system SHALL impedir su uso en nuevas ventas.

## Reglas de negocio

- Un producto pertenece a una categoría y puede tener una marca opcional.
- Un producto debe tener al menos una unidad de medida válida según catálogo Factus o catálogo interno homologado.
- El stock mínimo se usa para alertas, no para bloquear edición.
- Los impuestos del producto deben poder resolverse sin depender de clases internas del módulo de Facturación.
- **Código de barras**: Cada producto puede tener un código de barras único (EAN-13, EAN-8, UPC-A u otro formato estándar). El código de barras es opcional pero, si se proporciona, debe ser único en toda la compañía para evitar duplicados en el catálogo. El sistema debe validar el formato del código de barras según el estándar utilizado.
