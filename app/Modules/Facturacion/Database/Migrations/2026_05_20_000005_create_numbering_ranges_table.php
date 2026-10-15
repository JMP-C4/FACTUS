<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('numbering_ranges', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // ID de Factus para sincronización.
            $table->string('factus_id')->nullable();

            // Prefijo de la resolución (ej: "SETP").
            $table->string('prefix', 10);

            // Número de resolución de la DIAN.
            $table->string('resolution_number');

            // Número actual consecutivo.
            $table->unsignedBigInteger('current_number')->default(0);

            // Rango válido.
            $table->unsignedBigInteger('from_number');
            $table->unsignedBigInteger('to_number');

            // Vigencia.
            $table->date('valid_from');
            $table->date('valid_until');

            // Estado.
            $table->boolean('is_active')->default(true);

            // Payload completo de Factus.
            $table->json('raw_payload')->nullable();

            $table->timestamps();

            $table->unique(['company_id', 'prefix']);
            $table->index(['company_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('numbering_ranges');
    }
};
