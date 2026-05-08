<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table): void {
            // Identificador interno del rol.
            $table->id();

            // Nombre tecnico unico del rol.
            // Ejemplos esperados: super_admin, admin, supervisor, cashier.
            $table->string('name')->unique();

            // Define el nivel natural del rol.
            // global: rol con alcance sobre toda la plataforma.
            // company: rol acotado a una empresa.
            // branch: rol acotado a una sucursal.
            $table->string('scope_type', 20);

            // Texto de apoyo para explicar la intencion del rol.
            $table->string('description')->nullable();

            // Laravel guarda created_at y updated_at.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
