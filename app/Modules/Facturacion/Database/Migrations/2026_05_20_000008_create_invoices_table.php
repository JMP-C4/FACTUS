<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table): void {
            $table->id();

            // Relaciones base.
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('numbering_range_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Estado de la factura.
            $table->string('status', 20)->default('draft');
            $table->string('status_dian', 20)->nullable();

            // Datos de integración Factus.
            $table->string('factus_id')->nullable();
            $table->string('cufe')->nullable();
            $table->string('qr_url')->nullable();

            // Número consecutivo de la factura.
            $table->string('invoice_number');

            // Fecha de emisión.
            $table->timestamp('issued_at')->nullable();

            // Totales.
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount_total', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);

            // Métodos de pago de la factura.
            $table->string('payment_form_code', 10)->default('1');
            $table->string('payment_method_code', 20)->default('cash');

            // Payloads de Factus.
            $table->json('factus_payload')->nullable();
            $table->json('factus_response')->nullable();

            $table->timestamps();

            // Índices para monitoreo.
            $table->index(['company_id', 'status']);
            $table->index('status_dian');
            $table->unique('cufe');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
