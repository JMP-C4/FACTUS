<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stocks', function (Blueprint $table): void {
            $table->id();

            // Stock por producto en un almacén específico.
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            // Cantidad actual en stock.
            $table->integer('quantity')->default(0);

            // Snapshot del stock mínimo al momento de crear el registro.
            $table->integer('minimum_stock_snapshot')->default(0);

            $table->timestamps();

            // Un producto solo puede tener un registro de stock por almacén.
            $table->unique(['warehouse_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stocks');
    }
};
