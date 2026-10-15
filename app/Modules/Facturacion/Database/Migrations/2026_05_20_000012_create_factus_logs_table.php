<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('factus_logs', function (Blueprint $table): void {
            $table->id();

            $table->foreignId('company_id')->constrained()->cascadeOnDelete();

            // Referencia polimórfica al documento.
            $table->nullableMorphs('loggable');

            // Datos de la petición HTTP.
            $table->string('endpoint');
            $table->string('http_method', 10);
            $table->json('request_body')->nullable();
            $table->json('response_body')->nullable();
            $table->unsignedInteger('http_status');

            // Estado de la operación.
            $table->string('status', 20)->default('pending');

            // Mensaje de error si aplica.
            $table->text('error_message')->nullable();

            // Fecha de procesamiento.
            $table->timestamp('processed_at')->nullable();

            $table->timestamps();

            // Índices para monitoreo.
            $table->index(['company_id', 'status']);
            $table->index('loggable_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('factus_logs');
    }
};
