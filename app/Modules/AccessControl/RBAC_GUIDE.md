# Guía de Control de Acceso Basado en Roles (RBAC)

## Descripción General

El sistema FACTUS implementa un modelo RBAC (Role-Based Access Control) que permite gestionar permisos y acceso a la plataforma de forma granular y escalable.

### Conceptos Clave

- **Rol (Role)**: Agrupación de permisos con un nombre único y alcance definido
  - `super_admin` - Acceso total a la plataforma (global)
  - `admin` - Administrador de empresa (company scope)
  - `supervisor` - Supervisor de operaciones (company scope)
  - `cashier` - Operador de caja (branch scope)

- **Permiso (Permission)**: Acción específica que un usuario puede ejecutar
  - Nombrados como `modulo.accion` (ej: `productos.crear`, `facturacion.emitir`)
  - Agrupados por módulo funcional

- **Asignación de Rol (UserRole)**: Vinculación entre usuario y rol con scope
  - Permite asignar roles a usuarios en contextos específicos (empresa, sucursal)
  - Registra quién asignó el rol para auditoría

---

## Estructura de Permisos

Los permisos se organizan por módulo funcional:

### Platform (Plataforma)
- `platform.manage` - Acceso total a configuración de plataforma
- `platform.logs.view` - Ver logs de auditoria

### Usuarios
- `users.view`, `users.create`, `users.edit`, `users.delete`
- `roles.assign`, `roles.revoke`

### Productos
- `productos.view`, `productos.create`, `productos.edit`, `productos.delete`
- `categorias.manage`, `marcas.manage`, `impuestos.manage`

### Clientes
- `clientes.view`, `clientes.create`, `clientes.edit`, `clientes.delete`
- `clientes.fiscal_view` - Ver datos fiscales

### Inventario
- `inventario.view`, `inventario.entrada`, `inventario.ajuste`, `inventario.reservar`
- `almacenes.manage`

### Facturación
- `facturacion.view`, `facturacion.emitir`, `facturacion.anular`
- `facturacion.nota_credito`, `facturacion.resincronizar`

### Reportes
- `reportes.ventas`, `reportes.inventario`, `reportes.financiero`
- `reportes.exportar`

### POS (Punto de Venta)
- `pos.vender`, `pos.cobrar`, `pos.devoluciones`, `pos.cierre_caja`

---

## Roles Predefinidos

### 1. Super Admin (Global)
Acceso total a todos los módulos y configuraciones. Típicamente hay un solo super_admin.

**Permisos:**
- Todos los permisos del sistema
- Sin restricción de scope
- Crea y administra usuarios y roles

**Caso de uso:**
- Propietario/Gerente General de la plataforma
- Configuración de companias y seguridad
- Auditoría global

### 2. Admin (Company Scope)
Administrador completo dentro de una empresa específica.

**Permisos:**
- Todos excepto `platform.*`
- Acceso a usuarios dentro de la empresa
- Administración de productos, clientes, inventario, facturación

**Caso de uso:**
- Gerente de la empresa/compañía
- Responsable de operaciones comerciales

### 3. Supervisor (Company Scope)
Supervisor de operaciones con acceso de lectura y aprobaciones.

**Permisos:**
- Lectura en todos los módulos
- Aprobaciones de ajustes, anulaciones, devoluciones
- Generación de reportes

**Caso de uso:**
- Supervisor de ventas
- Validador de operaciones sensibles
- Responsable de auditoría operativa

### 4. Cashier (Branch Scope)
Operador de caja con acceso restringido a POS.

**Permisos:**
- Crear ventas en POS
- Procesar pagos
- Procesar devoluciones
- Cierre de caja

**Caso de uso:**
- Cajero en punto de venta
- Operador de caja
- Vendedor

---

## Uso en Aplicación

### 1. Verificar Permisos en Controlador

```php
use App\Models\User;

// Método 1: Verificar un permiso
if ($user->hasPermission('productos.crear')) {
    // Usuario tiene permiso
}

// Método 2: Verificar múltiples permisos (todos requeridos)
if ($user->hasAllPermissions(['productos.crear', 'productos.editar'])) {
    // Usuario tiene ambos permisos
}

// Método 3: Verificar al menos uno
if ($user->hasAnyPermission(['productos.crear', 'clientes.crear'])) {
    // Usuario tiene al menos uno
}

// Método 4: Lanzar excepción si no autorizado
try {
    $user->authorize('facturacion.emitir');
} catch (\Illuminate\Auth\Access\AuthorizationException $e) {
    // Usuario no autorizado
}
```

### 2. Proteger Rutas con Middleware

```php
// En routes/web.php

// Un permiso requerido
Route::post('/productos', [ProductoController::class, 'store'])
    ->middleware('permission:productos.crear');

// Múltiples permisos (todos requeridos)
Route::group(['middleware' => 'permission:usuarios.crear,usuarios.editar'], function () {
    // Rutas aquí
});

// Con métodos HTTP
Route::resource('/productos', ProductoController::class)
    ->middleware(['auth', 'permission:productos.view']);
```

### 3. Usar en Vistas (Blade)

