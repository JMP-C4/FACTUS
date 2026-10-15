# Convenciones de Módulos

Esta guía resume la convención mínima para crear nuevos módulos dentro del monolito modular.

## Estructura esperada

```text
app/Modules/{Modulo}/
  Contracts/
  Database/
    Migrations/
    Seeders/
  Domain/
  Http/
    Controllers/
    Requests/
  Models/
  Providers/
  Routes/
  Services/
  Views/
```

## Contratos y bindings cross-module

- Los contratos públicos viven en `Contracts/`.
- Las implementaciones concretas viven en `Services/`.
- Los módulos consumidores deben depender del contrato, no de la clase concreta.
- El `ModuleServiceProvider` registra esas relaciones mediante los métodos `bindings()` y `singletons()`.

### Ejemplo

```php
protected function bindings(): array
{
    return [
        ProductCatalogContract::class => ProductCatalogService::class,
    ];
}
```

## Convención de migraciones por módulo

- Cada módulo guarda sus migraciones en `Database/Migrations/`.
- Laravel las carga automáticamente gracias a `BaseModuleServiceProvider::loadMigrationsFrom()`.
- El nombre del archivo debe describir la tabla o cambio del dominio del módulo.
- Las tablas deben usar nombres del dominio de negocio, no nombres técnicos del módulo.

### Ejemplos

- `create_roles_table`
- `create_permissions_table`
- `create_products_table`
- `create_numbering_ranges_table`

## Cuándo usar `bind` y cuándo usar `singleton`

- `bind`: cuando la implementación puede recrearse sin problema en cada resolución.
- `singleton`: cuando quieres compartir una única instancia durante el ciclo de vida del contenedor.

## Regla de oro

Si otro módulo necesita una capacidad tuya:
- expones un contrato en `Contracts/`
- registras el binding en el provider del módulo
- nunca importas directamente clases internas de otro módulo desde `Services/`, `Http/` o `Models/`
