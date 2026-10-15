<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_payments', function (Blueprint $table): void {
            $table->id();

            // Pago pertenece a una factura.
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();

            // Forma y método de pago.
            $table->string('payment_form_code', 10)->default('1');
            $table->string('payment_method_code', 20)->default('cash');

            // Monto pagado con este método.
            $table->decimal('amount', 14, 2);

            // Campos condicionales según método.
            $table->date('due_date')->nullable();
            $table->string('reference_number')->nullable();
            $table->string('card_last_four', 4)->nullable();
            $table->string('card_approval_code')->nullable();
            $table->string('bank_entity')->nullable();

            // Para pagos en efectivo.
            $table->decimal('cash_received', 14, 2)->nullable();
            $table->decimal('cash_change', 14, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_payments');
    }
};
