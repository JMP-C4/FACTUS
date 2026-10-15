# Vistas Blade

## Principios de UI

- Server-side rendering rápido y predecible.
- Componentes Blade reutilizables para formularios, tablas, badges y cajas métricas.
- Layouts con split-screen o bloques asimétricos en pantallas clave.

## Pantallas principales

- `auth/login.blade.php`: acceso con layout centrado, borde grueso, sombra dura desplazada y CTA dominante.
- `facturacion/pos.blade.php`: split-screen con catálogo a la izquierda y carrito/resumen a la derecha.
- `productos/index.blade.php`: tabla administrativa con filtros y acciones rápidas.
- `clientes/index.blade.php`: listado con búsqueda por documento/nombre y alta rápida.
- `inventario/movimientos/index.blade.php`: histórico con filtros por almacén, fecha y tipo.
- `reportes/ventas/index.blade.php`: métricas superiores, filtros laterales y tabla/exportación.

## Componentes Blade sugeridos

- `components/layouts/app-shell.blade.php`
- `components/ui/brutal-card.blade.php`
- `components/ui/brutal-button.blade.php`
- `components/ui/input-group.blade.php`
- `components/ui/stat-tile.blade.php`
- `components/ui/status-badge.blade.php`
- `components/pos/product-search-panel.blade.php`
- `components/pos/cart-summary.blade.php`

## Reglas de composición

- Los componentes reciben data preparada desde ViewModels o controllers simples.
- Los mensajes de error y éxito usan el mismo sistema visual.
- La jerarquía tipográfica usa mayúsculas y tracking amplio solo en labels, headings y CTAs, no en párrafos extensos.
