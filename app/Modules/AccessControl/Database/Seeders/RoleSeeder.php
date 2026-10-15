<?php

namespace App\Modules\AccessControl\Database\Seeders;

use App\Modules\AccessControl\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    // Crea los roles base del sistema
    // updateOrCreate es seguro si se re-ejecuta
    public function run(): void
    {
        $roles = [
            [
                // Acceso total: plataforma, compañías, usuarios, configuración
                'name' => 'super_admin',
                'scope_type' => 'global',
                'description' => 'Administra la plataforma completa, companias, usuarios y configuraciones criticas.',
            ],
            [
                // Administra su compañía: catálogos, clientes, facturación, reportes
                'name' => 'admin',
                'scope_type' => 'company',
                'description' => 'Administra catalogos, clientes, inventario, facturacion y reportes de una empresa.',
            ],
            [
                // Aprueba operaciones, revisa incidencias
                'name' => 'supervisor',
                'scope_type' => 'company',
                'description' => 'Aprueba ajustes, revisa incidencias y supervisa operaciones sensibles.',
            ],
            [
                // Caja: POS y ventas en la sucursal
                'name' => 'cashier',
                'scope_type' => 'branch',
                'description' => 'Opera el POS y ejecuta ventas dentro de una sucursal o punto de venta.',
            ],
        ];

        foreach ($roles as $roleData) {
            // Si existe por nombre, actualiza. Si no existe, crea.
            Role::updateOrCreate(
                ['name' => $roleData['name']],
                $roleData
            );
        }
    }
}
