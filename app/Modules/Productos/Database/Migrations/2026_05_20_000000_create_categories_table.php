<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table): void {
            $table->id();

            // Cada categoría pertenece a una compañía.
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // Nombre visible de la categoría.
            $table->string('name');

            // Slug único por compañía para URLs limpias.
            $table->string('slug');

            // Control de activación.
            $table->boolean('is_active')->default(true);

            $table->timestamps();

            // Unicidad del slug por compañía.
            $table->unique(['company_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
