<?php

namespace App\Modules\Inventario\Services;

use App\Modules\Inventario\Models\Stock;
use App\Modules\Inventario\Models\StockMovement;
use App\Modules\Inventario\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class InventoryService
{
    public function __construct(
        private readonly InventoryMovementGateway $movementGateway,
    ) {}

    /**
     * Lista almacenes de una compañía.
     */
    public function getWarehouses(?int $companyId): Collection
    {
        return Warehouse::forCompany($companyId)->orderBy('name')->get();
    }

    /**
     * Lista movimientos con paginación y filtros.
     */
    public function listMovements(?int $companyId, array $filters = []): LengthAwarePaginator
    {
        $query = StockMovement::forCompany($companyId)
            ->with(['product', 'warehouse', 'performer']);

        if (! empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (! empty($filters['movement_type'])) {
            $query->byType($filters['movement_type']);
        }

        if (! empty($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        return $query->latest()->paginate(15);
    }

    /**
     * Registra una entrada de stock.
     */
    public function registerEntry(int $companyId, int $warehouseId, int $productId, int $quantity, ?string $reason, int $userId): void
    {
        $this->validateWarehouse($warehouseId, $companyId);
        $this->movementGateway->entry($warehouseId, $productId, $quantity, $reason, $userId);
    }

    /**
     * Registra un ajuste de stock.
     */
    public function registerAdjustment(int $companyId, int $warehouseId, int $productId, int $newQuantity, string $reason, int $userId): void
    {
        $this->validateWarehouse($warehouseId, $companyId);
        $this->movementGateway->adjustment($warehouseId, $productId, $newQuantity, $reason, $userId);
    }

    /**
     * Obtiene alertas de stock mínimo.
     */
    public function getLowStockAlerts(?int $companyId): Collection
    {
        return Stock::query()
            ->when($companyId !== null, fn ($q) => $q->whereHas('warehouse', fn ($w) => $w->where('company_id', $companyId)))
            ->whereColumn('quantity', '<=', 'minimum_stock_snapshot')
            ->with(['product', 'warehouse'])
            ->get();
    }

    private function validateWarehouse(int $warehouseId, int $companyId): void
    {
        $exists = Warehouse::where('id', $warehouseId)
            ->where('company_id', $companyId)
            ->exists();

        if (! $exists) {
            abort(404, 'Almacén no encontrado.');
        }
    }
}
