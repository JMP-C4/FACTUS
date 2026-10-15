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
        // Crear tabla de compañías para soportar multi-tenancy
        Schema::create('companies', function (Blueprint $table) {
            $table->id();

            // Información básica de la compañía
            $table->string('name'); // Nombre legal de la compañía
            $table->string('nit', 20)->unique(); // NIT o identificación fiscal
            $table->string('email')->nullable(); // Email de contacto principal
            $table->string('phone', 20)->nullable(); // Teléfono de contacto

            // Información fiscal y de facturación
            $table->string('address')->nullable(); // Dirección física
            $table->string('city')->nullable(); // Ciudad
            $table->string('regime')->default('comun'); // Régimen fiscal: comun, simplificado

            // Configuración de Factus
            $table->string('factus_client_id')->nullable(); // ID de cliente en Factus
            $table->string('factus_client_secret')->nullable(); // Secret de cliente en Factus (encriptado)
            $table->boolean('factus_sandbox')->default(true); // Usar sandbox de Factus

            // Estado y control
            $table->boolean('is_active')->default(true); // Si la compañía está activa
            $table->timestamp('trial_ends_at')->nullable(); // Fecha fin de periodo de prueba

            $table->timestamps();
            $table->softDeletes(); // Soft delete para no perder datos históricos
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