```blade
@if(auth()->user()->hasPermission('productos.crear'))
    <button class="btn btn-primary">Crear Producto</button>
@endif

@if(auth()->user()->hasAnyPermission(['reportes.ventas', 'reportes.financiero']))
    <div class="reports-section">
        <!-- Contenido de reportes -->
    </div>
@endif

@if(auth()->user()->isSuperAdmin())
    <a href="/admin/platform">Configuración Plataforma</a>
@endif
```

### 4. Crear Policies de Laravel

```php
// app/Policies/ProductoPolicy.php
namespace App\Policies;

use App\Models\User;
use App\Models\Producto;

class ProductoPolicy
{
    public function create(User $user): bool
    {
        return $user->hasPermission('productos.crear');
    }

    public function update(User $user, Producto $producto): bool
    {
        return $user->hasPermission('productos.editar');
    }
}
```

---

## Setup Inicial

### 1. Ejecutar Migraciones

```bash
php artisan migrate
```

Esto crea las tablas:
- `roles` - Definiciones de roles
- `permissions` - Definiciones de permisos
- `permission_role` - Relación N:N entre permisos y roles
- `user_roles` - Asignaciones de roles a usuarios

### 2. Ejecutar Seeders

```bash
php artisan db:seed
```

Esto:
- Crea los 4 roles predefinidos (super_admin, admin, supervisor, cashier)
- Crea todos los permisos del sistema
- Asigna permisos a roles automáticamente

### 3. Crear Primer Super Admin

```bash
php artisan rbac:create-super-admin
```

El comando será interactivo:
- Pide nombre, email y contraseña
- Valida que no exista un super_admin
- Crea el usuario y lo asigna al rol

---

## Auditoría y Logging

### Registro de Asignaciones de Rol

Cada vez que se asigna un rol a un usuario, se registra:
- `user_id` - Quién recibe el rol
- `role_id` - Qué rol se asigna
- `company_id` - Scope de empresa (si aplica)
- `branch_id` - Scope de sucursal (si aplica)
- `assigned_by` - Quién hizo la asignación
- `created_at` / `updated_at` - Cuándo se asignó

### Logging de Acceso Denegado

Cuando un usuario intenta acceder a un recurso sin permisos, se registra en logs:
- ID del usuario
- Permisos requeridos vs tenidos
- Ruta intentada
- IP del cliente

```php
// En app/Modules/AccessControl/Http/Middleware/CheckPermission.php
\Log::warning('Acceso denegado por permisos insuficientes', [
    'user_id' => $user->id,
    'required_permissions' => $requiredPermissions,
    'route' => $request->getPathInfo(),
]);
```

---

## Extensión del Sistema

### Agregar Nuevo Permiso

1. Agregar a `PermissionSeeder`:
```php
[
    'name' => 'nueva_funcionalidad.accion',
    'module' => 'modulo_nuevo',
    'description' => 'Descripción del permiso',
],
```

2. Asignar a roles en `assignPermissionsToRoles()`:
```php
$adminPermissions = [
    // ... otros permisos
    'nueva_funcionalidad.accion',
];
```

3. Ejecutar seeder:
```bash
php artisan db:seed --class=PermissionSeeder
```

### Crear Nuevo Rol

1. En `RoleSeeder`:
```php
[
    'name' => 'nuevo_rol',
    'scope_type' => 'company',  // o 'global', 'branch'
    'description' => 'Descripción del rol',
],
```

2. En `PermissionSeeder`, asignar permisos a ese rol:
```php
$newRole = Role::where('name', 'nuevo_rol')->first();
if ($newRole) {
    $permissionIds = Permission::whereIn('name', [...])->pluck('id');
    $newRole->permissions()->sync($permissionIds);
}
```

---

## Mejores Prácticas

1. **Nombrado consistente**: Usar `modulo.accion` para permisos
2. **Permisos granulares**: No tener permisos demasiado amplios
3. **Roles con propósito claro**: Cada rol debe tener un caso de uso definido
4. **Auditoría**: Siempre registrar quién hace cambios de rol/permiso
5. **Separación de responsabilidades**: Super_admin debe ser excepcional
6. **Documentación**: Mantener actualizado este archivo con nuevos permisos
7. **Pruebas**: Cubrir con tests las rutas protegidas
8. **Logging**: Monitorear intentos de acceso denegado

---

## Archivos Relacionados

- `app/Modules/AccessControl/Models/Role.php` - Modelo de Rol
- `app/Modules/AccessControl/Models/Permission.php` - Modelo de Permiso
- `app/Modules/AccessControl/Models/UserRole.php` - Asignación de Rol
- `app/Modules/AccessControl/Traits/HasPermissions.php` - Trait con métodos de autorización
- `app/Modules/AccessControl/Http/Middleware/CheckPermission.php` - Middleware de protección
- `app/Modules/AccessControl/Console/Commands/CreateSuperAdminCommand.php` - Comando de bootstrap
- `app/Modules/AccessControl/Database/Seeders/RoleSeeder.php` - Seeder de roles
- `app/Modules/AccessControl/Database/Seeders/PermissionSeeder.php` - Seeder de permisos

---

## Roadmap Futuro

- [ ] Políticas (Policies) específicas por entidad
- [ ] Permisos dinámicos en dashboard
- [ ] UI para administración de roles/permisos
- [ ] Auditoría avanzada con historial de cambios
- [ ] Integración con 2FA para super_admin
- [ ] Permisos basados en atributos (ABAC)
