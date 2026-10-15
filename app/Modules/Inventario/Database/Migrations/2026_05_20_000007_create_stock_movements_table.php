<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table): void {
            $table->id();

            // Movimiento en un almacén específico para un producto.
            $table->foreignId('warehouse_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();

            // Tipo de movimiento: entry, exit, adjustment, reservation, release.
            $table->string('movement_type', 30);

            // Cantidad del movimiento (positivo = entrada, negativo = salida).
            $table->integer('quantity');

            // Referencia al documento origen (polimórfico).
            $table->nullableMorphs('reference');

            // Motivo o descripción del movimiento.
            $table->text('reason')->nullable();

            // Usuario que realizó el movimiento.
            $table->foreignId('performed_by')->nullable()->constrained('users')->nullOnDelete();

            // Metadata adicional en JSON.
            $table->json('meta_json')->nullable();

            $table->timestamps();

            // Índices para consultas frecuentes.
            $table->index(['warehouse_id', 'product_id']);
            $table->index('movement_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
