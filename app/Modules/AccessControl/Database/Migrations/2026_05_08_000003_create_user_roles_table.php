<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_roles', function (Blueprint $table): void {
            // Identificador interno de la asignacion.
            $table->id();

            // Usuario al que se le asigna el rol.
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Rol asignado al usuario.
            $table->foreignId('role_id')
                ->constrained('roles')
                ->cascadeOnDelete();

            // Scope de empresa.
            // Se deja sin foreign key por ahora porque la tabla companies
            // se implementara en una tarea posterior.
            $table->unsignedBigInteger('company_id')->nullable();

            // Scope de sucursal.
            // Tambien queda sin foreign key hasta que exista branches.
            $table->unsignedBigInteger('branch_id')->nullable();

            // Usuario que hizo la asignacion.
            // Permite auditoria administrativa desde el inicio.
            $table->foreignId('assigned_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Fechas de auditoria basicas.
            $table->timestamps();

            // Indices para consultas frecuentes de seguridad.
            $table->index(['user_id', 'role_id']);
            $table->index(['company_id', 'branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_roles');
    }
};
