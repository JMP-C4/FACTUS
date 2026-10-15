<?php

namespace App\Modules\AccessControl\Database\Seeders;

use App\Modules\AccessControl\Models\Permission;
use App\Modules\AccessControl\Models\Role;
use Illuminate\Database\Seeder;

/**
 * PermissionSeeder
 *
 * Define todos los permisos del sistema organizados por módulo funcional.
 * Los permisos se asignan a roles mediante la tabla permission_role.
 *
 * Estructura de permisos: "modulo.accion"
 * Ejemplo: "productos.crear", "facturacion.emitir", "usuarios.administrar"
 */
class PermissionSeeder extends Seeder
{
    /**
     * Crea o actualiza los permisos del sistema y los asigna a roles.
     *
     * Los permisos se organizan por módulo funcional:
     * - platform: Administración global de la plataforma
     * - usuarios: Gestión de usuarios y roles
     * - productos: Gestión del catálogo de productos
     * - clientes: Gestión de información de clientes
     * - inventario: Gestión de stock y movimientos
     * - facturacion: Emisión y gestión de facturas
     * - reportes: Acceso a reportes y análisis
     * - pos: Operación del punto de venta
     */
    public function run(): void
    {
        // Paso 1: Crear todos los permisos del sistema
        $permissions = $this->definePermissions();

        foreach ($permissions as $permissionData) {
            Permission::updateOrCreate(
                ['name' => $permissionData['name']],
                $permissionData
            );
        }

        // Paso 2: Asignar permisos a roles
        $this->assignPermissionsToRoles($permissions);
    }

