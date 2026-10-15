<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table): void {
            $table->id();

            // Cada cliente pertenece a una compañía.
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // Documento de identificación.
            $table->string('identification_document_code', 10)->default('13');
            $table->string('document_number', 20);

            // Datos de organización legal.
            $table->string('legal_organization_code', 10)->default('22');
            $table->string('tribute_code', 10)->default('ZZ');

            // Ubicación geográfica para facturación.
            $table->string('municipality_code', 10)->nullable();

            // Datos personales o de empresa.
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('company_name')->nullable();

            // Datos de contacto.
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('address')->nullable();

            // Correo para envío de facturas.
            $table->string('billing_email')->nullable();

            // Indica si el cliente tiene datos suficientes para facturación electrónica.
            $table->boolean('is_facturable')->default(false);

            $table->timestamps();

            // Documento único por compañía.
            $table->unique(['company_id', 'document_number']);

            // Índices para búsquedas frecuentes.
            $table->index(['company_id', 'first_name']);
            $table->index(['company_id', 'last_name']);
            $table->index(['company_id', 'company_name']);
            $table->index(['company_id', 'is_facturable']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
