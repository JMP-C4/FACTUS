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
        // Crear tabla de sucursales para soportar multi-sucursal por compañía
        Schema::create('branches', function (Blueprint $table) {
            $table->id();

            // Relación con la compañía
            $table->foreignId('company_id')->constrained()->onDelete('cascade'); // Cada sucursal pertenece a una compañía

            // Información básica de la sucursal
            $table->string('name'); // Nombre de la sucursal (ej: "Sucursal Centro", "Bodega Norte")
            $table->string('code', 10)->unique(); // Código único de la sucursal (ej: "SUC01", "BOD01")
            $table->string('address')->nullable(); // Dirección física
            $table->string('city')->nullable(); // Ciudad
            $table->string('phone', 20)->nullable(); // Teléfono de la sucursal

            // Configuración de facturación
            $table->string('pos_prefix', 3)->default('001'); // Prefijo para numeración de facturas
            $table->integer('resolution_from')->default(1); // Rango de numeración desde
            $table->integer('resolution_to')->default(999999999); // Rango de numeración hasta
            $table->date('resolution_date')->nullable(); // Fecha de resolución DIAN

            // Configuración de inventario
            $table->boolean('allows_negative_stock')->default(false); // Permite stock negativo
            $table->boolean('is_main')->default(false); // Si es la sucursal principal
            $table->boolean('is_warehouse')->default(false); // Si es bodega/solo inventario

            // Estado y control
            $table->boolean('is_active')->default(true); // Si la sucursal está activa

            $table->timestamps();
            $table->softDeletes(); // Soft delete para no perder datos históricos

            // Índices para búsquedas frecuentes
            $table->index(['company_id', 'is_active']);
            $table->index(['company_id', 'code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
