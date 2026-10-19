<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="320" alt="Laravel">
</p>

# FACTUS

Plataforma de facturación electrónica multiempresa construida con **Laravel 13** sobre **PostgreSQL 16**, organizada como un **monolito modular**. Gestiona varias compañías, sucursales, usuarios con roles y permisos, catálogo de productos, clientes, inventario, facturación (facturas y notas crédito), reportes, auditoría, automatizaciones y un diagrama entidad-relación del propio esquema.

- **PHP:** ^8.3 (desarrollado sobre 8.4.1)
- **Laravel:** ^13.7
- **Base de datos:** PostgreSQL 16 (`pgsql`); los tests corren en SQLite en memoria
- **Frontend:** Blade + Alpine.js 3 + Tailwind CSS 3 + Vite 8
- **Facturación:** motor local en PostgreSQL (desacoplado de servicios externos)

---

## Arquitectura

El proyecto es un **monolito modular**: cada dominio vive en `app/Modules/<Modulo>` con sus propios `Providers`, `Routes`, `Http` (controllers/requests), `Models`, `Services` y `Database` (migraciones/seeders). La comunicación entre módulos se hace a través de **contratos** (interfaces) para mantener aislamiento.

| Módulo | Responsabilidad |
| --- | --- |
| `Organizacion` | Compañías y sucursales (multi-tenant) |
| `Usuarios` | Gestión de usuarios |
| `AccessControl` | Roles, permisos y asignaciones |
| `Productos` | Productos, marcas, categorías, impuestos |
| `Clientes` | Clientes |
| `Inventario` | Almacenes, existencias (stock) y movimientos |
| `Facturacion` | Facturas, notas crédito, POS, numeración (DIAN), códigos de barras/QR |
| `Reportes` | Reportes de ventas e inventario (con exportación CSV) |
| `Auditoria` | Registro de actividad (activity log) |
| `Automatizacion` | Procesos automatizados |
| `DiagramaER` | Diagrama entidad-relación generado desde el esquema real |
| `Support` | Base compartida: `BaseModuleServiceProvider`, trait `BelongsToCompany` |

Los proveedores de cada módulo se registran en `bootstrap/providers.php`. La lógica compartida de multi-tenant (scope por compañía) se apoya en el trait `App\Modules\Support\Models\Concerns\BelongsToCompany` (`scopeForCompany`).

### Multi-tenant

Modelos como `Product`, `Brand`, `Category`, `Customer`, `Invoice`, `CreditNote`, `NumberingRange`, `BillingLog` y `Warehouse` usan `BelongsToCompany` para filtrar por `company_id`, garantizando el aislamiento de datos entre compañías.

### Roles

Definidos en `AccessControl` con alcance diferenciado:

- `super_admin` (global) — administra la plataforma, compañías y usuarios.
- `admin` (company) — catálogos, clientes, inventario, facturación y reportes de una empresa.
- `supervisor` (company) — aprueba ajustes y supervisa operaciones sensibles.
- `cashier` (branch) — opera el POS y ejecuta ventas en una sucursal.

---

## Requisitos

- PHP 8.3+ con extensiones habituales (`pdo_pgsql`, `mbstring`, `openssl`, `intl`)
- Composer 2
- Node.js 20+ y npm
- PostgreSQL 16

---

## Instalación

```bash
# 1. Dependencias y arranque inicial (composer script)
composer setup
```

El script `setup` ejecuta: `composer install`, copia `.env.example` a `.env`, genera la API key, corre `php artisan migrate --force`, instala dependencias npm y compila los assets.

Ajusta la conexión a base de datos en `.env`:

```dotenv
APP_NAME=FACTUS
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=factus
```

### Datos de demo (opcional)

```bash
php artisan db:seed --class=DemoSeeder
```

---

## Desarrollo

```bash
composer dev
```

Levanta en paralelo: servidor HTTP (`php artisan serve`), worker de colas (`queue:listen`), logs (`pail`) y Vite (`npm run dev`).

Comandos individuales:

```bash
php artisan serve      # servidor de desarrollo
npm run dev            # Vite en modo watch
npm run build          # compilar assets para producción
php artisan queue:listen
```

---

## Rutas principales

- `/` — página de inicio
- `/dashboard` — panel (bento grid con KPIs, módulos y actividad reciente)
- `/profile` — perfil del usuario autenticado
- `/admin/diagrama-er` — diagrama entidad-relación del esquema (`schema`, `table/{table}/data`, `table/{table}/details`)
- Rutas de cada módulo en `app/Modules/<Modulo>/Routes/web.php` (organización, productos, clientes, inventario, facturación, reportes, auditoría, automatización, usuarios, control de acceso)

---

## Testing y calidad

Los tests usan PHPUnit sobre SQLite en memoria (`phpunit.xml`).

```bash
php artisan test                 # suite completa
php artisan test --filter=ReportTest
php artisan test --filter=DiagramaER
vendor/bin/pint                  # estilo de código (Laravel Pint)
```

Estructura de tests en `tests/Feature/{AccessControl,Auditoria,Automatizacion,DiagramaER,Facturacion,Inventario,Organizacion,Productos,Reportes,Ui,Usuarios}`. Hay además pruebas de carga en `tests/performance/` (script `load-test.cjs`).

---

## Configuración de facturación

El catálogo de facturación está centralizado en `config/billing.php`: métodos de pago, códigos de documento DIAN (factura `01`, nota crédito `91`), unidad de medida por defecto (`94`), tolerancia de pago y roles autorizados para anulaciones en POS. Evita hardcodear estos valores en servicios y requests.

---

## Estructura del proyecto

```
app/
  Http/                 Controladores y middleware globales
  Models/               Modelos compartidos (User, Company, ...)
  Modules/              Dominios en monolito modular
    <Modulo>/
      Providers/        Service provider del módulo
      Routes/           Rutas web del módulo
      Http/             Controllers y Requests
      Models/           Modelos Eloquent
      Services/         Lógica de dominio
      Database/         Migraciones y seeders
  Services/             Servicios de aplicación (p. ej. DashboardService)
config/                 Configuración (incl. billing.php)
database/               Migraciones y seeders base
resources/views/        Plantillas Blade (layout, dashboard, módulos)
routes/                 Rutas base (web, auth, console)
tests/                  Pruebas funcionales, de UI y de rendimiento
```

---

## Licencia

Software propietario. Todos los derechos reservados.
