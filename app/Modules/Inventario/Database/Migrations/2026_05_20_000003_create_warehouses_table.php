<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('warehouses', function (Blueprint $table): void {
            $table->id();

            // Cada almacén pertenece a una compañía.
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // Nombre y código único por compañía.
            $table->string('name');
            $table->string('code');

            // Indica si es el almacén por defecto.
            $table->boolean('is_default')->default(false);

            $table->timestamps();

            // Código único por compañía.
            $table->unique(['company_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouses');
    }
};
