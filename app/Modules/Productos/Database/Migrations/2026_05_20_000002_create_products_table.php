<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table): void {
            $table->id();

            // Cada producto pertenece a una compañía.
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // Categoría y marca opcionales.
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();

            // Datos básicos del producto.
            $table->string('name');
            $table->text('description')->nullable();

            // Identificadores únicos por compañía.
            $table->string('sku');
            $table->string('barcode')->nullable();

            // Datos fiscales y comerciales.
            $table->string('unit_measure_code', 10)->default('94');
            $table->string('standard_code', 10)->nullable();
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('tax_rate', 5, 2)->default(0);

            // Control de stock mínimo para alertas.
            $table->integer('minimum_stock')->default(0);

            // Control de activación.
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // SKU único por compañía.
            $table->unique(['company_id', 'sku']);

            // Código de barras único por compañía cuando se proporciona.
            $table->unique(['company_id', 'barcode']);

            // Índices para búsquedas frecuentes.
            $table->index(['company_id', 'name']);
            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
