<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table): void {
            $table->id();

            // Ítem pertenece a una factura.
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();

            // Producto de referencia.
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();

            // Datos del ítem (snapshot al momento de la venta).
            $table->string('description');
            $table->integer('quantity');
            $table->decimal('unit_price', 14, 2);
            $table->decimal('discount_total', 14, 2)->default(0);
            $table->decimal('tax_total', 14, 2)->default(0);
            $table->decimal('line_total', 14, 2);

            // Datos fiscales del ítem.
            $table->string('tax_code', 10)->nullable();
            $table->string('unit_measure_code', 10)->default('94');

            // Snapshot del producto al momento de la venta.
            $table->json('product_snapshot')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
