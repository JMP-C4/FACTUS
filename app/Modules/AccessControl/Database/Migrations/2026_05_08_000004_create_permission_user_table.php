<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permission_user', function (Blueprint $table): void {
            // Relacion con la tabla users.
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Relacion con la tabla permissions.
            $table->foreignId('permission_id')
                ->constrained('permissions')
                ->cascadeOnDelete();

            // Evita asignar el mismo permiso dos veces al mismo usuario.
            $table->primary(['user_id', 'permission_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permission_user');
    }
};
