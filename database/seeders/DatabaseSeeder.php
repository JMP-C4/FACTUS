<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\AccessControl\Database\Seeders\PermissionSeeder;
use App\Modules\AccessControl\Database\Seeders\RoleSeeder;
use App\Modules\Productos\Database\Seeders\TaxSeeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    // Orden importante para RBAC:
    // 1. Roles (super_admin, admin, supervisor, cashier)
    // 2. Permisos (asignados a roles)
    // 3. Datos de prueba
    public function run(): void
    {
        // Paso 1: Crea roles base (super_admin, admin, supervisor, cashier)
        $this->call(RoleSeeder::class);

        // Paso 2: Crea permisos y los asigna a roles
        $this->call(PermissionSeeder::class);

        // Paso 3: Datos locales de demostración (compañía, sucursal, usuarios,
        // catálogo, clientes, stock y rango de numeración).
        $this->call(TaxSeeder::class);
        $this->call(DemoSeeder::class);

        // Paso 4: Usuario de prueba (no duplica si ya existe)
        $testUser = User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => 'password']
        );

        // El dashboard exige el middleware 'verified'; se marca como verificado
        // porque no tiene flujo de verificación por email.
        if (is_null($testUser->email_verified_at)) {
            $testUser->email_verified_at = now();
            $testUser->save();
        }
    }
}