    /**
     * Define la estructura de permisos por módulo funcional.
     * Cada permiso tiene: name, module, description
     *
     * @return array<int, array<string, string>>
     */
    private function definePermissions(): array
    {
        return [
            // ========================================
            // PERMISOS: PLATAFORMA (Platform)
            // ========================================
            // Acceso exclusivo de super_admin para configuraciones críticas
            [
                'name' => 'platform.manage',
                'module' => 'platform',
                'description' => 'Acceso total a configuración de plataforma, companias y seguridad.',
            ],
            [
                'name' => 'platform.logs.view',
                'module' => 'platform',
                'description' => 'Ver logs de auditoria y eventos del sistema.',
            ],

            // ========================================
            // PERMISOS: USUARIOS Y CONTROL DE ACCESO
            // ========================================
            // Administración de usuarios y roles
            [
                'name' => 'users.view',
                'module' => 'usuarios',
                'description' => 'Listar usuarios del sistema.',
            ],
            [
                'name' => 'users.create',
                'module' => 'usuarios',
                'description' => 'Crear nuevos usuarios.',
            ],
            [
                'name' => 'users.edit',
                'module' => 'usuarios',
                'description' => 'Editar información de usuarios existentes.',
            ],
            [
                'name' => 'users.delete',
                'module' => 'usuarios',
                'description' => 'Desactivar o eliminar usuarios.',
            ],
            [
                'name' => 'roles.assign',
                'module' => 'usuarios',
                'description' => 'Asignar roles a usuarios.',
            ],
            [
                'name' => 'roles.revoke',
                'module' => 'usuarios',
                'description' => 'Revocar roles de usuarios.',
            ],

            // ========================================
            // PERMISOS: PRODUCTOS Y CATÁLOGOS
            // ========================================
            // Gestión de catálogos de productos
            [
                'name' => 'productos.view',
                'module' => 'productos',
                'description' => 'Ver catálogo de productos.',
            ],
            [
                'name' => 'productos.create',
                'module' => 'productos',
                'description' => 'Crear nuevos productos.',
            ],
            [
                'name' => 'productos.edit',
                'module' => 'productos',
                'description' => 'Editar información de productos.',
            ],
            [
                'name' => 'productos.delete',
                'module' => 'productos',
                'description' => 'Desactivar productos.',
            ],
            [
                'name' => 'categorias.manage',
                'module' => 'productos',
                'description' => 'Administrar categorías de productos.',
            ],
            [
                'name' => 'marcas.manage',
                'module' => 'productos',
                'description' => 'Administrar marcas de productos.',
            ],
            [
                'name' => 'impuestos.manage',
                'module' => 'productos',
                'description' => 'Administrar tipos de impuestos.',
            ],

            // ========================================
            // PERMISOS: CLIENTES
            // ========================================
            // Gestión de cartera de clientes
            [
                'name' => 'clientes.view',
                'module' => 'clientes',
                'description' => 'Ver catálogo de clientes.',
            ],
            [
                'name' => 'clientes.create',
                'module' => 'clientes',
                'description' => 'Crear nuevos clientes.',
            ],
            [
                'name' => 'clientes.edit',
                'module' => 'clientes',
                'description' => 'Editar información de clientes.',
            ],
            [
                'name' => 'clientes.delete',
                'module' => 'clientes',
                'description' => 'Desactivar clientes.',
            ],
            [
                'name' => 'clientes.fiscal_view',
                'module' => 'clientes',
                'description' => 'Ver datos fiscales y tributarios de clientes.',
            ],

            // ========================================
            // PERMISOS: INVENTARIO
            // ========================================
            // Gestión de stock y movimientos
            [
                'name' => 'inventario.view',
                'module' => 'inventario',
                'description' => 'Consultar niveles de stock e historial.',
            ],
            [
                'name' => 'inventario.entrada',
                'module' => 'inventario',
                'description' => 'Registrar entradas de stock (compras, devoluciones).',
            ],
            [
                'name' => 'inventario.ajuste',
                'module' => 'inventario',
                'description' => 'Realizar ajustes de inventario (mermas, inconsistencias).',
            ],
            [
                'name' => 'inventario.reservar',
                'module' => 'inventario',
                'description' => 'Reservar stock para ventas pendientes.',
            ],
            [
                'name' => 'almacenes.manage',
                'module' => 'inventario',
                'description' => 'Administrar almacenes y puntos de stock.',
            ],

            // ========================================
            // PERMISOS: FACTURACIÓN
            // ========================================
            // Emisión y gestión de facturas electrónicas
            [
                'name' => 'facturacion.view',
                'module' => 'facturacion',
                'description' => 'Ver facturas emitidas y su estado.',
            ],
            [
                'name' => 'facturacion.emitir',
                'module' => 'facturacion',
                'description' => 'Emitir nuevas facturas electrónicas.',
            ],
            [
                'name' => 'facturacion.anular',
                'module' => 'facturacion',
                'description' => 'Anular facturas emitidas.',
            ],
            [
                'name' => 'facturacion.nota_credito',
                'module' => 'facturacion',
                'description' => 'Emitir notas de crédito.',
            ],
            [
                'name' => 'facturacion.resincronizar',
                'module' => 'facturacion',
                'description' => 'Resincronizar el estado de facturación local.',
            ],

            // ========================================
            // PERMISOS: REPORTES
            // ========================================
            // Acceso a reportes y análisis
            [
                'name' => 'reportes.ventas',
                'module' => 'reportes',
                'description' => 'Acceder a reportes de ventas.',
            ],
            [
                'name' => 'reportes.inventario',
                'module' => 'reportes',
                'description' => 'Acceder a reportes de inventario.',
            ],
            [
                'name' => 'reportes.financiero',
                'module' => 'reportes',
                'description' => 'Acceder a reportes financieros e impuestos.',
            ],
            [
                'name' => 'reportes.exportar',
                'module' => 'reportes',
                'description' => 'Exportar reportes a CSV/Excel.',
            ],

            // ========================================
            // PERMISOS: PUNTO DE VENTA (POS)
            // ========================================
            // Operación del registro de caja
            [
                'name' => 'pos.vender',
                'module' => 'pos',
                'description' => 'Crear y procesar ventas en el punto de venta.',
            ],
            [
                'name' => 'pos.cobrar',
                'module' => 'pos',
                'description' => 'Procesar pagos y cerrar transacciones.',
            ],
            [
                'name' => 'pos.devoluciones',
                'module' => 'pos',
                'description' => 'Procesar devoluciones en el punto de venta.',
            ],
            [
                'name' => 'pos.cierre_caja',
                'module' => 'pos',
                'description' => 'Cerrar turno de caja y cuadratura de efectivo.',
            ],
        ];
    }

