<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brands', function (Blueprint $table): void {
            $table->id();

            // Cada marca pertenece a una compañía.
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // Nombre visible de la marca.
            $table->string('name');

            // Control de activación.
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Nombre único por compañía.
            $table->unique(['company_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brands');
    }
};
