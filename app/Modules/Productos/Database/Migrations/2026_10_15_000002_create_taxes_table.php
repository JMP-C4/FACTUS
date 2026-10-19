<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxes', function (Blueprint $table): void {
            $table->id();

            // company_id nulo = impuesto global del sistema (ej. tarifas de IVA).
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('code', 30);
            $table->decimal('rate', 5, 2)->default(0);
            $table->string('type', 20)->default('vat');
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxes');
    }
};