    /**
     * Asigna permisos a cada rol según su responsabilidad.
     *
     * Estructura de alcance:
     * - super_admin: Acceso total a permisos
     * - admin: Acceso a administración excepto plataforma
     * - supervisor: Acceso lectura + aprobaciones
     * - cashier: Acceso solo a operación POS
     *
     * @param  array<int, array<string, string>>  $permissions
     */
    private function assignPermissionsToRoles(array $permissions): void
    {
        // Obtener los roles del sistema
        $superAdmin = Role::where('name', 'super_admin')->first();
        $admin = Role::where('name', 'admin')->first();
        $supervisor = Role::where('name', 'supervisor')->first();
        $cashier = Role::where('name', 'cashier')->first();

        // Usar los nombres de permisos para consultar por ID
        $permissionNames = array_column($permissions, 'name');

        // ========================================
        // ROL: SUPER_ADMIN
        // ========================================
        // Acceso a TODOS los permisos del sistema.
        // Este rol es especial porque no tenemos restricción de scope.
        if ($superAdmin) {
            $permissionIds = Permission::whereIn('name', $permissionNames)->pluck('id');
            $superAdmin->permissions()->sync($permissionIds);
        }

        // ========================================
        // ROL: ADMIN
        // ========================================
        // Acceso total a administración dentro de una empresa/compañía.
        // Excluye permisos de plataforma global.
        if ($admin) {
            $adminPermissions = [
                // Usuarios dentro de la compañía
                'users.view', 'users.create', 'users.edit', 'users.delete',
                'roles.assign', 'roles.revoke',
                // Productos
                'productos.view', 'productos.create', 'productos.edit', 'productos.delete',
                'categorias.manage', 'marcas.manage', 'impuestos.manage',
                // Clientes
                'clientes.view', 'clientes.create', 'clientes.edit', 'clientes.delete',
                'clientes.fiscal_view',
                // Inventario
                'inventario.view', 'inventario.entrada', 'inventario.ajuste',
                'inventario.reservar', 'almacenes.manage',
                // Facturación
                'facturacion.view', 'facturacion.emitir', 'facturacion.anular',
                'facturacion.nota_credito', 'facturacion.resincronizar',
                // Reportes
                'reportes.ventas', 'reportes.inventario', 'reportes.financiero', 'reportes.exportar',
                // POS
                'pos.vender', 'pos.cobrar', 'pos.devoluciones', 'pos.cierre_caja',
            ];
            $permissionIds = Permission::whereIn('name', $adminPermissions)->pluck('id');
            $admin->permissions()->sync($permissionIds);
        }

        // ========================================
        // ROL: SUPERVISOR
        // ========================================
        // Acceso de lectura + aprobaciones sobre operaciones sensibles.
        // Responsable de supervisar y validar movimientos.
        if ($supervisor) {
            $supervisorPermissions = [
                // Lectura
                'users.view',
                'productos.view',
                'clientes.view',
                'clientes.fiscal_view',
                'inventario.view',
                'facturacion.view',
                // Reportes
                'reportes.ventas', 'reportes.inventario', 'reportes.financiero',
                // Aprobaciones de movimientos
                'inventario.ajuste',  // Aprobar ajustes de stock
                'facturacion.anular',  // Anular facturas (validación)
                'facturacion.nota_credito',  // Aprobar notas de crédito
                'pos.devoluciones',  // Autorizar devoluciones
            ];
            $permissionIds = Permission::whereIn('name', $supervisorPermissions)->pluck('id');
            $supervisor->permissions()->sync($permissionIds);
        }

        // ========================================
        // ROL: CASHIER
        // ========================================
        // Acceso restringido solo a operación de caja (POS).
        // Rol más limitado, pensado para operadores de punto de venta.
        if ($cashier) {
            $cashierPermissions = [
                // Lectura de productos y clientes
                'productos.view',
                'clientes.view',
                'clientes.create',  // Crear cliente rápido en POS
                // Inventario (lectura)
                'inventario.view',
                // POS: operaciones de caja
                'pos.vender',
                'pos.cobrar',
                'pos.devoluciones',
                'pos.cierre_caja',
            ];
            $permissionIds = Permission::whereIn('name', $cashierPermissions)->pluck('id');
            $cashier->permissions()->sync($permissionIds);
        }
    }
}
