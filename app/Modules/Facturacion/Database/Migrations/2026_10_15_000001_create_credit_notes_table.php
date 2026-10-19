<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_notes', function (Blueprint $table): void {
            $table->id();

            // Relaciones base.
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Estado del documento.
            $table->string('status', 20)->default('draft');
            $table->string('status_dian', 20)->nullable();

            // Datos de facturación local.
            $table->string('number')->nullable();
            $table->string('external_id')->nullable();
            $table->string('cufe')->nullable();
            $table->string('qr_url')->nullable();

            // Concepto de corrección DIAN (1..5) y motivo.
            $table->string('correction_concept_code', 2);
            $table->text('reason')->nullable();

            // Totales acreditados.
            $table->decimal('total', 14, 2)->default(0);

            // Payloads de facturación local.
            $table->json('billing_payload')->nullable();
            $table->json('billing_response')->nullable();

            $table->timestamp('issued_at')->nullable();

            $table->timestamps();

            $table->index(['company_id', 'status']);
            $table->index('invoice_id');
            $table->unique('cufe');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_notes');
    }
};
