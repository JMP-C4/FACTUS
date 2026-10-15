<?php

namespace App\Modules\Inventario\Services;

use App\Modules\Inventario\Contracts\InventoryMovementGatewayContract;
use App\Modules\Inventario\Models\Stock;
use App\Modules\Inventario\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class InventoryMovementGateway implements InventoryMovementGatewayContract
{
    public function entry(int $warehouseId, int $productId, int $quantity, ?string $reason = null, ?int $performedBy = null): void
    {
        DB::transaction(function () use ($warehouseId, $productId, $quantity, $reason, $performedBy): void {
            $stock = $this->getOrCreateStock($warehouseId, $productId);

            $stock->increment('quantity', $quantity);

            StockMovement::create([
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'movement_type' => 'entry',
                'quantity' => $quantity,
                'reason' => $reason,
                'performed_by' => $performedBy,
            ]);
        });
    }

    public function exit(int $warehouseId, int $productId, int $quantity, ?string $reason = null, ?int $performedBy = null): void
    {
        DB::transaction(function () use ($warehouseId, $productId, $quantity, $reason, $performedBy): void {
            $stock = Stock::where('warehouse_id', $warehouseId)
                ->where('product_id', $productId)
                ->first();

            if (! $stock || ! $stock->hasSufficientStock($quantity)) {
                abort(422, 'Stock insuficiente para realizar la salida.');
            }

            $stock->decrement('quantity', $quantity);

            StockMovement::create([
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'movement_type' => 'exit',
                'quantity' => -$quantity,
                'reason' => $reason,
                'performed_by' => $performedBy,
            ]);
        });
    }

    public function adjustment(int $warehouseId, int $productId, int $quantity, string $reason, ?int $performedBy = null): void
    {
        DB::transaction(function () use ($warehouseId, $productId, $quantity, $reason, $performedBy): void {
            $stock = $this->getOrCreateStock($warehouseId, $productId);

            $stock->update(['quantity' => $quantity]);

            StockMovement::create([
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'movement_type' => 'adjustment',
                'quantity' => $quantity - $stock->quantity,
                'reason' => $reason,
                'performed_by' => $performedBy,
                'meta_json' => ['previous_quantity' => $stock->quantity],
            ]);
        });
    }

    public function hasSufficientStock(int $warehouseId, int $productId, int $quantity): bool
    {
        $stock = Stock::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->first();

        return $stock?->hasSufficientStock($quantity) ?? false;
    }

    public function getStock(int $warehouseId, int $productId): int
    {
        $stock = Stock::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->first();

        return $stock?->quantity ?? 0;
    }

    private function getOrCreateStock(int $warehouseId, int $productId): Stock
    {
        return Stock::firstOrCreate(
            ['warehouse_id' => $warehouseId, 'product_id' => $productId],
            ['quantity' => 0]
        );
    }
}
