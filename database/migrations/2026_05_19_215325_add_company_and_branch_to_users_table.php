<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Agregar campos para multi-tenancy a la tabla users
        Schema::table('users', function (Blueprint $table) {
            // Relación con compañía (nullable para super_admins que pueden ser globales)
            $table->foreignId('company_id')->nullable()->constrained()->onDelete('cascade');

            // Relación con sucursal (nullable para usuarios con acceso a todas las sucursales de la compañía)
            $table->foreignId('branch_id')->nullable()->constrained()->onDelete('cascade');

            // Índices para búsquedas frecuentes
            $table->index(['company_id', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['company_id']);
            $table->dropForeign(['branch_id']);
            $table->dropIndex(['company_id', 'branch_id']);
            $table->dropColumn(['company_id', 'branch_id']);
        });
    }
};
