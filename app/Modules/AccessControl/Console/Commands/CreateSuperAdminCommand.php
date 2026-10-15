<?php

namespace App\Modules\AccessControl\Console\Commands;

use App\Models\User;
use App\Modules\AccessControl\Models\Role;
use Illuminate\Console\Command;

/**
 * CreateSuperAdminCommand
 *
 * Comando artisan para crear el primer usuario super_admin del sistema.
 * Se ejecuta típicamente durante el setup inicial de la plataforma.
 *
 * Uso:
 *   php artisan rbac:create-super-admin
 *
 * El comando es interactivo y pide:
 * - Nombre del usuario
 * - Email del usuario
 * - Contraseña (con confirmación)
 */
class CreateSuperAdminCommand extends Command
{
    /**
     * Firma del comando.
     */
    protected $signature = 'rbac:create-super-admin';

    /**
     * Descripción del comando.
     */
    protected $description = 'Crea el primer usuario super_admin del sistema con acceso total a la plataforma.';

    /**
     * Ejecuta el comando de forma interactiva.
     */
    public function handle(): int
    {
        $this->info('╔══════════════════════════════════════════════╗');
        $this->info('║      CREADOR DE USUARIO SUPER_ADMIN          ║');
        $this->info('║    Bootstrap Inicial de la Plataforma        ║');
        $this->info('╚══════════════════════════════════════════════╝');
        $this->newLine();

        // Paso 1: Validar que no exista un super_admin ya creado
        $this->validateNoExistingSuperAdmin();

        // Paso 2: Obtener información del usuario interactivamente
        $name = $this->ask('Nombre del administrador super (ej: Juan Parada)');
        $email = $this->ask('Email del administrador (ej: admin@factus.local)');

        // Validar email único
        while (User::where('email', $email)->exists()) {
            $this->error("❌ El email '{$email}' ya está registrado en el sistema.");
            $email = $this->ask('Por favor, ingresa un email diferente');
        }

        // Paso 3: Solicitar contraseña con confirmación
        $password = $this->secret('Contraseña (no se mostrará mientras escribes)');
        $passwordConfirm = $this->secret('Confirma la contraseña');

        if ($password !== $passwordConfirm) {
            $this->error('❌ Las contraseñas no coinciden.');

            return self::FAILURE;
        }

        // Validar longitud mínima de contraseña
        if (strlen($password) < 8) {
            $this->error('❌ La contraseña debe tener al menos 8 caracteres.');

            return self::FAILURE;
        }

        // Paso 4: Crear el usuario
        $this->line('');
        $this->info('📋 Creando usuario...');

        try {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => bcrypt($password),
            ]);

            $this->line('<info>✅ Usuario creado exitosamente</info>');
        } catch (\Exception $e) {
            $this->error('❌ Error al crear el usuario: '.$e->getMessage());

            return self::FAILURE;
        }

        // Paso 5: Asignar rol super_admin
        $this->info('🔐 Asignando rol super_admin...');

        try {
            $superAdminRole = Role::where('name', 'super_admin')->first();

            if (! $superAdminRole) {
                $this->error('❌ El rol super_admin no existe. Ejecuta primero: php artisan db:seed --class=RoleSeeder');
                $user->delete(); // Limpiar el usuario creado

                return self::FAILURE;
            }

            // Crear la asignación de rol sin scope (super_admin es global)
            $user->userRoles()->create([
                'role_id' => $superAdminRole->id,
                'company_id' => null,  // Sin scope de empresa
                'branch_id' => null,   // Sin scope de sucursal
                'assigned_by' => null, // Creado por bootstrap, sin usuario asignador
            ]);

            $this->line('<info>✅ Rol super_admin asignado exitosamente</info>');
        } catch (\Exception $e) {
            $this->error('❌ Error al asignar rol: '.$e->getMessage());
            $user->delete(); // Limpiar el usuario creado

            return self::FAILURE;
        }

        // Paso 6: Mostrar resumen
        $this->newLine();
        $this->info('╔══════════════════════════════════════════════╗');
        $this->info('║        ✅ USUARIO CREADO EXITOSAMENTE        ║');
        $this->info('╚══════════════════════════════════════════════╝');
        $this->newLine();

        $this->table(
            ['Propiedad', 'Valor'],
            [
                ['ID', $user->id],
                ['Nombre', $user->name],
                ['Email', $user->email],
                ['Rol', 'super_admin'],
                ['Alcance', 'Global (Plataforma Completa)'],
            ]
        );

        $this->newLine();
        $this->info('Credenciales de acceso:');
        $this->line("<fg=cyan>  Email:    {$email}</>");
        $this->line('<fg=cyan>  Password: (la que ingresaste)</>');
        $this->newLine();

        $this->comment('⚠️  RECOMENDACIONES DE SEGURIDAD:');
        $this->line('  1. Guarda estas credenciales en un lugar seguro.');
        $this->line('  2. Crea otros usuarios admin con permisos específicos.');
        $this->line('  3. No compartas la contraseña del super_admin.');
        $this->line('  4. Considera usar un gestor de contraseñas.');
        $this->newLine();

        return self::SUCCESS;
    }

    /**
     * Valida que no exista un super_admin ya creado.
     * Previene sobrescrituras accidentales.
     */
    private function validateNoExistingSuperAdmin(): void
    {
        $superAdminRole = Role::where('name', 'super_admin')->first();

        if (! $superAdminRole) {
            $this->error('❌ El rol super_admin no existe en la base de datos.');
            $this->info('Ejecuta primero: php artisan db:seed --class=RoleSeeder');
            exit(1);
        }

        $existingSuperAdmin = User::whereHas('roles', function ($query) {
            $query->where('name', 'super_admin');
        })->first();

        if ($existingSuperAdmin) {
            $this->warn('⚠️  YA EXISTE UN USUARIO SUPER_ADMIN EN EL SISTEMA');
            $this->line("   Nombre: {$existingSuperAdmin->name}");
            $this->line("   Email: {$existingSuperAdmin->email}");
            $this->newLine();

            $proceed = $this->confirm('¿Deseas crear otro usuario super_admin de todas formas?', false);

            if (! $proceed) {
                $this->info('✓ Operación cancelada.');
                exit(0);
            }

            $this->warn('⚠️  Continuando con la creación de un nuevo super_admin.');
            $this->newLine();
        }
    }
}
