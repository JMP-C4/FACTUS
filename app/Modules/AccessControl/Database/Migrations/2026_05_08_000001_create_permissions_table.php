<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table): void {
            // Identificador interno del permiso.
            $table->id();

            // Nombre tecnico unico del permiso.
            // Ejemplos: platform.manage, products.manage, billing.issue.
            $table->string('name')->unique();

            // Modulo o area funcional a la que pertenece el permiso.
            $table->string('module', 100);

            // Explicacion legible del permiso.
            $table->string('description')->nullable();

            // Fechas de auditoria basicas.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
